<?php

// app/Http/Controllers/EmpleadoController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
        $roles = Role::orderBy('name')->pluck('name');

        // Sólo usuarios con rol "jefe"
        $jefes = User::role('jefe')->orderBy('name')->get(['id','name']);

        return view('empleados.create', compact('departamentos','roles','jefes'));
    }

    public function store(Request $request)
    {
        $validRoles = Role::pluck('name')->toArray();

        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'email'           => ['required','email','max:255','unique:users,email'],
            'departamento_id' => ['nullable','exists:departamentos,id'],
            'password'        => ['required','confirmed','min:8'],
            'supervisor_id' => [
                'nullable','integer','exists:users,id',
                function ($attr,$value,$fail) {
                    if (!$value) return;
                    $boss = \App\Models\User::find($value);
                    // <- case-insensitive
                    $tieneRolJefe = $boss?->roles()->whereRaw('LOWER(name) = ?', ['jefe'])->exists();
                    if (!$tieneRolJefe) {
                        $fail('El supervisor seleccionado debe tener el rol de Jefe.');
                    }
                },
            ],

            'roles'           => ['array'],
            'roles.*'         => ['string', Rule::in($validRoles)],
        ]);

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'activo'          => true,
            'departamento_id' => $data['departamento_id'] ?? null,
            'supervisor_id'   => $data['supervisor_id'] ?? null,   // 👈 guardar jefe
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('empleados.index')->with('success','Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $departamentos = Departamento::where('activo',true)->orderBy('nombre')->get();
        $roles = Role::orderBy('name')->pluck('name');
        $userRoles = $user->roles->pluck('name')->toArray();

        // Jefes, excluyendo al mismo usuario
        $jefes = User::role('jefe')
            ->where('id','!=',$user->id)
            ->orderBy('name')->get(['id','name']);

        return view('empleados.edit', compact('user','departamentos','roles','userRoles','jefes'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'email'           => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'departamento_id' => ['nullable','exists:departamentos,id'],
            'password'        => ['nullable','confirmed','min:8'],
            'supervisor_id' => [
                    'nullable','integer','exists:users,id','different:'.$user->id,
                    function ($attr,$value,$fail) use ($user) {
                        if (!$value) return;
                        $boss = \App\Models\User::find($value);

                        $tieneRolJefe = $boss?->roles()->whereRaw('LOWER(name) = ?', ['jefe'])->exists();
                        if (!$tieneRolJefe) {
                            return $fail('El supervisor seleccionado debe tener el rol de Jefe.');
                        }

                        // Evitar ciclos
                        $curr = $boss; $depth = 0;
                        while ($curr && $depth++ < 10) {
                            if ($curr->id === $user->id) {
                                return $fail('No se puede asignar un subordinado como supervisor (ciclo detectado).');
                            }
                            $curr = $curr->supervisor;
                        }
                    },
                ],
            'roles'           => ['array'],
            'roles.*'         => ['string'],
        ]);

        $user->name            = $data['name'];
        $user->email           = $data['email'];
        $user->departamento_id = $data['departamento_id'] ?? null;
        $user->supervisor_id   = $data['supervisor_id'] ?? null;   // 👈 actualizar jefe
        if(!empty($data['password'])){
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('empleados.index')->with('success','Usuario actualizado correctamente.');
    }

    public function toggle(User $user)
    {
        $user->activo = ! $user->activo;
        $user->save();

        try { DB::table('sessions')->where('user_id', $user->id)->delete(); } catch (\Throwable $e) {}
        if (method_exists($user, 'tokens')) { $user->tokens()->delete(); }

        $msg = $user->activo ? 'Usuario activado.' : 'Usuario inactivado (sesiones/tokens cerrados).';
        return redirect()->route('empleados.index')->with('success', $msg);
    }
}
