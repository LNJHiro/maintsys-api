import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/ordem_servico.dart';
import '../../services/database_service.dart';
import '../../widgets/status_badge.dart';
import 'ordem_form_screen.dart';

class OrdemDetailScreen extends StatefulWidget {
  final int ordemId;
  const OrdemDetailScreen({super.key, required this.ordemId});

  @override
  State<OrdemDetailScreen> createState() => _OrdemDetailScreenState();
}

class _OrdemDetailScreenState extends State<OrdemDetailScreen> {
  OrdemServico? _ordem;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final o = await DatabaseService.getOrdemById(widget.ordemId);
    if (mounted) setState(() { _ordem = o; _loading = false; });
  }

  Future<void> _iniciar() async {
    await DatabaseService.iniciarOrdem(widget.ordemId);
    _load();
  }

  Future<void> _cancelar() async {
    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancelar ordem'),
        content: const Text('Confirma o cancelamento desta O.S.?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Não')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Cancelar O.S.', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (confirma == true) {
      await DatabaseService.cancelarOrdem(widget.ordemId);
      _load();
    }
  }

  Future<void> _concluir() async {
    final solucaoCtrl = TextEditingController();
    final pecasCtrl = TextEditingController();
    final tempoCtrl = TextEditingController();
    final custoCtrl = TextEditingController();
    final obsCtrl = TextEditingController();

    final ok = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
            16, 16, 16, MediaQuery.of(ctx).viewInsets.bottom + 16),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Concluir Ordem de Serviço',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              TextField(
                controller: solucaoCtrl,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Solução aplicada *',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: pecasCtrl,
                decoration: const InputDecoration(
                  labelText: 'Peças utilizadas',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: tempoCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Tempo parado (h)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: custoCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Custo (R\$)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: obsCtrl,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Observações',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    if (solucaoCtrl.text.trim().isEmpty) {
                      ScaffoldMessenger.of(ctx).showSnackBar(
                        const SnackBar(
                            content: Text('Informe a solução aplicada.')),
                      );
                      return;
                    }
                    Navigator.pop(ctx, true);
                  },
                  child: const Text('Concluir O.S.'),
                ),
              ),
            ],
          ),
        ),
      ),
    );

    if (ok == true) {
      await DatabaseService.concluirOrdem(
        widget.ordemId,
        solucaoCtrl.text.trim(),
        pecasUtilizadas:
            pecasCtrl.text.trim().isEmpty ? null : pecasCtrl.text.trim(),
        tempoParadaHoras: double.tryParse(tempoCtrl.text.trim()),
        custo: double.tryParse(custoCtrl.text.trim()),
        observacoes:
            obsCtrl.text.trim().isEmpty ? null : obsCtrl.text.trim(),
      );
      _load();
    }
  }

  Future<void> _editar() async {
    if (_ordem == null) return;
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => OrdemFormScreen(ordem: _ordem)),
    );
    if (ok == true) _load();
  }

  Future<void> _excluir() async {
    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Excluir ordem'),
        content: const Text('Excluir esta O.S.? Esta ação não pode ser desfeita.'),
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
      await DatabaseService.deleteOrdem(widget.ordemId);
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_ordem == null) {
      return const Scaffold(body: Center(child: Text('Ordem não encontrada.')));
    }
    final o = _ordem!;

    return Scaffold(
      appBar: AppBar(
        title: Text(o.numero),
        actions: [
          if (!o.isConcluida && !o.isCancelada)
            IconButton(icon: const Icon(Icons.edit), onPressed: _editar),
          IconButton(icon: const Icon(Icons.delete_outline), onPressed: _excluir),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildStatusCard(o),
          const SizedBox(height: 12),
          _buildDetalhesCard(o),
          if (o.solucao != null) ...[
            const SizedBox(height: 12),
            _buildSolucaoCard(o),
          ],
          const SizedBox(height: 20),
          _buildAcoes(o),
        ],
      ),
    );
  }

  Widget _buildStatusCard(OrdemServico o) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(o.numero,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      StatusBadge.tipo(o.tipo),
                      const SizedBox(width: 6),
                      StatusBadge.prioridade(o.prioridade),
                    ],
                  ),
                ],
              ),
            ),
            StatusBadge.ordem(o.status),
          ],
        ),
      ),
    );
  }

  Widget _buildDetalhesCard(OrdemServico o) {
    final fmt = DateFormat('dd/MM/yyyy HH:mm');
    final fmtD = DateFormat('dd/MM/yyyy');
    final dataAb = DateTime.tryParse(o.dataAbertura);
    final dataPrev = o.dataPrevista != null ? DateTime.tryParse(o.dataPrevista!) : null;
    final dataCon = o.dataConclusao != null ? DateTime.tryParse(o.dataConclusao!) : null;

    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Detalhes',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
            const Divider(height: 16),
            _info('Máquina', o.maquinaModelo ?? '#${o.maquinaId}',
                Icons.precision_manufacturing_outlined),
            _info('Técnico', o.tecnicoNome ?? '#${o.tecnicoId}',
                Icons.engineering_outlined),
            if (dataAb != null)
              _info('Abertura', fmt.format(dataAb), Icons.calendar_today_outlined),
            if (dataPrev != null)
              _info('Previsão', fmtD.format(dataPrev), Icons.event_outlined),
            if (dataCon != null)
              _info('Conclusão', fmt.format(dataCon), Icons.task_alt_outlined),
            if (o.descricao != null && o.descricao!.isNotEmpty) ...[
              const SizedBox(height: 8),
              const Text('Descrição:',
                  style: TextStyle(fontWeight: FontWeight.w500, fontSize: 13)),
              const SizedBox(height: 4),
              Text(o.descricao!, style: const TextStyle(fontSize: 13)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSolucaoCard(OrdemServico o) {
    return Card(
      margin: EdgeInsets.zero,
      color: const Color(0xFFF1F8E9),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.task_alt, color: Color(0xFF2E7D32), size: 18),
                SizedBox(width: 6),
                Text('Solução Aplicada',
                    style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF2E7D32))),
              ],
            ),
            const SizedBox(height: 8),
            Text(o.solucao!, style: const TextStyle(fontSize: 13)),
          ],
        ),
      ),
    );
  }

  Widget _buildAcoes(OrdemServico o) {
    if (o.isConcluida || o.isCancelada) {
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Center(
          child: Text(
            o.isConcluida ? 'Ordem concluída.' : 'Ordem cancelada.',
            style: TextStyle(color: Colors.grey.shade600),
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (o.isAberta)
          ElevatedButton.icon(
            icon: const Icon(Icons.play_arrow),
            label: const Text('Iniciar Atendimento'),
            onPressed: _iniciar,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
            ),
          ),
        if (o.isEmAndamento) ...[
          ElevatedButton.icon(
            icon: const Icon(Icons.task_alt),
            label: const Text('Concluir Ordem'),
            onPressed: _concluir,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF2E7D32),
              foregroundColor: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
        ],
        if (!o.isCancelada)
          OutlinedButton.icon(
            icon: const Icon(Icons.cancel_outlined, color: Colors.red),
            label: const Text('Cancelar O.S.', style: TextStyle(color: Colors.red)),
            onPressed: _cancelar,
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Colors.red),
            ),
          ),
      ],
    );
  }

  Widget _info(String label, String valor, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
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
