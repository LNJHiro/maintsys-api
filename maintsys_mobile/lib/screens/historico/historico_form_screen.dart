import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/maquina.dart';
import '../../models/tecnico.dart';
import '../../models/historico_manutencao.dart';
import '../../services/database_service.dart';

class HistoricoFormScreen extends StatefulWidget {
  const HistoricoFormScreen({super.key});

  @override
  State<HistoricoFormScreen> createState() => _HistoricoFormScreenState();
}

class _HistoricoFormScreenState extends State<HistoricoFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descricaoCtrl = TextEditingController();
  final _solucaoCtrl = TextEditingController();
  final _pecasCtrl = TextEditingController();
  final _tempoCtrl = TextEditingController();
  final _custoCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();

  String _tipo = 'corretiva';
  int? _maquinaId;
  int? _tecnicoId;
  DateTime _dataInicio = DateTime.now();
  DateTime _dataFim = DateTime.now();
  bool _loading = false;

  List<Maquina> _maquinas = [];
  List<Tecnico> _tecnicos = [];

  @override
  void initState() {
    super.initState();
    _carregarDados();
  }

  Future<void> _carregarDados() async {
    final m = await DatabaseService.getMaquinas();
    final t = await DatabaseService.getTecnicos();
    if (mounted) {
      setState(() {
        _maquinas = m;
        _tecnicos = t;
        if (m.isNotEmpty) _maquinaId = m.first.id;
        if (t.isNotEmpty) _tecnicoId = t.first.id;
      });
    }
  }

  @override
  void dispose() {
    _descricaoCtrl.dispose();
    _solucaoCtrl.dispose();
    _pecasCtrl.dispose();
    _tempoCtrl.dispose();
    _custoCtrl.dispose();
    _obsCtrl.dispose();
    super.dispose();
  }

  Future<void> _selecionarData(bool isInicio) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isInicio ? _dataInicio : _dataFim,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (picked != null) {
      setState(() {
        if (isInicio) {
          _dataInicio = picked;
          if (_dataFim.isBefore(_dataInicio)) _dataFim = _dataInicio;
        } else {
          _dataFim = picked;
        }
      });
    }
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    if (_maquinaId == null || _tecnicoId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecione máquina e técnico.')),
      );
      return;
    }
    setState(() => _loading = true);
    final h = HistoricoManutencao(
      maquinaId: _maquinaId!,
      tecnicoId: _tecnicoId!,
      tipo: _tipo,
      descricao: _descricaoCtrl.text.trim().isEmpty ? null : _descricaoCtrl.text.trim(),
      solucao: _solucaoCtrl.text.trim().isEmpty ? null : _solucaoCtrl.text.trim(),
      pecasUtilizadas: _pecasCtrl.text.trim().isEmpty ? null : _pecasCtrl.text.trim(),
      tempoParadaHoras: double.tryParse(_tempoCtrl.text.trim()),
      custo: double.tryParse(_custoCtrl.text.trim()),
      dataInicio: _dataInicio.toIso8601String(),
      dataFim: _dataFim.toIso8601String(),
      observacoes: _obsCtrl.text.trim().isEmpty ? null : _obsCtrl.text.trim(),
    );
    try {
      await DatabaseService.insertHistorico(h);
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erro: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final fmt = DateFormat('dd/MM/yyyy');
    return Scaffold(
      appBar: AppBar(title: const Text('Registrar Manutenção')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (_maquinas.isEmpty)
              const InputDecorator(
                decoration: InputDecoration(labelText: 'Máquina'),
                child: Text('Carregando...', style: TextStyle(color: Colors.grey)),
              )
            else
              DropdownButtonFormField<int>(
                initialValue: _maquinaId,
                decoration: const InputDecoration(labelText: 'Máquina'),
                items: _maquinas
                    .map((m) => DropdownMenuItem(
                          value: m.id,
                          child: Text(m.modelo, overflow: TextOverflow.ellipsis),
                        ))
                    .toList(),
                onChanged: (v) => setState(() => _maquinaId = v),
                validator: (v) => v == null ? 'Selecione uma máquina' : null,
              ),
            const SizedBox(height: 14),
            if (_tecnicos.isEmpty)
              const InputDecorator(
                decoration: InputDecoration(labelText: 'Técnico'),
                child: Text('Carregando...', style: TextStyle(color: Colors.grey)),
              )
            else
              DropdownButtonFormField<int>(
                initialValue: _tecnicoId,
                decoration: const InputDecoration(labelText: 'Técnico Responsável'),
                items: _tecnicos
                    .map((t) => DropdownMenuItem(
                          value: t.id,
                          child: Text(t.nome, overflow: TextOverflow.ellipsis),
                        ))
                    .toList(),
                onChanged: (v) => setState(() => _tecnicoId = v),
                validator: (v) => v == null ? 'Selecione um técnico' : null,
              ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _tipo,
              decoration: const InputDecoration(labelText: 'Tipo'),
              items: const [
                DropdownMenuItem(value: 'preventiva', child: Text('Preventiva')),
                DropdownMenuItem(value: 'corretiva', child: Text('Corretiva')),
              ],
              onChanged: (v) => setState(() => _tipo = v!),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () => _selecionarData(true),
                    child: InputDecorator(
                      decoration: const InputDecoration(labelText: 'Data início'),
                      child: Text(fmt.format(_dataInicio)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: InkWell(
                    onTap: () => _selecionarData(false),
                    child: InputDecorator(
                      decoration: const InputDecoration(labelText: 'Data fim'),
                      child: Text(fmt.format(_dataFim)),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _descricaoCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Descrição do serviço'),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _solucaoCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Solução aplicada'),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _pecasCtrl,
              decoration: const InputDecoration(labelText: 'Peças utilizadas'),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _tempoCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Tempo parado (h)'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _custoCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Custo (R\$)'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _obsCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Observações'),
            ),
            const SizedBox(height: 28),
            ElevatedButton(
              onPressed: _loading ? null : _salvar,
              child: _loading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Text('Registrar Manutenção'),
            ),
          ],
        ),
      ),
    );
  }
}
