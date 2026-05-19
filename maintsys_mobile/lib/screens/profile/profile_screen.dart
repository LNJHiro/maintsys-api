import 'package:flutter/material.dart';
import '../../models/user.dart';
import '../../services/auth_service.dart';
import '../../services/database_service.dart';
import '../../theme.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _infoKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _currentCtrl = TextEditingController();
  final _newCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _savingInfo = false;
  bool _savingPwd = false;

  @override
  void initState() {
    super.initState();
    final u = AuthService.currentUser!;
    _nameCtrl.text = u.name;
    _emailCtrl.text = u.email;
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _currentCtrl.dispose();
    _newCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveInfo() async {
    if (!_infoKey.currentState!.validate()) return;
    setState(() => _savingInfo = true);
    final u = AuthService.currentUser!;
    final email = _emailCtrl.text.trim();
    if (email != u.email &&
        await DatabaseService.emailExists(email, exceptId: u.id)) {
      setState(() => _savingInfo = false);
      _snack('E-mail já está em uso.', Colors.red);
      return;
    }
    final novo = u.copyWith(name: _nameCtrl.text.trim(), email: email);
    await DatabaseService.updateUser(novo);
    AuthService.updateCurrent(novo);
    setState(() => _savingInfo = false);
    _snack('Perfil atualizado com sucesso.', Colors.green);
  }

  Future<void> _savePassword() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _savingPwd = true);
    final u = AuthService.currentUser!;
    final auth = await DatabaseService.authenticate(u.email, _currentCtrl.text);
    if (auth == null) {
      setState(() => _savingPwd = false);
      _snack('Senha atual incorreta.', Colors.red);
      return;
    }
    await DatabaseService.updatePassword(u.id!, _newCtrl.text);
    AuthService.updateCurrent(u.copyWith(password: _newCtrl.text));
    _currentCtrl.clear();
    _newCtrl.clear();
    _confirmCtrl.clear();
    setState(() => _savingPwd = false);
    _snack('Senha alterada com sucesso.', Colors.green);
  }

  void _snack(String msg, Color color) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: color),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser!;
    return Scaffold(
      appBar: AppBar(title: const Text('Meu Perfil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildHeader(user),
          const SizedBox(height: 24),
          _buildInfoCard(),
          const SizedBox(height: 16),
          _buildPasswordCard(),
        ],
      ),
    );
  }

  Widget _buildHeader(User user) {
    return Column(
      children: [
        CircleAvatar(
          radius: 40,
          backgroundColor: AppTheme.kPrimary,
          child: Text(
            user.name.isNotEmpty ? user.name[0].toUpperCase() : '?',
            style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.bold),
          ),
        ),
        const SizedBox(height: 12),
        Text(user.name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        Text(user.email, style: TextStyle(color: Colors.grey.shade600)),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          decoration: BoxDecoration(
            color: AppTheme.kPrimary.withOpacity(0.1),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            user.roleLabel,
            style: const TextStyle(color: AppTheme.kPrimary, fontWeight: FontWeight.w600, fontSize: 12),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoCard() {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _infoKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Informações Pessoais',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
              const SizedBox(height: 16),
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
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _savingInfo ? null : _saveInfo,
                icon: _savingInfo
                    ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.save),
                label: const Text('Salvar Informações'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPasswordCard() {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Alterar Senha',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
              const SizedBox(height: 16),
              TextFormField(
                controller: _currentCtrl,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Senha atual',
                  prefixIcon: Icon(Icons.lock_outline),
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Informe a senha atual' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _newCtrl,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Nova senha',
                  prefixIcon: Icon(Icons.lock),
                ),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Informe a nova senha';
                  if (v.length < 6) return 'Mínimo de 6 caracteres';
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _confirmCtrl,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Confirmar nova senha',
                  prefixIcon: Icon(Icons.lock),
                ),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Confirme a senha';
                  if (v != _newCtrl.text) return 'As senhas não coincidem';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _savingPwd ? null : _savePassword,
                icon: _savingPwd
                    ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.key),
                label: const Text('Alterar Senha'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
