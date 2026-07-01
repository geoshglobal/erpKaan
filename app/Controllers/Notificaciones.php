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

    /** Per-user channel preferences (email / push). */
    public function preferencias(): string
    {
        return view('notificaciones/preferencias', [
            'title'      => 'Configuración de notificaciones',
            'prefs'      => NotifPrefs::all((int) auth()->id()),
            'emailAddr'  => auth()->user()->email ?? null,
            'pushGlobal' => \App\Libraries\Push::enabled(),
            'mailGlobal' => \App\Libraries\Mailer::enabled(),
        ]);
    }

    public function guardarPreferencias()
    {
        NotifPrefs::save(
            (int) auth()->id(),
            (bool) $this->request->getPost('email'),
            (bool) $this->request->getPost('push')
        );

        return redirect()->to('notificaciones/preferencias')->with('success', 'Preferencias guardadas.');
    }
}
