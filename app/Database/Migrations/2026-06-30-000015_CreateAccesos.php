<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'casa_id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tipo'                  => ['type' => 'ENUM', 'constraint' => ['visita', 'paqueteria', 'delivery', 'proveedor'], 'default' => 'visita'],
            'solicitante_persona_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'creado_por_user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nombre_visitante'      => ['type' => 'varchar', 'constraint' => 150],
            'empresa'               => ['type' => 'varchar', 'constraint' => 120, 'null' => true],
            'telefono'              => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'num_personas'          => ['type' => 'int', 'constraint' => 11, 'default' => 1],
            'placas'                => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            'foto_path'             => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'qr_token'              => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            'valido_desde'          => ['type' => 'datetime', 'null' => true],
            'valido_hasta'          => ['type' => 'datetime', 'null' => true],
            'estado'                => ['type' => 'varchar', 'constraint' => 20, 'default' => 'programado'],
            'check_in_at'           => ['type' => 'datetime', 'null' => true],
            'check_out_at'          => ['type' => 'datetime', 'null' => true],
            'caseta_user_id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'notas'                 => ['type' => 'text', 'null' => true],
            'created_at'            => ['type' => 'datetime', 'null' => true],
            'updated_at'            => ['type' => 'datetime', 'null' => true],
            'deleted_at'            => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('qr_token');
        $this->forge->addKey(['condominio_id', 'estado']);
        $this->forge->addKey('casa_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('casa_id', 'casas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('accesos', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('accesos', true);
    }
}
