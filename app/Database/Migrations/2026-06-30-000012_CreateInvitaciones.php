<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-persona invitations for resident self-registration into the WebApp.
 */
class CreateInvitaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                 => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'condominio_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'persona_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'token'              => ['type' => 'varchar', 'constraint' => 64],
            'rol'                => ['type' => 'varchar', 'constraint' => 20],
            'email'              => ['type' => 'varchar', 'constraint' => 150, 'null' => true],
            'expires_at'         => ['type' => 'datetime', 'null' => true],
            'used_at'            => ['type' => 'datetime', 'null' => true],
            'created_by_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'datetime', 'null' => true],
            'updated_at'         => ['type' => 'datetime', 'null' => true],
            'deleted_at'         => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('persona_id');
        $this->forge->addForeignKey('condominio_id', 'condominios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('persona_id', 'personas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('invitaciones', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('invitaciones', true);
    }
}
