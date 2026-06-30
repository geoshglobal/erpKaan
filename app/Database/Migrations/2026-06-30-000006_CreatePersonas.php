<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Unified people registry: a persona can be an owner (casa_propietarios)
 * and/or a tenant (ocupantes), and may optionally have a Shield login (user_id).
 */
class CreatePersonas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nombre'           => ['type' => 'varchar', 'constraint' => 120],
            'apellido_paterno' => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'apellido_materno' => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'email'            => ['type' => 'varchar', 'constraint' => 150, 'null' => true],
            'telefono'         => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'telefono2'        => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'foto_path'        => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'fecha_nacimiento' => ['type' => 'date', 'null' => true],
            // Datos fiscales CFDI (receptor)
            'rfc'              => ['type' => 'varchar', 'constraint' => 13, 'null' => true],
            'razon_social'     => ['type' => 'varchar', 'constraint' => 200, 'null' => true],
            'regimen_fiscal'   => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'uso_cfdi'         => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'cp_fiscal'        => ['type' => 'varchar', 'constraint' => 10, 'null' => true],
            'notas'            => ['type' => 'text', 'null' => true],
            'activo'           => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
            'deleted_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('condominio_id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('personas', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('personas', true);
    }
}
