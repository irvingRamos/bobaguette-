<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = [
            'email' => 'admin@bobaguette.com', // El que creamos en el Seeder
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // Si entra, lo mandamos al inventario (que crearemos ahorita)
            return redirect()->intended('inventario');
        }

        return back()->withErrors(['error' => 'Credenciales incorrectas']);
    }
}