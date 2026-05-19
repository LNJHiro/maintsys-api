import 'package:flutter/material.dart';
import '../../models/tecnico.dart';
import '../../models/ordem_servico.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import '../../theme.dart';
import 'tecnico_form_screen.dart';
import '../ordens/ordem_detail_screen.dart';

class TecnicoDetailScreen extends StatefulWidget {
  final int tecnicoId;
  const TecnicoDetailScreen({super.key, required this.tecnicoId});

  @override
  State<TecnicoDetailScreen> createState() => _TecnicoDetailScreenState();
}

class _TecnicoDetailScreenState extends State<TecnicoDetailScreen> {
  Tecnico? _tecnico;
  List<OrdemServico> _ordens = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final t = await DatabaseService.getTecnicoById(widget.tecnicoId);
    final todas = await DatabaseService.getOrdens();
    final ordens = todas.where((o) => o.tecnicoId == widget.tecnicoId).toList();
    if (mounted) setState(() { _tecnico = t; _ordens = ordens; _loading = false; });
  }

  Future<void> _editar() async {
    if (_tecnico == null) return;
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => TecnicoFormScreen(tecnico: _tecnico)),
    );
    if (ok == true) _load();
  }

  Future<void> _excluir() async {
    if (_tecnico == null) return;
    final pode = await DatabaseService.canDeleteTecnico(_tecnico!.id!);
    if (!mounted) return;
    if (!pode) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Não é possível excluir: técnico possui ordens vinculadas.'),
        backgroundColor: Colors.red,
      ));
      return;
    }
    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Excluir técnico'),
        content: Text('Excluir "${_tecnico!.nome}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Excluir', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (confirma == true) {
      await DatabaseService.deleteTecnico(_tecnico!.id!);
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_tecnico == null) return const Scaffold(body: Center(child: Text('Técnico não encontrado.')));
    final t = _tecnico!;

    return Scaffold(
      appBar: AppBar(
        title: Text(t.nome, overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(icon: const Icon(Icons.edit), onPressed: _editar),
          IconButton(icon: const Icon(Icons.delete_outline), onPressed: _excluir),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            margin: EdgeInsets.zero,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: AppTheme.kPrimary.withOpacity(0.1),
                        child: Text(
                          t.nome.substring(0, 1).toUpperCase(),
                          style: const TextStyle(
                              color: AppTheme.kPrimary,
                              fontWeight: FontWeight.bold,
                              fontSize: 22),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(t.nome,
                                style: const TextStyle(
                                    fontSize: 18, fontWeight: FontWeight.bold)),
                            Text(t.especialidade,
                                style:
                                    TextStyle(color: Colors.grey.shade600, fontSize: 14)),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  _info('Matrícula', t.matricula, Icons.badge_outlined),
                  _info('E-mail', t.email, Icons.email_outlined),
                  if (t.telefone != null)
                    _info('Telefone', t.telefone!, Icons.phone_outlined),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),
          const Row(
            children: [
              Icon(Icons.assignment, size: 18, color: AppTheme.kPrimary),
              SizedBox(width: 8),
              Text('Ordens Vinculadas',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 8),
          if (_ordens.isEmpty)
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: const Center(
                  child: Text('Nenhuma ordem vinculada.',
                      style: TextStyle(color: Colors.grey))),
            )
          else
            ..._ordens.map((o) => Card(
                  child: ListTile(
                    leading: StatusBadge.ordem(o.status),
                    title: Text(o.numero,
                        style: const TextStyle(fontWeight: FontWeight.w600)),
                    subtitle: Text(
                        '${o.maquinaModelo ?? ''} • ${o.tipoLabel} • ${o.prioridadeLabel}'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => OrdemDetailScreen(ordemId: o.id!)),
                      );
                      _load();
                    },
                  ),
                )),
        ],
      ),
    );
  }

  Widget _info(String label, String valor, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          Icon(icon, size: 16, color: Colors.grey.shade500),
          const SizedBox(width: 8),
          SizedBox(
            width: 90,
            child: Text(label,
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
          ),
          Expanded(
            child: Text(valor,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}
