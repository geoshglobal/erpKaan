<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTorres extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'clave'         => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            'nombre'        => ['type' => 'varchar', 'constraint' => 120],
            'descripcion'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'orden'         => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'activo'        => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('condominio_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('torres', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('torres', true);
    }
}
