<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'persona_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'acceso_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tipo'          => ['type' => 'varchar', 'constraint' => 30, 'default' => 'acceso'],
            'titulo'        => ['type' => 'varchar', 'constraint' => 150],
            'mensaje'       => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'canal'         => ['type' => 'varchar', 'constraint' => 20, 'default' => 'in_app'],
            'leido_at'      => ['type' => 'datetime', 'null' => true],
            'created_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'leido_at']);
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('notificaciones', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('notificaciones', true);
    }
}
