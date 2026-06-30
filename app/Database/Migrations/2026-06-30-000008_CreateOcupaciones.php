<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Occupancy / tenancy period of a casa, with its use type.
 */
class CreateOcupaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'casa_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tipo_uso'      => ['type' => 'ENUM', 'constraint' => ['propio', 'renta_lineal', 'renta_vacacional'], 'default' => 'propio'],
            'fecha_inicio'  => ['type' => 'date', 'null' => true],
            'fecha_fin'     => ['type' => 'date', 'null' => true],
            'vigente'       => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'renta_monto'   => ['type' => 'decimal', 'constraint' => '12,2', 'null' => true],
            'deposito'      => ['type' => 'decimal', 'constraint' => '12,2', 'null' => true],
            'notas'         => ['type' => 'text', 'null' => true],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('condominio_id');
        $this->forge->addKey(['casa_id', 'vigente']);
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('casa_id', 'casas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('ocupaciones', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('ocupaciones', true);
    }
}
