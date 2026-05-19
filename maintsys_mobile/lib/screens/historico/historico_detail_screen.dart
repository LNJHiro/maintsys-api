import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/historico_manutencao.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';

class HistoricoDetailScreen extends StatefulWidget {
  final int historicoId;
  const HistoricoDetailScreen({super.key, required this.historicoId});

  @override
  State<HistoricoDetailScreen> createState() => _HistoricoDetailScreenState();
}

class _HistoricoDetailScreenState extends State<HistoricoDetailScreen> {
  HistoricoManutencao? _historico;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final h = await DatabaseService.getHistoricoById(widget.historicoId);
    if (mounted) setState(() { _historico = h; _loading = false; });
  }

  Future<void> _excluir() async {
    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Excluir registro'),
        content: const Text('Excluir este registro do histórico?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancelar'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Excluir', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (confirma == true) {
      await DatabaseService.deleteHistorico(widget.historicoId);
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_historico == null) {
      return const Scaffold(body: Center(child: Text('Registro não encontrado.')));
    }
    final h = _historico!;
    final fmt = DateFormat('dd/MM/yyyy HH:mm');
    final dataInicio = h.dataInicio != null ? DateTime.tryParse(h.dataInicio!) : null;
    final dataFim = h.dataFim != null ? DateTime.tryParse(h.dataFim!) : null;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Histórico de Manutenção'),
        actions: [
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
                      Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          color: h.tipo == 'preventiva'
                              ? const Color(0xFF1565C0).withOpacity(0.1)
                              : const Color(0xFFE65100).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          h.tipo == 'preventiva'
                              ? Icons.shield_outlined
                              : Icons.build_outlined,
                          color: h.tipo == 'preventiva'
                              ? const Color(0xFF1565C0)
                              : const Color(0xFFE65100),
                          size: 26,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(h.maquinaModelo ?? 'Máquina #${h.maquinaId}',
                                style: const TextStyle(
                                    fontSize: 17, fontWeight: FontWeight.bold)),
                            Text(h.tecnicoNome ?? 'Técnico #${h.tecnicoId}',
                                style: TextStyle(color: Colors.grey.shade600)),
                          ],
                        ),
                      ),
                      StatusBadge.tipo(h.tipo),
                    ],
                  ),
                  const Divider(height: 20),
                  if (h.ordemNumero != null)
                    _info('O.S. Vinculada', h.ordemNumero!, Icons.assignment_outlined),
                  if (dataInicio != null)
                    _info('Início', fmt.format(dataInicio), Icons.play_circle_outlined),
                  if (dataFim != null)
                    _info('Fim', fmt.format(dataFim), Icons.stop_circle_outlined),
                  if (h.tempoParadaHoras != null)
                    _info('Tempo parado', '${h.tempoParadaHoras!.toStringAsFixed(1)}h',
                        Icons.timer_outlined),
                  if (h.custo != null)
                    _info('Custo total', 'R\$ ${h.custo!.toStringAsFixed(2)}',
                        Icons.attach_money),
                ],
              ),
            ),
          ),
          if (h.descricao != null && h.descricao!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _buildSecao('Descrição', h.descricao!, Icons.description_outlined),
          ],
          if (h.solucao != null && h.solucao!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _buildSecao('Solução Aplicada', h.solucao!, Icons.task_alt,
                cor: const Color(0xFF2E7D32), bg: const Color(0xFFF1F8E9)),
          ],
          if (h.pecasUtilizadas != null && h.pecasUtilizadas!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _buildSecao('Peças Utilizadas', h.pecasUtilizadas!, Icons.inventory_2_outlined),
          ],
          if (h.observacoes != null && h.observacoes!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _buildSecao('Observações', h.observacoes!, Icons.notes),
          ],
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _info(String label, String valor, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: Colors.grey.shade500),
          const SizedBox(width: 8),
          SizedBox(
            width: 100,
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

  Widget _buildSecao(String titulo, String conteudo, IconData icon,
      {Color? cor, Color? bg}) {
    return Card(
      margin: EdgeInsets.zero,
      color: bg,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, size: 16, color: cor ?? Colors.grey.shade600),
                const SizedBox(width: 6),
                Text(
                  titulo,
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    color: cor ?? Colors.grey.shade700,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(conteudo, style: const TextStyle(fontSize: 13)),
          ],
        ),
      ),
    );
  }
}
