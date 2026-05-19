import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../theme.dart';
import 'home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _senhaCtrl = TextEditingController();
  bool _obscure = true;
  bool _loading = false;
  String? _erro;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _senhaCtrl.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _loading = true;
      _erro = null;
    });
    final ok = await AuthService.login(_emailCtrl.text.trim(), _senhaCtrl.text);
    if (!mounted) return;
    if (ok) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const HomeScreen()),
      );
    } else {
      setState(() {
        _loading = false;
        _erro = 'E-mail ou senha inválidos.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _buildLogo(),
                const SizedBox(height: 40),
                _buildForm(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogo() {
    return Column(
      children: [
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            color: AppTheme.kPrimary,
            borderRadius: BorderRadius.circular(20),
          ),
          child: const Icon(Icons.build_circle, color: Colors.white, size: 48),
        ),
        const SizedBox(height: 16),
        const Text(
          'MaintSys',
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.bold,
            color: AppTheme.kPrimary,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Sistema de Gerenciamento de Manutenção',
          style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildForm() {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (_erro != null)
            Container(
              padding: const EdgeInsets.all(12),
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.red.shade200),
              ),
              child: Row(
                children: [
                  Icon(Icons.error_outline, color: Colors.red.shade700, size: 18),
                  const SizedBox(width: 8),
                  Text(_erro!, style: TextStyle(color: Colors.red.shade700)),
                ],
              ),
            ),
          TextFormField(
            controller: _emailCtrl,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(
              labelText: 'E-mail',
              prefixIcon: Icon(Icons.email_outlined),
            ),
            validator: (v) => (v == null || v.trim().isEmpty) ? 'Informe o e-mail' : null,
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _senhaCtrl,
            obscureText: _obscure,
            decoration: InputDecoration(
              labelText: 'Senha',
              prefixIcon: const Icon(Icons.lock_outlined),
              suffixIcon: IconButton(
                icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility),
                onPressed: () => setState(() => _obscure = !_obscure),
              ),
            ),
            validator: (v) => (v == null || v.isEmpty) ? 'Informe a senha' : null,
          ),
          const SizedBox(height: 28),
          ElevatedButton(
            onPressed: _loading ? null : _login,
            child: _loading
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Text('Entrar', style: TextStyle(fontSize: 16)),
          ),
          const SizedBox(height: 24),
          _buildDica(),
        ],
      ),
    );
  }

  Widget _buildDica() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Usuários de demonstração:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey.shade700)),
          const SizedBox(height: 6),
          ...const [
            ['master@senai.br', 'master123', 'Admin Master'],
            ['admin@senai.br', 'admin123', 'Administrador'],
            ['usuario@senai.br', 'usuario123', 'Usuário'],
          ].map((e) => Padding(
                padding: const EdgeInsets.only(top: 2),
                child: Row(
                  children: [
                    Icon(Icons.person_outline, size: 14, color: Colors.grey.shade500),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text('${e[0]}  •  ${e[1]}  (${e[2]})',
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    ),
                  ],
                ),
              )),
        ],
      ),
    );
  }
}
