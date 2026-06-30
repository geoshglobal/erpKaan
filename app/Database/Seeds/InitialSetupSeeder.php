<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Seeds a superadmin login and a demo condominio so the app is usable
 * right after migrating. Idempotent: skips records that already exist.
 */
class InitialSetupSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperadmin();
        $this->seedDemoCondominio();
    }

    private function seedSuperadmin(): void
    {
        $users = new UserModel();

        if ($users->where('username', 'superadmin')->first() !== null) {
            return;
        }

        $user = new User([
            'username' => 'superadmin',
            'email'    => 'admin@erpkaan.mx',
            'password' => 'Kaan!2026Admin', // CHANGE THIS after first login
        ]);

        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->activate();          // mark email verified / active
        $user->addGroup('superadmin');
    }

    private function seedDemoCondominio(): void
    {
        $db = $this->db;

        $exists = $db->table('condominios')->where('slug', 'demo')->get()->getRow();
        if ($exists !== null) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $db->table('condominios')->insert([
            'nombre'     => 'Condominio Demo',
            'slug'       => 'demo',
            'pais'       => 'MX',
            'moneda'     => 'MXN',
            'activo'     => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
