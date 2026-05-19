import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/maquina.dart';
import '../../services/database_service.dart';

class MaquinaFormScreen extends StatefulWidget {
  final Maquina? maquina;
  const MaquinaFormScreen({super.key, this.maquina});

  @override
  State<MaquinaFormScreen> createState() => _MaquinaFormScreenState();
}

class _MaquinaFormScreenState extends State<MaquinaFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _serieCtrl = TextEditingController();
  final _modeloCtrl = TextEditingController();
  final _fabricanteCtrl = TextEditingController();
  final _localizacaoCtrl = TextEditingController();
  final _descricaoCtrl = TextEditingController();
  String _status = 'operacional';
  DateTime _dataCadastro = DateTime.now();
  bool _loading = false;

  bool get _editando => widget.maquina != null;

  @override
  void initState() {
    super.initState();
    if (_editando) {
      final m = widget.maquina!;
      _serieCtrl.text = m.numeroSerie;
      _modeloCtrl.text = m.modelo;
      _fabricanteCtrl.text = m.fabricante;
      _localizacaoCtrl.text = m.localizacao;
      _descricaoCtrl.text = m.descricao ?? '';
      _status = m.status;
      _dataCadastro = DateTime.tryParse(m.dataCadastro) ?? DateTime.now();
    }
  }

  @override
  void dispose() {
    _serieCtrl.dispose();
    _modeloCtrl.dispose();
    _fabricanteCtrl.dispose();
    _localizacaoCtrl.dispose();
    _descricaoCtrl.dispose();
    super.dispose();
  }

  Future<void> _selecionarData() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _dataCadastro,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (picked != null) setState(() => _dataCadastro = picked);
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    final maquina = Maquina(
      id: widget.maquina?.id,
      numeroSerie: _serieCtrl.text.trim(),
      modelo: _modeloCtrl.text.trim(),
      fabricante: _fabricanteCtrl.text.trim(),
      localizacao: _localizacaoCtrl.text.trim(),
      dataCadastro: DateFormat('yyyy-MM-dd').format(_dataCadastro),
      status: _status,
      descricao: _descricaoCtrl.text.trim().isEmpty ? null : _descricaoCtrl.text.trim(),
    );
    try {
      if (_editando) {
        await DatabaseService.updateMaquina(maquina);
      } else {
        await DatabaseService.insertMaquina(maquina);
      }
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erro ao salvar: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_editando ? 'Editar Máquina' : 'Nova Máquina'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _campo('Número de Série', _serieCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            _campo('Modelo', _modeloCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            _campo('Fabricante', _fabricanteCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            _campo('Localização', _localizacaoCtrl, obrigatorio: true),
            const SizedBox(height: 14),
            _buildDropdownStatus(),
            const SizedBox(height: 14),
            _buildDataCadastro(),
            const SizedBox(height: 14),
            TextFormField(
              controller: _descricaoCtrl,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Descrição (opcional)'),
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
                  : Text(_editando ? 'Salvar Alterações' : 'Cadastrar Máquina'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _campo(String label, TextEditingController ctrl, {bool obrigatorio = false}) {
    return TextFormField(
      controller: ctrl,
      decoration: InputDecoration(labelText: label),
      validator: obrigatorio
          ? (v) => (v == null || v.trim().isEmpty) ? 'Campo obrigatório' : null
          : null,
    );
  }

  Widget _buildDropdownStatus() {
    return DropdownButtonFormField<String>(
      initialValue: _status,
      decoration: const InputDecoration(labelText: 'Status'),
      items: Maquina.statusOptions.map((s) {
        final label = switch (s) {
          'operacional' => 'Operacional',
          'em_manutencao' => 'Em Manutenção',
          'parada_critica' => 'Parada Crítica',
          'inativa' => 'Inativa',
          _ => s,
        };
        return DropdownMenuItem(value: s, child: Text(label));
      }).toList(),
      onChanged: (v) => setState(() => _status = v!),
    );
  }

  Widget _buildDataCadastro() {
    return InkWell(
      onTap: _selecionarData,
      child: InputDecorator(
        decoration: const InputDecoration(labelText: 'Data de Cadastro'),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(DateFormat('dd/MM/yyyy').format(_dataCadastro)),
            const Icon(Icons.calendar_today, size: 18),
          ],
        ),
      ),
    );
  }
}
