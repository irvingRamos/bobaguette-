<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        // Solo administradores pueden ver usuarios
        if (auth()->user()->rol !== 'Administrador') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $usuarios          = User::all();
        $totalUsuarios     = User::count();
        $totalJefes        = User::where('rol', 'Administrador')->count();
        $totalTrabajadores = User::where('rol', 'Trabajador')->count();

        return view('usuarios', compact('usuarios', 'totalUsuarios', 'totalJefes', 'totalTrabajadores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'rol'      => 'required|string',
            'turno'    => 'required|string',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password' => 'La contraseña debe incluir mayúsculas, minúsculas, números y un símbolo.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . str_replace(' ', '_', $request->name) . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('img/usuarios'), $nombreFoto);
            $fotoPath = 'img/usuarios/' . $nombreFoto;
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol,
            'turno'    => $request->turno,
            'foto'     => $fotoPath,
        ]);

        return redirect()->route('usuarios.index')->with('success', '¡Usuario registrado con éxito!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'rol'      => 'required|string',
            'turno'    => 'required|string',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password' => 'La contraseña debe incluir mayúsculas, minúsculas, números y un símbolo.',
        ]);

        $fotoPath = $user->foto;
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($user->foto && file_exists(public_path($user->foto))) {
                unlink(public_path($user->foto));
            }

            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . str_replace(' ', '_', $request->name) . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('img/usuarios'), $nombreFoto);
            $fotoPath = 'img/usuarios/' . $nombreFoto;
        }

        $userData = [
            'name'  => $request->name,
            'email' => $request->email,
            'rol'   => $request->rol,
            'turno' => $request->turno,
            'foto'  => $fotoPath,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('usuarios.index')->with('success', '¡Usuario actualizado con éxito!');
    }

    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        User::destroy($id);

        // 'eliminar' para que dispare el toast rojo en la vista
        return redirect()->route('usuarios.index')->with('eliminar', 'Usuario eliminado correctamente.');
    }
}