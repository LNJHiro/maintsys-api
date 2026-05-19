import 'package:flutter/material.dart';
import '../../models/user.dart';
import '../../services/database_service.dart';

class UsuarioFormScreen extends StatefulWidget {
  final User? user;
  const UsuarioFormScreen({super.key, this.user});

  @override
  State<UsuarioFormScreen> createState() => _UsuarioFormScreenState();
}

class _UsuarioFormScreenState extends State<UsuarioFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  String _role = 'usuario';
  bool _saving = false;

  bool get _isEdit => widget.user != null;

  @override
  void initState() {
    super.initState();
    if (_isEdit) {
      final u = widget.user!;
      _nameCtrl.text = u.name;
      _emailCtrl.text = u.email;
      _role = u.role == 'admin_master' ? 'admin' : u.role;
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final email = _emailCtrl.text.trim();
    final emailDuplicado = await DatabaseService.emailExists(
      email,
      exceptId: widget.user?.id,
    );
    if (emailDuplicado) {
      if (!mounted) return;
      setState(() => _saving = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('E-mail já está em uso.'), backgroundColor: Colors.red),
      );
      return;
    }

    if (_isEdit) {
      final atualizado = widget.user!.copyWith(
        name: _nameCtrl.text.trim(),
        email: email,
        role: _role,
      );
      await DatabaseService.updateUser(
        atualizado,
        newPassword: _passCtrl.text.isEmpty ? null : _passCtrl.text,
      );
    } else {
      await DatabaseService.insertUser(User(
        name: _nameCtrl.text.trim(),
        email: email,
        password: _passCtrl.text,
        role: _role,
      ));
    }

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(_isEdit ? 'Usuário atualizado.' : 'Usuário criado.'),
        backgroundColor: Colors.green,
      ),
    );
    Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_isEdit ? 'Editar Usuário' : 'Novo Usuário')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _nameCtrl,
                decoration: const InputDecoration(
                  labelText: 'Nome',
                  prefixIcon: Icon(Icons.person_outline),
                ),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Informe o nome' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'E-mail',
                  prefixIcon: Icon(Icons.email_outlined),
                ),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Informe o e-mail';
                  if (!v.contains('@')) return 'E-mail inválido';
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _passCtrl,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: _isEdit ? 'Nova senha (opcional)' : 'Senha',
                  prefixIcon: const Icon(Icons.lock_outline),
                ),
                validator: (v) {
                  if (!_isEdit && (v == null || v.isEmpty)) return 'Informe a senha';
                  if (v != null && v.isNotEmpty && v.length < 6) return 'Mínimo 6 caracteres';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _role,
                decoration: const InputDecoration(
                  labelText: 'Função',
                  prefixIcon: Icon(Icons.badge_outlined),
                ),
                items: const [
                  DropdownMenuItem(value: 'admin', child: Text('Administrador')),
                  DropdownMenuItem(value: 'usuario', child: Text('Usuário')),
                ],
                onChanged: (v) => setState(() => _role = v ?? 'usuario'),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _saving ? null : _save,
                icon: _saving
                    ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.save),
                label: Text(_isEdit ? 'Salvar Alterações' : 'Criar Usuário'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
