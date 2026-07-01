<?php

namespace App\Libraries;

use App\Models\PushSubscriptionModel;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Web Push (PWA) sender built on minishlink/web-push + VAPID keys from `.env`.
 * Gated by `notify.push`: off in dev (no service worker / HTTPS), so sends are
 * skipped and no-op. Subscriptions that report 410/404 are soft-deleted.
 */
class Push
{
    public static function enabled(): bool
    {
        return filter_var(env('notify.push', false), FILTER_VALIDATE_BOOLEAN)
            && env('push.publicKey') && env('push.privateKey');
    }

    /** VAPID public key for the browser (application server key). */
    public static function publicKey(): ?string
    {
        return env('push.publicKey') ?: null;
    }

    /**
     * Push a notification to every active subscription of a user. Best-effort:
     * returns the number of pushes that reported success. No-op when disabled
     * or the user has no subscriptions.
     */
    public static function toUser(int $userId, string $titulo, string $mensaje, ?string $url = null): int
    {
        if (! self::enabled() || $userId <= 0) {
            return 0;
        }

        $model = new PushSubscriptionModel();
        $subs  = $model->forUser($userId);
        if ($subs === []) {
            return 0;
        }

        $auth = [
            'VAPID' => [
                'subject'    => env('push.subject', 'mailto:admin@kaan.geoshglobal.com'),
                'publicKey'  => env('push.publicKey'),
                'privateKey' => env('push.privateKey'),
            ],
        ];

        $payload = json_encode([
            'title' => $titulo,
            'body'  => $mensaje,
            'url'   => Notify::absUrl($url) ?? site_url('notificaciones'),
        ], JSON_UNESCAPED_UNICODE);

        try {
            $webPush = new WebPush($auth);
        } catch (\Throwable $e) {
            log_message('error', 'Push init failed: ' . $e->getMessage());
            return 0;
        }

        foreach ($subs as $s) {
            $sub = Subscription::create([
                'endpoint' => $s['endpoint'],
                'keys'     => ['p256dh' => $s['p256dh'], 'auth' => $s['auth']],
            ]);
            $webPush->queueNotification($sub, $payload);
        }

        $sent = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
                continue;
            }
            // 404/410 → endpoint gone; drop it so we stop trying.
            if ($report->isSubscriptionExpired()) {
                $model->removeByEndpoint($report->getEndpoint());
            } else {
                log_message('warning', 'Push failed: ' . $report->getReason());
            }
        }

        return $sent;
    }
}
