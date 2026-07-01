<?php

namespace App\Models;

use CodeIgniter\Model;

class PushSubscriptionModel extends Model
{
    protected $table         = 'push_subscriptions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'user_id', 'endpoint', 'endpoint_hash', 'p256dh', 'auth', 'user_agent',
    ];

    /**
     * Upsert a subscription for a user (keyed by endpoint hash). Re-subscribing
     * the same browser refreshes its keys instead of duplicating rows; a
     * previously soft-deleted row is revived.
     */
    public function store(int $userId, string $endpoint, string $p256dh, string $auth, ?string $ua = null): void
    {
        $hash = hash('sha256', $endpoint);
        $data = [
            'user_id'       => $userId,
            'endpoint'      => $endpoint,
            'endpoint_hash' => $hash,
            'p256dh'        => $p256dh,
            'auth'          => $auth,
            'user_agent'    => $ua ? substr($ua, 0, 255) : null,
        ];

        // withDeleted so we can revive a soft-deleted subscription.
        $existing = $this->withDeleted()->where('endpoint_hash', $hash)->first();
        if ($existing) {
            $this->builder()
                ->where('id', $existing['id'])
                ->update($data + ['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
            return;
        }

        $this->insert($data);
    }

    /** Active subscriptions for a user. @return list<array<string,mixed>> */
    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /** Soft-delete a subscription by endpoint (e.g. after a 410 Gone or unsubscribe). */
    public function removeByEndpoint(string $endpoint): void
    {
        $row = $this->where('endpoint_hash', hash('sha256', $endpoint))->first();
        if ($row) {
            $this->delete($row['id']); // soft delete → UPDATE deleted_at
        }
    }
}
