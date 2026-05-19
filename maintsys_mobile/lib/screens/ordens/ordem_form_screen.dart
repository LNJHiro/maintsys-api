import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/maquina.dart';
import '../../models/tecnico.dart';
import '../../models/ordem_servico.dart';
import '../../services/database_service.dart';

class OrdemFormScreen extends StatefulWidget {
  final OrdemServico? ordem;
  const OrdemFormScreen({super.key, this.ordem});

  @override
  State<OrdemFormScreen> createState() => _OrdemFormScreenState();
}

class _OrdemFormScreenState extends State<OrdemFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descricaoCtrl = TextEditingController();

  String _tipo = 'corretiva';
  String _prioridade = 'media';
  int? _maquinaId;
  int? _tecnicoId;
  DateTime? _dataPrevista;
  bool _loading = false;

  List<Maquina> _maquinas = [];
  List<Tecnico> _tecnicos = [];

  bool get _editando => widget.ordem != null;

  @override
  void initState() {
    super.initState();
    _carregarDados();
    if (_editando) {
      final o = widget.ordem!;
      _descricaoCtrl.text = o.descricao ?? '';
      _tipo = o.tipo;
      _prioridade = o.prioridade;
      _maquinaId = o.maquinaId;
      _tecnicoId = o.tecnicoId;
      if (o.dataPrevista != null) {
        _dataPrevista = DateTime.tryParse(o.dataPrevista!);
      }
    }
  }

  Future<void> _carregarDados() async {
    final maquinas = await DatabaseService.getMaquinas();
    final tecnicos = await DatabaseService.getTecnicosAtivos();
    if (mounted) {
      setState(() {
        _maquinas = maquinas;
        _tecnicos = tecnicos;
        if (!_editando) {
          if (maquinas.isNotEmpty) _maquinaId ??= maquinas.first.id;
          if (tecnicos.isNotEmpty) _tecnicoId ??= tecnicos.first.id;
        }
      });
    }
  }

  @override
  void dispose() {
    _descricaoCtrl.dispose();
    super.dispose();
  }

  Future<void> _selecionarData() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _dataPrevista ?? DateTime.now().add(const Duration(days: 7)),
      firstDate: DateTime.now(),
      lastDate: DateTime(2100),
    );
    if (picked != null) setState(() => _dataPrevista = picked);
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    if (_maquinaId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecione uma máquina.')),
      );
      return;
    }
    if (_tecnicoId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecione um técnico.')),
      );
      return;
    }
    setState(() => _loading = true);
    final ordem = OrdemServico(
      id: widget.ordem?.id,
      numero: widget.ordem?.numero ?? '',
      tipo: _tipo,
      status: widget.ordem?.status ?? 'aberta',
      prioridade: _prioridade,
      descricao: _descricaoCtrl.text.trim().isEmpty ? null : _descricaoCtrl.text.trim(),
      maquinaId: _maquinaId!,
      tecnicoId: _tecnicoId!,
      dataAbertura: widget.ordem?.dataAbertura ?? DateTime.now().toIso8601String(),
      dataPrevista: _dataPrevista != null
          ? DateFormat('yyyy-MM-dd').format(_dataPrevista!)
          : null,
    );
    try {
      if (_editando) {
        await DatabaseService.updateOrdem(ordem);
      } else {
        await DatabaseService.insertOrdem(ordem);
      }
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
    return Scaffold(
      appBar: AppBar(title: Text(_editando ? 'Editar Ordem' : 'Nova Ordem de Serviço')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _buildDropdownMaquina(),
            const SizedBox(height: 14),
            _buildDropdownTecnico(),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _tipo,
              decoration: const InputDecoration(labelText: 'Tipo'),
              items: OrdemServico.tipoOptions.map((t) {
                return DropdownMenuItem(
                  value: t,
                  child: Text(t == 'preventiva' ? 'Preventiva' : 'Corretiva'),
                );
              }).toList(),
              onChanged: (v) => setState(() => _tipo = v!),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _prioridade,
              decoration: const InputDecoration(labelText: 'Prioridade'),
              items: const [
                DropdownMenuItem(value: 'baixa', child: Text('Baixa')),
                DropdownMenuItem(value: 'media', child: Text('Média')),
                DropdownMenuItem(value: 'alta', child: Text('Alta')),
                DropdownMenuItem(value: 'critica', child: Text('Crítica')),
              ],
              onChanged: (v) => setState(() => _prioridade = v!),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _descricaoCtrl,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Descrição do problema',
                hintText: 'Descreva o problema ou serviço a realizar...',
              ),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: _selecionarData,
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'Data prevista (opcional)'),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(_dataPrevista != null
                        ? DateFormat('dd/MM/yyyy').format(_dataPrevista!)
                        : 'Selecionar data'),
                    Row(
                      children: [
                        if (_dataPrevista != null)
                          GestureDetector(
                            onTap: () => setState(() => _dataPrevista = null),
                            child: const Icon(Icons.clear, size: 18, color: Colors.grey),
                          ),
                        const SizedBox(width: 4),
                        const Icon(Icons.calendar_today, size: 18),
                      ],
                    ),
                  ],
                ),
              ),
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
                  : Text(_editando ? 'Salvar Alterações' : 'Abrir Ordem de Serviço'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdownMaquina() {
    if (_maquinas.isEmpty) {
      return const InputDecorator(
        decoration: InputDecoration(labelText: 'Máquina'),
        child: Text('Carregando...', style: TextStyle(color: Colors.grey)),
      );
    }
    return DropdownButtonFormField<int>(
      initialValue: _maquinaId,
      decoration: const InputDecoration(labelText: 'Máquina'),
      items: _maquinas
          .map((m) => DropdownMenuItem(
                value: m.id,
                child: Text('${m.modelo} (${m.numeroSerie})',
                    overflow: TextOverflow.ellipsis),
              ))
          .toList(),
      onChanged: (v) => setState(() => _maquinaId = v),
      validator: (v) => v == null ? 'Selecione uma máquina' : null,
    );
  }

  Widget _buildDropdownTecnico() {
    if (_tecnicos.isEmpty) {
      return const InputDecorator(
        decoration: InputDecoration(labelText: 'Técnico Responsável'),
        child: Text('Carregando...', style: TextStyle(color: Colors.grey)),
      );
    }
    return DropdownButtonFormField<int>(
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
    );
  }
}
