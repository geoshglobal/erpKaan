<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Resident's up-front authorization (at visit creation) to use their OWN parking
 * spot if no visitor spot is available/configured — so caseta doesn't have to
 * request it at the gate. Distinct from autorizacion_cajon (the gate outcome).
 */
class AddAutorizaCajonPropioToAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('accesos', [
            'autoriza_cajon_propio' => [
                'type'       => 'tinyint',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'permite_vehiculo',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('accesos', 'autoriza_cajon_propio');
    }
}
