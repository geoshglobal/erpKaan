<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Occupant limits: a condominio-wide default and an optional per-casa override.
 * NULL means unlimited.
 */
class AddMaxOcupantes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('condominios', [
            'max_ocupantes' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'moneda'],
        ]);
        $this->forge->addColumn('casas', [
            'max_ocupantes' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'num_cajones'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('condominios', 'max_ocupantes');
        $this->forge->dropColumn('casas', 'max_ocupantes');
    }
}
