<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extend invitaciones to support occupant invitations (from a casa's ocupación)
 * in addition to the original persona-account invitations.
 */
class ExtendInvitaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('invitaciones', [
            'tipo'         => ['type' => 'varchar', 'constraint' => 20, 'default' => 'cuenta', 'after' => 'condominio_id'],
            'ocupacion_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'persona_id'],
            'rol_ocupante' => ['type' => 'varchar', 'constraint' => 20, 'null' => true, 'after' => 'rol'],
            'nombre'       => ['type' => 'varchar', 'constraint' => 120, 'null' => true, 'after' => 'rol_ocupante'],
            'telefono'     => ['type' => 'varchar', 'constraint' => 30, 'null' => true, 'after' => 'email'],
        ]);

        // persona_id becomes nullable (a new occupant has no persona until they register).
        $this->forge->modifyColumn('invitaciones', [
            'persona_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);

        $this->forge->addForeignKey('ocupacion_id', 'ocupaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('invitaciones');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('invitaciones', 'invitaciones_ocupacion_id_foreign');
        $this->forge->dropColumn('invitaciones', ['tipo', 'ocupacion_id', 'rol_ocupante', 'nombre', 'telefono']);
        $this->forge->modifyColumn('invitaciones', [
            'persona_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => false],
        ]);
    }
}
