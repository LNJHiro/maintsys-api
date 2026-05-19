import 'package:flutter/material.dart';
import '../../models/maquina.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/empty_state.dart';
import '../../theme.dart';
import 'maquina_form_screen.dart';
import 'maquina_detail_screen.dart';

class MaquinasScreen extends StatefulWidget {
  const MaquinasScreen({super.key});

  @override
  State<MaquinasScreen> createState() => _MaquinasScreenState();
}

class _MaquinasScreenState extends State<MaquinasScreen> {
  late Future<List<Maquina>> _future;
  String? _filtroStatus;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = DatabaseService.getMaquinas();
  }

  void _reload() => setState(() => _load());

  Future<void> _abrirFormulario([Maquina? maquina]) async {
    final resultado = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => MaquinaFormScreen(maquina: maquina)),
    );
    if (resultado == true) _reload();
  }

  Future<void> _abrirDetalhe(Maquina maquina) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => MaquinaDetailScreen(maquinaId: maquina.id!)),
    );
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(
        onPressed: () => _abrirFormulario(),
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          _buildFiltros(),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => _reload(),
              color: AppTheme.kPrimary,
              child: FutureBuilder<List<Maquina>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return Center(child: Text('Erro: ${snap.error}'));
                  }
                  var lista = snap.data!;
                  if (_filtroStatus != null) {
                    lista = lista.where((m) => m.status == _filtroStatus).toList();
                  }
                  if (lista.isEmpty) {
                    return EmptyState(
                      icon: Icons.precision_manufacturing_outlined,
                      message: _filtroStatus != null
                          ? 'Nenhuma máquina com este status.'
                          : 'Nenhuma máquina cadastrada.',
                      actionLabel: 'Cadastrar máquina',
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

  Widget _buildFiltros() {
    const filtros = [
      (null, 'Todas'),
      ('operacional', 'Operacional'),
      ('em_manutencao', 'Manutenção'),
      ('parada_critica', 'Crítica'),
      ('inativa', 'Inativa'),
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
                onSelected: (_) => setState(() => _filtroStatus = f.$1),
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

  Widget _buildItem(Maquina m) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _abrirDetalhe(m),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppTheme.kPrimary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.precision_manufacturing,
                    color: AppTheme.kPrimary, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      m.modelo,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      m.fabricante,
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(Icons.location_on_outlined,
                            size: 13, color: Colors.grey.shade500),
                        const SizedBox(width: 2),
                        Expanded(
                          child: Text(
                            m.localizacao,
                            style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    StatusBadge.maquina(m.status),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
