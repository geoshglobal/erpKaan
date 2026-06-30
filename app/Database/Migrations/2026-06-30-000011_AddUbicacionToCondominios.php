<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUbicacionToCondominios extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('condominios', [
            'latitud'  => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true, 'after' => 'cp_fiscal'],
            'longitud' => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true, 'after' => 'latitud'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('condominios', ['latitud', 'longitud']);
    }
}
