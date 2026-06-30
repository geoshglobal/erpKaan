<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * M:N owners <-> casas, with co-ownership metadata.
 * Resolves "un dueño puede tener varias casas" + multiple owners per casa.
 */
class CreateCasaPropietarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'casa_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'persona_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'principal'    => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'porcentaje'   => ['type' => 'decimal', 'constraint' => '5,2', 'default' => 100.00],
            'fecha_inicio' => ['type' => 'date', 'null' => true],
            'fecha_fin'    => ['type' => 'date', 'null' => true],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
            'deleted_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('casa_id');
        $this->forge->addKey('persona_id');
        $this->forge->addForeignKey('casa_id', 'casas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('persona_id', 'personas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('casa_propietarios', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('casa_propietarios', true);
    }
}
