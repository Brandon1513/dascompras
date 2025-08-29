<?php

// app/Http/Controllers/EmpleadoController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $users = User::when($search, function($q) use ($search){
                $q->where(function($qq) use ($search){
                    $qq->where('name','like',"%{$search}%")
                       ->orWhere('email','like',"%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('empleados.index', compact('users'));
    }

    public function create()
{
    $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();
    $roles = Role::orderBy('name')->pluck('name'); // ['administrador','lider','validador',...]
    return view('empleados.create', compact('departamentos','roles'));
}

public function store(Request $request)
{
    // Lista de roles válidos para la validación
    $validRoles = Role::pluck('name')->toArray();

    $data = $request->validate([
        'name'            => ['required','string','max:255'],
        'email'           => ['required','email','max:255','unique:users,email'],
        'departamento_id' => ['nullable','exists:departamentos,id'],
        'password'        => ['required','confirmed','min:8'],
        'roles'           => ['array'],
        'roles.*'         => ['string', Rule::in($validRoles)],
    ]);

    $user = User::create([
        'name'            => $data['name'],
        'email'           => $data['email'],
        'password'        => Hash::make($data['password']),
        'activo'          => true,
        'departamento_id' => $data['departamento_id'] ?? null,
    ]);

    // Asignar roles seleccionados
    $user->syncRoles($data['roles'] ?? []);

    return redirect()->route('empleados.index')->with('success','Usuario creado correctamente.');
}

    public function edit(User $user)
{
    $departamentos = Departamento::where('activo',true)->orderBy('nombre')->get();
    $roles = Role::orderBy('name')->pluck('name'); // ['administrador','lider','validador', ...]
    $userRoles = $user->roles->pluck('name')->toArray();

    return view('empleados.edit', compact('user','departamentos','roles','userRoles'));
}

public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name'            => ['required','string','max:255'],
        'email'           => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
        'departamento_id' => ['nullable','exists:departamentos,id'],
        'password'        => ['nullable','confirmed','min:8'],
        'roles'           => ['array'],        // ej. ['administrador','lider']
        'roles.*'         => ['string'],
    ]);

    $user->name  = $data['name'];
    $user->email = $data['email'];
    $user->departamento_id = $data['departamento_id'] ?? null;
    if(!empty($data['password'])){
        $user->password = Hash::make($data['password']);
    }
    $user->save();

    // sincroniza roles (si no mandas nada, deja vacío)
    $user->syncRoles($data['roles'] ?? []);

    return redirect()->route('empleados.index')->with('success','Usuario actualizado correctamente.');
}

    public function toggle(User $user)
{
    $user->activo = ! $user->activo;
    $user->save();

    if (!$user->activo) {
        // Cierra sesiones si usas session driver = database
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // si no usas database sessions, no pasa nada
        }

        // Revoca tokens de API (Sanctum)
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
    }

    $msg = $user->activo ? 'Usuario activado.' : 'Usuario inactivado (sesiones/tokens cerrados).';
    return redirect()->route('empleados.index')->with('success', $msg);
}

}
