<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Muestra la vista del login.
     * CORRECCIÓN: Se cambió el nombre a showLoginForm para que coincida con web.php
     */
    public function showLoginForm() 
    {
        return view('login');
    }

    /**
     * Procesa el intento de acceso.
     */
    public function login(Request $request)
    {
        // Validamos que los campos no lleguen vacíos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Intentamos iniciar sesión con las credenciales
        if (Auth::attempt($credentials)) {
            // Regeneramos la sesión por seguridad
            $request->session()->regenerate();
            
            // Si entra, lo mandamos al dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Si falla, regresa al login con el mensaje de error
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}