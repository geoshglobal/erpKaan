<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUrlToNotificaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('notificaciones', [
            'url' => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'mensaje'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('notificaciones', 'url');
    }
}
