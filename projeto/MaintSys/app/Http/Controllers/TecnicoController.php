<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Tecnico;
use App\Models\User;

class TecnicoController extends Controller
{
    public function index()
    {
        $tecnicos = Tecnico::withCount('ordens')->latest()->paginate(15);
        return view('tecnicos.index', compact('tecnicos'));
    }

    public function create()
    {
        return view('tecnicos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'          => 'required|string|max:255',
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula',
            'email'         => [
                'required',
                'email',
                'max:255',
                'unique:tecnicos,email',
                'unique:users,email',
            ],
            'password'      => 'required|string|min:8|confirmed',
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);
        DB::transaction(function () use ($request, $data) {
            $hashedPassword = Hash::make($data['password']);

            $user = User::create([
                'name'     => $data['nome'],
                'email'    => $data['email'],
                'password' => $hashedPassword,
                'role'     => 'usuario',
            ]);

            $data['user_id'] = $user->id;
            $data['password'] = $hashedPassword;
            $data['ativo'] = $request->boolean('ativo', true);

            Tecnico::create($data);
        });
        return redirect()->route('tecnicos.index')->with('success', 'Tecnico cadastrado com perfil de usuario!');
    }

    public function show(string $id)
    {
        $tecnico = Tecnico::with(['ordens.maquina', 'historicos.maquina'])->findOrFail($id);
        return view('tecnicos.show', compact('tecnico'));
    }

    public function edit(string $id)
    {
        $tecnico = Tecnico::findOrFail($id);
        return view('tecnicos.edit', compact('tecnico'));
    }

    public function update(Request $request, string $id)
    {
        $tecnico = Tecnico::findOrFail($id);
        $data = $request->validate([
            'nome'          => 'required|string|max:255',
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula,' . $id,
            'email'         => [
                'required',
                'email',
                'max:255',
                Rule::unique('tecnicos', 'email')->ignore($tecnico->id),
                Rule::unique('users', 'email')->ignore($tecnico->user_id),
            ],
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);
        $password = null;

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $password = Hash::make($request->password);
            $data['password'] = $password;
        }
        $data['ativo'] = $request->boolean('ativo', true);

        DB::transaction(function () use ($tecnico, $data, $password) {
            $user = $tecnico->user;

            if (!$user) {
                $user = User::create([
                    'name'     => $data['nome'],
                    'email'    => $data['email'],
                    'password' => $password ?? $tecnico->password,
                    'role'     => 'usuario',
                ]);

                $data['user_id'] = $user->id;
            } else {
                $userData = [
                    'name'  => $data['nome'],
                    'email' => $data['email'],
                ];

                if ($password) {
                    $userData['password'] = $password;
                }

                $user->update($userData);
            }

            $tecnico->update($data);
        });
        return redirect()->route('tecnicos.index')->with('success', 'Técnico atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $tecnico = Tecnico::findOrFail($id);
        if ($tecnico->ordens()->exists()) {
            return redirect()->route('tecnicos.index')
                ->with('error', 'Não é possível excluir: existem O.S. vinculadas a este técnico.');
        }
        if ($tecnico->historicos()->exists()) {
            return redirect()->route('tecnicos.index')
                ->with('error', 'Nao e possivel excluir: existem historicos vinculados a este tecnico.');
        }

        DB::transaction(function () use ($tecnico) {
            $user = $tecnico->user;

            $tecnico->delete();

            if ($user && $user->role === 'usuario') {
                $user->delete();
            }
        });
        return redirect()->route('tecnicos.index')->with('success', 'Técnico excluído com sucesso!');
    }
}
