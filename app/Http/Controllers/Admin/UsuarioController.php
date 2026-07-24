<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // ==========================================
    // LISTA DE USUARIOS
    // ==========================================
    public function index()
    {
        $usuarios = User::with('role')->orderBy('id', 'desc')->paginate(10);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    // ==========================================
    // FORMULARIO PARA CREAR USUARIO
    // ==========================================
    public function create()
    {
        $roles = Role::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    // ==========================================
    // GUARDAR NUEVO USUARIO
    // ==========================================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    // ==========================================
    // FORMULARIO PARA EDITAR USUARIO
    // ==========================================
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::all();
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    // ==========================================
    // ACTUALIZAR USUARIO
    // ==========================================
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    // ==========================================
    // ELIMINAR USUARIO
    // ==========================================
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        
        // No permitir eliminar al propio usuario
        if ($usuario->id == auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}