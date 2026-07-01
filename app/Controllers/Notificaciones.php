<?php

namespace App\Controllers;

use App\Libraries\NotifPrefs;
use App\Models\NotificacionModel;

class Notificaciones extends BaseController
{
    public function index(): string
    {
        $model = new NotificacionModel();
        $items = $model->forUser((int) auth()->id());
        $model->markAllRead((int) auth()->id()); // viewing clears the unread badge

        return view('notificaciones/index', [
            'title' => 'Notificaciones',
            'items' => $items,
        ]);
    }

    /** Per-user channel preferences (email / push / timezone). */
    public function preferencias(): string
    {
        $condo = service('tenant')->active();

        return view('notificaciones/preferencias', [
            'title'      => 'Configuración de notificaciones',
            'prefs'      => NotifPrefs::all((int) auth()->id()),
            'emailAddr'  => auth()->user()->email ?? null,
            'pushGlobal' => \App\Libraries\Push::enabled(),
            'mailGlobal' => \App\Libraries\Mailer::enabled(),
            'zones'      => \App\Libraries\Tz::ZONES,
            'condoTz'    => $condo['timezone'] ?? \App\Libraries\Tz::DEFAULT,
        ]);
    }

    public function guardarPreferencias()
    {
        $tz = (string) $this->request->getPost('timezone');
        if ($tz !== '' && ! \App\Libraries\Tz::valid($tz)) {
            $tz = '';
        }

        NotifPrefs::save(
            (int) auth()->id(),
            (bool) $this->request->getPost('email'),
            (bool) $this->request->getPost('push'),
            $tz
        );

        return redirect()->to('notificaciones/preferencias')->with('success', 'Preferencias guardadas.');
    }
}
