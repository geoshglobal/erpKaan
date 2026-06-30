<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Audit/timeline of status changes on an acceso. */
class CreateAccesoEventos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'acceso_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'estado_anterior' => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            'estado_nuevo'    => ['type' => 'varchar', 'constraint' => 20],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nota'            => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('acceso_id');
        $this->forge->addForeignKey('acceso_id', 'accesos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('acceso_eventos', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('acceso_eventos', true);
    }
}
