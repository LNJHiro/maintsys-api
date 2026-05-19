import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/maquina.dart';
import '../../models/ordem_servico.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import '../../theme.dart';
import 'maquina_form_screen.dart';
import '../ordens/ordem_detail_screen.dart';

class MaquinaDetailScreen extends StatefulWidget {
  final int maquinaId;
  const MaquinaDetailScreen({super.key, required this.maquinaId});

  @override
  State<MaquinaDetailScreen> createState() => _MaquinaDetailScreenState();
}

class _MaquinaDetailScreenState extends State<MaquinaDetailScreen> {
  Maquina? _maquina;
  List<OrdemServico> _ordens = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final m = await DatabaseService.getMaquinaById(widget.maquinaId);
    final o = await DatabaseService.getOrdensByMaquina(widget.maquinaId);
    if (mounted) setState(() { _maquina = m; _ordens = o; _loading = false; });
  }

  Future<void> _editar() async {
    if (_maquina == null) return;
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => MaquinaFormScreen(maquina: _maquina)),
    );
    if (ok == true) _load();
  }

  Future<void> _excluir() async {
    if (_maquina == null) return;
    final pode = await DatabaseService.canDeleteMaquina(_maquina!.id!);
    if (!mounted) return;
    if (!pode) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Não é possível excluir: máquina possui ordens vinculadas.'),
        backgroundColor: Colors.red,
      ));
      return;
    }
    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Excluir máquina'),
        content: Text('Excluir "${_maquina!.modelo}"? Esta ação não pode ser desfeita.'),
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
      await DatabaseService.deleteMaquina(_maquina!.id!);
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (_maquina == null) {
      return const Scaffold(body: Center(child: Text('Máquina não encontrada.')));
    }
    final m = _maquina!;
    return Scaffold(
      appBar: AppBar(
        title: Text(m.modelo, overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(icon: const Icon(Icons.edit), onPressed: _editar),
          IconButton(icon: const Icon(Icons.delete_outline), onPressed: _excluir),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildInfoCard(m),
          const SizedBox(height: 20),
          _buildOrdensSection(),
        ],
      ),
    );
  }

  Widget _buildInfoCard(Maquina m) {
    final fmt = DateFormat('dd/MM/yyyy');
    final dataCad = DateTime.tryParse(m.dataCadastro);
    return Card(
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
                    color: AppTheme.kPrimary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.precision_manufacturing,
                      color: AppTheme.kPrimary, size: 28),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(m.modelo,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      Text(m.fabricante,
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 14)),
                    ],
                  ),
                ),
                StatusBadge.maquina(m.status),
              ],
            ),
            const Divider(height: 24),
            _info('Nº de Série', m.numeroSerie, Icons.qr_code),
            _info('Localização', m.localizacao, Icons.location_on_outlined),
            _info('Cadastro',
                dataCad != null ? fmt.format(dataCad) : m.dataCadastro,
                Icons.calendar_today_outlined),
            if (m.descricao != null && m.descricao!.isNotEmpty)
              _info('Descrição', m.descricao!, Icons.description_outlined),
          ],
        ),
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

  Widget _buildOrdensSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.assignment, size: 18, color: AppTheme.kPrimary),
            SizedBox(width: 8),
            Text('Ordens de Serviço',
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
                  style: TextStyle(color: Colors.grey)),
            ),
          )
        else
          ..._ordens.map((o) => Card(
                child: ListTile(
                  leading: StatusBadge.ordem(o.status),
                  title: Text(o.numero, style: const TextStyle(fontWeight: FontWeight.w600)),
                  subtitle: Text('${o.tipoLabel} • ${o.prioridadeLabel}'),
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
    );
  }
}
