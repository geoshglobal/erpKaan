<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Track email delivery of in-app notifications (F2.4 email channel).
 * Front-loaded so runtime prod code (INSERT/UPDATE only) can stamp these.
 */
class AddEmailTrackingToNotificaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('notificaciones', [
            'email_enviado_at' => ['type' => 'datetime', 'null' => true, 'after' => 'leido_at'],
            'email_error'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'email_enviado_at'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('notificaciones', ['email_enviado_at', 'email_error']);
    }
}
