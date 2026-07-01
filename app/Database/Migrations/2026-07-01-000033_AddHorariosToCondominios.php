<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-condominio allowed schedules (days + hours) for resident-announced
 * deliveries and proveedores. Stored as JSON, e.g.
 *   {"delivery":{"activo":true,"dias":[0,1,2,3,4,5,6],"desde":"08:00","hasta":"22:00"},
 *    "proveedor":{"activo":true,"dias":[1,2,3,4,5],"desde":"09:00","hasta":"18:00"}}
 */
class AddHorariosToCondominios extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('condominios', [
            'horarios' => ['type' => 'text', 'null' => true, 'after' => 'longitud'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('condominios', 'horarios');
    }
}
