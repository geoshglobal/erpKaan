<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Proof-of-delivery photo captured by caseta when handing a package to the resident. */
class AddFotoEntregaToAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('accesos', [
            'foto_entrega_path' => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'foto_path'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('accesos', 'foto_entrega_path');
    }
}
