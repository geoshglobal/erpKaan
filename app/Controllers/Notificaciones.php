<?php

namespace App\Controllers;

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
}
