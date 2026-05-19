import 'package:flutter/material.dart';
import '../../models/tecnico.dart';
import '../../services/database_service.dart';
import '../../theme.dart';

class TecnicoFormScreen extends StatefulWidget {
  final Tecnico? tecnico;
  const TecnicoFormScreen({super.key, this.tecnico});

  @override
  State<TecnicoFormScreen> createState() => _TecnicoFormScreenState();
}

class _TecnicoFormScreenState extends State<TecnicoFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nomeCtrl = TextEditingController();
  final _matriculaCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _especialidadeCtrl = TextEditingController();
  final _telefoneCtrl = TextEditingController();
  bool _ativo = true;
  bool _loading = false;

  bool get _editando => widget.tecnico != null;

  @override
  void initState() {
    super.initState();
    if (_editando) {
      final t = widget.tecnico!;
      _nomeCtrl.text = t.nome;
      _matriculaCtrl.text = t.matricula;
      _emailCtrl.text = t.email;
      _especialidadeCtrl.text = t.especialidade;
      _telefoneCtrl.text = t.telefone ?? '';
      _ativo = t.ativo;
    }
  }

  @override
  void dispose() {
    _nomeCtrl.dispose();
    _matriculaCtrl.dispose();
    _emailCtrl.dispose();
    _especialidadeCtrl.dispose();
    _telefoneCtrl.dispose();
    super.dispose();
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    final tecnico = Tecnico(
      id: widget.tecnico?.id,
      nome: _nomeCtrl.text.trim(),
      matricula: _matriculaCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      especialidade: _especialidadeCtrl.text.trim(),
      telefone: _telefoneCtrl.text.trim().isEmpty ? null : _telefoneCtrl.text.trim(),
      ativo: _ativo,
    );
    try {
      if (_editando) {
        await DatabaseService.updateTecnico(tecnico);
      } else {
        await DatabaseService.insertTecnico(tecnico);
      }
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().contains('UNIQUE')
                ? 'Matrícula já cadastrada.'
                : 'Erro ao salvar: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_editando ? 'Editar Técnico' : 'Novo Técnico')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _campo('Nome completo', _nomeCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            _campo('Matrícula', _matriculaCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            TextFormField(
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'E-mail'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Campo obrigatório' : null,
            ),
            const SizedBox(height: 14),
            _campo('Especialidade', _especialidadeCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            TextFormField(
              controller: _telefoneCtrl,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'Telefone (opcional)'),
            ),
            const SizedBox(height: 14),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Técnico ativo'),
              value: _ativo,
              onChanged: (v) => setState(() => _ativo = v),
              activeThumbColor: AppTheme.kPrimary,
            ),
            const SizedBox(height: 28),
            ElevatedButton(
              onPressed: _loading ? null : _salvar,
              child: _loading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : Text(_editando ? 'Salvar Alterações' : 'Cadastrar Técnico'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _campo(String label, TextEditingController ctrl, {bool obrigatorio = false}) {
    return TextFormField(
      controller: ctrl,
      decoration: InputDecoration(labelText: label),
      validator: obrigatorio
          ? (v) => (v == null || v.trim().isEmpty) ? 'Campo obrigatório' : null
          : null,
    );
  }
}
