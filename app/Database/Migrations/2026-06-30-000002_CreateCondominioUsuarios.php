<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Scopes a Shield user to a condominio (which staff manages which condo).
 */
class CreateCondominioUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'rol'           => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'activo'        => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['condominio_id', 'user_id']);
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('condominio_usuarios', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('condominio_usuarios', true);
    }
}
