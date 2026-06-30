<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCondominios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre'         => ['type' => 'varchar', 'constraint' => 150],
            'slug'           => ['type' => 'varchar', 'constraint' => 160],
            'direccion'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'colonia'        => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'municipio'      => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'estado'         => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'cp'             => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'pais'           => ['type' => 'varchar', 'constraint' => 2, 'default' => 'MX'],
            'moneda'         => ['type' => 'varchar', 'constraint' => 3, 'default' => 'MXN'],
            'telefono'       => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'email'          => ['type' => 'varchar', 'constraint' => 150, 'null' => true],
            // Datos fiscales CFDI (emisor)
            'razon_social'   => ['type' => 'varchar', 'constraint' => 200, 'null' => true],
            'rfc'            => ['type' => 'varchar', 'constraint' => 13, 'null' => true],
            'regimen_fiscal' => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'cp_fiscal'      => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'logo_path'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'settings'       => ['type' => 'json', 'null' => true],
            'activo'         => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'datetime', 'null' => true],
            'updated_at'     => ['type' => 'datetime', 'null' => true],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('condominios', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('condominios', true);
    }
}
