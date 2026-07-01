<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Web Push (PWA) subscriptions — one row per browser/device a user has opted in
 * on. Soft-deleted (prod can't DELETE): expired/gone endpoints (HTTP 410) are
 * marked deleted via UPDATE. `endpoint_hash` (sha256) carries the unique index
 * so the long endpoint URL itself never hits index-length limits.
 */
class CreatePushSubscriptions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'endpoint'      => ['type' => 'varchar', 'constraint' => 500],
            'endpoint_hash' => ['type' => 'char', 'constraint' => 64],
            'p256dh'        => ['type' => 'varchar', 'constraint' => 255],
            'auth'          => ['type' => 'varchar', 'constraint' => 255],
            'user_agent'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('endpoint_hash');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('push_subscriptions', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('push_subscriptions', true);
    }
}
