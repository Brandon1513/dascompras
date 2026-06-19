<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Bloquea la vista pública de registro.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Bloquea el registro público de usuarios.
     */
    public function store(Request $request)
    {
        abort(404);
    }
}