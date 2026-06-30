<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Extra data captured by caseta at check-in. */
class AddCheckinFieldsToAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('accesos', [
            'ingreso_vehiculo' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'after' => 'placas'],
            'folio_corbatin'   => ['type' => 'varchar', 'constraint' => 40, 'null' => true, 'after' => 'ingreso_vehiculo'],
            'pax_ingresaron'   => ['type' => 'int', 'constraint' => 11, 'null' => true, 'after' => 'num_personas'],
            'id_foto_path'     => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'foto_path'],
            'sin_id'           => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0, 'after' => 'id_foto_path'],
            'id_nota'          => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'sin_id'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('accesos', ['ingreso_vehiculo', 'folio_corbatin', 'pax_ingresaron', 'id_foto_path', 'sin_id', 'id_nota']);
    }
}
