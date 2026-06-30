<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * People living under an ocupacion: one principal + N secundarios.
 * For uso propio these are the owners; for renta_lineal, the inquilinos.
 */
class CreateOcupantes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ocupacion_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'persona_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'rol'          => ['type' => 'ENUM', 'constraint' => ['principal', 'secundario'], 'default' => 'principal'],
            'parentesco'   => ['type' => 'varchar', 'constraint' => 60, 'null' => true],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
            'deleted_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('ocupacion_id');
        $this->forge->addKey('persona_id');
        $this->forge->addForeignKey('ocupacion_id', 'ocupaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('persona_id', 'personas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('ocupantes', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('ocupantes', true);
    }
}
