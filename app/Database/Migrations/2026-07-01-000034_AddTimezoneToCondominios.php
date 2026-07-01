<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Default display timezone per condominio. Datetimes are stored in UTC (server
 * tz) and rendered in this zone; users may override it in their preferences.
 */
class AddTimezoneToCondominios extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('condominios', [
            'timezone' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'after' => 'horarios'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('condominios', 'timezone');
    }
}
