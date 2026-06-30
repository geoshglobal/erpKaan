<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCasas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'torre_id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'identificador'         => ['type' => 'varchar', 'constraint' => 40],
            'tipo_ocupacion_actual' => ['type' => 'ENUM', 'constraint' => ['propio', 'renta_lineal', 'renta_vacacional'], 'default' => 'propio'],
            'num_cajones'           => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'm2'                    => ['type' => 'decimal', 'constraint' => '10,2', 'null' => true],
            'notas'                 => ['type' => 'text', 'null' => true],
            'activo'                => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'            => ['type' => 'datetime', 'null' => true],
            'updated_at'            => ['type' => 'datetime', 'null' => true],
            'deleted_at'            => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['condominio_id', 'identificador']);
        $this->forge->addKey('torre_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('torre_id', 'torres', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('casas', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('casas', true);
    }
}
