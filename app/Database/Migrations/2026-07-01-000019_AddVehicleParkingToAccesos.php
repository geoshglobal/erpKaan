<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Vehicle authorization on the visit + parking spot assignment / resident
 * authorization when a resident's spot must be used.
 */
class AddVehicleParkingToAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('accesos', [
            // Resident's authorization at creation (distinct from caseta's ingreso_vehiculo).
            'permite_vehiculo'   => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'after' => 'valido_hasta'],
            // Parking spot assigned at check-in (visitor or resident spot).
            'cajon_id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'folio_corbatin'],
            // Resident-spot authorization: null (n/a) | pendiente | autorizado | rechazado.
            'autorizacion_cajon' => ['type' => 'varchar', 'constraint' => 20, 'null' => true, 'after' => 'cajon_id'],
        ]);
        $this->forge->addForeignKey('cajon_id', 'cajones', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('accesos');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('accesos', 'accesos_cajon_id_foreign');
        $this->forge->dropColumn('accesos', ['permite_vehiculo', 'cajon_id', 'autorizacion_cajon']);
    }
}
