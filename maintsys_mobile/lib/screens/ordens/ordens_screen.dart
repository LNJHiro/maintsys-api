import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/ordem_servico.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/empty_state.dart';
import '../../theme.dart';
import 'ordem_form_screen.dart';
import 'ordem_detail_screen.dart';

class OrdensScreen extends StatefulWidget {
  const OrdensScreen({super.key});

  @override
  State<OrdensScreen> createState() => _OrdensScreenState();
}

class _OrdensScreenState extends State<OrdensScreen> {
  late Future<List<OrdemServico>> _future;
  String? _filtroStatus;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = DatabaseService.getOrdens(statusFiltro: _filtroStatus);
  }

  void _reload() => setState(() => _load());

  Future<void> _abrirFormulario() async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const OrdemFormScreen()),
    );
    if (ok == true) _reload();
  }

  Future<void> _abrirDetalhe(OrdemServico o) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => OrdemDetailScreen(ordemId: o.id!)),
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
      body: Column(
        children: [
          _buildFiltros(),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => _reload(),
              color: AppTheme.kPrimary,
              child: FutureBuilder<List<OrdemServico>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  final lista = snap.data ?? [];
                  if (lista.isEmpty) {
                    return EmptyState(
                      icon: Icons.assignment_outlined,
                      message: _filtroStatus != null
                          ? 'Nenhuma ordem com este status.'
                          : 'Nenhuma ordem de serviço.',
                      actionLabel: 'Criar ordem',
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
          ),
        ],
      ),
    );
  }

  Widget _buildFiltros() {
    const filtros = [
      (null, 'Todas'),
      ('aberta', 'Abertas'),
      ('em_andamento', 'Em Andamento'),
      ('concluida', 'Concluídas'),
      ('cancelada', 'Canceladas'),
    ];
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        child: Row(
          children: filtros.map((f) {
            final isSelected = _filtroStatus == f.$1;
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: FilterChip(
                label: Text(f.$2),
                selected: isSelected,
                onSelected: (_) => setState(() {
                  _filtroStatus = f.$1;
                  _load();
                }),
                selectedColor: AppTheme.kPrimary.withOpacity(0.15),
                checkmarkColor: AppTheme.kPrimary,
                labelStyle: TextStyle(
                  color: isSelected ? AppTheme.kPrimary : null,
                  fontWeight: isSelected ? FontWeight.w600 : null,
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildItem(OrdemServico o) {
    final fmt = DateFormat('dd/MM/yyyy');
    final dataAb = DateTime.tryParse(o.dataAbertura);

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _abrirDetalhe(o),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      o.numero,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ),
                  StatusBadge.ordem(o.status),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  Icon(Icons.precision_manufacturing_outlined,
                      size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      o.maquinaModelo ?? 'Máquina #${o.maquinaId}',
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 3),
              Row(
                children: [
                  Icon(Icons.engineering_outlined, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      o.tecnicoNome ?? 'Técnico #${o.tecnicoId}',
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  StatusBadge.tipo(o.tipo),
                  const SizedBox(width: 6),
                  StatusBadge.prioridade(o.prioridade),
                  const Spacer(),
                  if (dataAb != null)
                    Text(
                      fmt.format(dataAb),
                      style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                    ),
                ],
              ),
              if (o.descricao != null && o.descricao!.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(
                  o.descricao!,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
