<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCajones extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'casa_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'torre_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'identificador' => ['type' => 'varchar', 'constraint' => 40],
            'tipo'          => ['type' => 'ENUM', 'constraint' => ['asignado', 'visita', 'comun'], 'default' => 'asignado'],
            'techado'       => ['type' => 'tinyint', 'constraint' => 1, 'null' => true],
            'activo'        => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['condominio_id', 'identificador']);
        $this->forge->addKey('casa_id');
        $this->forge->addKey('torre_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('casa_id', 'casas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('torre_id', 'torres', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('cajones', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('cajones', true);
    }
}
