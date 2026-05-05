<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Tecnico;

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
            'email'         => 'required|email|max:255|unique:tecnicos,email',
            'password'      => 'required|string|min:8|confirmed',
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['ativo'] = $request->boolean('ativo', true);
        Tecnico::create($data);
        return redirect()->route('tecnicos.index')->with('success', 'Técnico cadastrado com sucesso!');
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
            'email'         => 'required|email|max:255|unique:tecnicos,email,' . $id,
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }
        $data['ativo'] = $request->boolean('ativo', true);
        $tecnico->update($data);
        return redirect()->route('tecnicos.index')->with('success', 'Técnico atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $tecnico = Tecnico::findOrFail($id);
        if ($tecnico->ordens()->exists()) {
            return redirect()->route('tecnicos.index')
                ->with('error', 'Não é possível excluir: existem O.S. vinculadas a este técnico.');
        }
        $tecnico->delete();
        return redirect()->route('tecnicos.index')->with('success', 'Técnico excluído com sucesso!');
    }
}