import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/historico_manutencao.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/empty_state.dart';
import '../../theme.dart';
import 'historico_form_screen.dart';
import 'historico_detail_screen.dart';

class HistoricoScreen extends StatefulWidget {
  const HistoricoScreen({super.key});

  @override
  State<HistoricoScreen> createState() => _HistoricoScreenState();
}

class _HistoricoScreenState extends State<HistoricoScreen> {
  late Future<List<HistoricoManutencao>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = DatabaseService.getHistorico();
  }

  void _reload() => setState(() => _load());

  Future<void> _abrirFormulario() async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const HistoricoFormScreen()),
    );
    if (ok == true) _reload();
  }

  Future<void> _abrirDetalhe(HistoricoManutencao h) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => HistoricoDetailScreen(historicoId: h.id!)),
    );
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(
        onPressed: _abrirFormulario,
        child: const Icon(Icons.add),
      ),
      body: RefreshIndicator(
        onRefresh: () async => _reload(),
        color: AppTheme.kPrimary,
        child: FutureBuilder<List<HistoricoManutencao>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            final lista = snap.data ?? [];
            if (lista.isEmpty) {
              return EmptyState(
                icon: Icons.history,
                message: 'Nenhum registro no histórico.',
                actionLabel: 'Registrar manutenção',
                onAction: _abrirFormulario,
              );
            }
            return ListView.builder(
              itemCount: lista.length,
              itemBuilder: (_, i) => _buildItem(lista[i]),
            );
          },
        ),
      ),
    );
  }

  Widget _buildItem(HistoricoManutencao h) {
    final fmt = DateFormat('dd/MM/yyyy');
    final dataInicio = h.dataInicio != null ? DateTime.tryParse(h.dataInicio!) : null;
    final dataFim = h.dataFim != null ? DateTime.tryParse(h.dataFim!) : null;

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _abrirDetalhe(h),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: h.tipo == 'preventiva'
                          ? const Color(0xFF1565C0).withOpacity(0.1)
                          : const Color(0xFFE65100).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      h.tipo == 'preventiva' ? Icons.shield_outlined : Icons.build_outlined,
                      color: h.tipo == 'preventiva'
                          ? const Color(0xFF1565C0)
                          : const Color(0xFFE65100),
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          h.maquinaModelo ?? 'Máquina #${h.maquinaId}',
                          style: const TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 14),
                        ),
                        Text(
                          h.tecnicoNome ?? 'Técnico #${h.tecnicoId}',
                          style: TextStyle(
                              color: Colors.grey.shade600, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  StatusBadge.tipo(h.tipo),
                ],
              ),
              if (h.descricao != null && h.descricao!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  h.descricao!,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                ),
              ],
              const SizedBox(height: 8),
              Row(
                children: [
                  if (dataInicio != null) ...[
                    Icon(Icons.calendar_today, size: 12, color: Colors.grey.shade500),
                    const SizedBox(width: 4),
                    Text(
                      fmt.format(dataInicio),
                      style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                    ),
                    if (dataFim != null) ...[
                      Text(' → ', style: TextStyle(color: Colors.grey.shade400, fontSize: 11)),
                      Text(
                        fmt.format(dataFim),
                        style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                      ),
                    ],
                  ],
                  const Spacer(),
                  if (h.custo != null)
                    Text(
                      'R\$ ${h.custo!.toStringAsFixed(2)}',
                      style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          color: Color(0xFF2E7D32)),
                    ),
                ],
              ),
              if (h.ordemNumero != null) ...[
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.assignment_outlined, size: 12, color: Colors.grey.shade400),
                    const SizedBox(width: 4),
                    Text(
                      'O.S.: ${h.ordemNumero}',
                      style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
