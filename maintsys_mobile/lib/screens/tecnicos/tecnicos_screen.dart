import 'package:flutter/material.dart';
import '../../models/tecnico.dart';
import '../../services/database_service.dart';
import '../../widgets/empty_state.dart';
import '../../theme.dart';
import 'tecnico_form_screen.dart';
import 'tecnico_detail_screen.dart';

class TecnicosScreen extends StatefulWidget {
  const TecnicosScreen({super.key});

  @override
  State<TecnicosScreen> createState() => _TecnicosScreenState();
}

class _TecnicosScreenState extends State<TecnicosScreen> {
  late Future<List<Tecnico>> _future;
  bool _apenasAtivos = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = DatabaseService.getTecnicos();
  }

  void _reload() => setState(() => _load());

  Future<void> _abrirFormulario([Tecnico? tecnico]) async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => TecnicoFormScreen(tecnico: tecnico)),
    );
    if (ok == true) _reload();
  }

  Future<void> _abrirDetalhe(Tecnico t) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => TecnicoDetailScreen(tecnicoId: t.id!)),
    );
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(
        onPressed: () => _abrirFormulario(),
        child: const Icon(Icons.person_add),
      ),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                const Text('Somente ativos'),
                const Spacer(),
                Switch(
                  value: _apenasAtivos,
                  onChanged: (v) => setState(() => _apenasAtivos = v),
                  activeThumbColor: AppTheme.kPrimary,
                ),
              ],
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => _reload(),
              color: AppTheme.kPrimary,
              child: FutureBuilder<List<Tecnico>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  var lista = snap.data ?? [];
                  if (_apenasAtivos) lista = lista.where((t) => t.ativo).toList();
                  if (lista.isEmpty) {
                    return EmptyState(
                      icon: Icons.engineering_outlined,
                      message: 'Nenhum técnico cadastrado.',
                      actionLabel: 'Cadastrar técnico',
                      onAction: () => _abrirFormulario(),
                    );
                  }
                  return ListView.builder(
                    itemCount: lista.length,
                    itemBuilder: (_, i) => _buildItem(lista[i]),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildItem(Tecnico t) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _abrirDetalhe(t),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              CircleAvatar(
                radius: 24,
                backgroundColor: AppTheme.kPrimary.withOpacity(0.1),
                child: Text(
                  t.nome.substring(0, 1).toUpperCase(),
                  style: const TextStyle(
                    color: AppTheme.kPrimary,
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(t.nome,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                    Text(t.especialidade,
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text('Matrícula: ${t.matricula}',
                        style: TextStyle(color: Colors.grey.shade500, fontSize: 12)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: t.ativo
                      ? const Color(0xFF2E7D32).withOpacity(0.12)
                      : Colors.grey.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  t.ativo ? 'Ativo' : 'Inativo',
                  style: TextStyle(
                    color: t.ativo ? const Color(0xFF2E7D32) : Colors.grey,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              const SizedBox(width: 4),
              const Icon(Icons.chevron_right, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
