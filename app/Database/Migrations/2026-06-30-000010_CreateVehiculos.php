<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Vehicles per casa/persona, optionally tied to a cajon. Used later by the
 * gate (caseta) module; created now since prod cannot ALTER tables.
 */
class CreateVehiculos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'casa_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'persona_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'cajon_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'placa'         => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            'marca'         => ['type' => 'varchar', 'constraint' => 60, 'null' => true],
            'modelo'        => ['type' => 'varchar', 'constraint' => 60, 'null' => true],
            'color'         => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
            'activo'        => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('condominio_id');
        $this->forge->addKey('casa_id');
        $this->forge->addKey('persona_id');
        $this->forge->addKey('cajon_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('casa_id', 'casas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('persona_id', 'personas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('cajon_id', 'cajones', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('vehiculos', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('vehiculos', true);
    }
}
