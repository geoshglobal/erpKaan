<?php

namespace App\Controllers;

use App\Models\PushSubscriptionModel;

/**
 * Web Push subscription endpoints for the logged-in user. The browser
 * subscribes via the service worker and posts the subscription here.
 */
class PushController extends BaseController
{
    /** Save/refresh the current browser's push subscription. */
    public function subscribe()
    {
        $userId = (int) (auth()->id() ?? 0);
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }

        $sub      = $this->request->getJSON(true) ?? [];
        $endpoint = $sub['endpoint'] ?? null;
        $p256dh   = $sub['keys']['p256dh'] ?? null;
        $auth     = $sub['keys']['auth'] ?? null;

        if (! $endpoint || ! $p256dh || ! $auth) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'incompleta']);
        }

        (new PushSubscriptionModel())->store(
            $userId,
            $endpoint,
            $p256dh,
            $auth,
            $this->request->getUserAgent()->getAgentString()
        );

        return $this->response->setJSON(['ok' => true]);
    }

    /** Remove the current browser's subscription (on toggle-off). */
    public function unsubscribe()
    {
        if (! auth()->loggedIn()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }

        $endpoint = ($this->request->getJSON(true) ?? [])['endpoint'] ?? null;
        if ($endpoint) {
            (new PushSubscriptionModel())->removeByEndpoint($endpoint);
        }

        return $this->response->setJSON(['ok' => true]);
    }
}
