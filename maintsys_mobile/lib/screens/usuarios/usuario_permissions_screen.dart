import 'package:flutter/material.dart';
import '../../models/permission.dart';
import '../../models/user.dart';
import '../../services/database_service.dart';
import '../../theme.dart';

class UsuarioPermissionsScreen extends StatefulWidget {
  final User user;
  const UsuarioPermissionsScreen({super.key, required this.user});

  @override
  State<UsuarioPermissionsScreen> createState() => _UsuarioPermissionsScreenState();
}

class _UsuarioPermissionsScreenState extends State<UsuarioPermissionsScreen> {
  Map<String, List<Permission>> _byModulo = {};
  Set<int> _selected = {};
  bool _hasIndividual = false;
  bool _loading = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final byModulo = await DatabaseService.getPermissionsByModulo();
    final hasIndividual =
        await DatabaseService.userHasIndividualPermissions(widget.user.id!);
    final ids = hasIndividual
        ? await DatabaseService.getUserPermissionIds(widget.user.id!)
        : await DatabaseService.getRolePermissionIds(widget.user.role);
    setState(() {
      _byModulo = byModulo;
      _selected = ids.toSet();
      _hasIndividual = hasIndividual;
      _loading = false;
    });
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    await DatabaseService.setUserPermissions(
      widget.user.id!,
      _selected.toList(),
    );
    setState(() {
      _saving = false;
      _hasIndividual = true;
    });
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Permissões individuais salvas.'), backgroundColor: Colors.green),
    );
  }

  Future<void> _resetToRole() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Restaurar padrão do role'),
        content: const Text(
          'Isso remove a configuração individual deste usuário e faz com que ele volte a usar as permissões padrão do role.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Restaurar', style: TextStyle(color: AppTheme.kPrimary)),
          ),
        ],
      ),
    );
    if (ok != true) return;
    await DatabaseService.clearUserPermissions(widget.user.id!);
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Permissões: ${widget.user.name}'),
        actions: [
          if (_hasIndividual)
            IconButton(
              icon: const Icon(Icons.restart_alt),
              tooltip: 'Restaurar padrão do role',
              onPressed: _resetToRole,
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _hasIndividual ? Colors.amber.shade50 : Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: _hasIndividual ? Colors.amber.shade300 : Colors.blue.shade200,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        _hasIndividual ? Icons.person_pin : Icons.group,
                        color: _hasIndividual ? Colors.amber.shade800 : Colors.blue.shade700,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          _hasIndividual
                              ? 'Este usuário possui configuração individual. Marcações abaixo são exclusivas dele.'
                              : 'Este usuário usa as permissões padrão do role "${widget.user.roleLabel}". Ao salvar, será criada uma configuração individual.',
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                ..._byModulo.entries.map(_buildModulo),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: _saving ? null : _save,
                  icon: _saving
                      ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.save),
                  label: const Text('Salvar Permissões'),
                ),
              ],
            ),
    );
  }

  Widget _buildModulo(MapEntry<String, List<Permission>> entry) {
    final modulo = entry.key;
    final perms = entry.value;
    final label = perms.first.moduloLabel;
    final allSelected = perms.every((p) => _selected.contains(p.id));
    final someSelected = perms.any((p) => _selected.contains(p.id));

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ExpansionTile(
        initiallyExpanded: true,
        leading: Icon(_moduloIcon(modulo), color: AppTheme.kPrimary),
        title: Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(
          '${perms.where((p) => _selected.contains(p.id)).length} de ${perms.length} selecionadas',
          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
        ),
        trailing: Checkbox(
          value: allSelected,
          tristate: true,
          onChanged: (v) {
            setState(() {
              if (allSelected) {
                _selected.removeAll(perms.map((p) => p.id!));
              } else {
                _selected.addAll(perms.map((p) => p.id!));
              }
            });
          },
          activeColor: someSelected ? AppTheme.kPrimary : null,
        ),
        children: perms
            .map((p) => CheckboxListTile(
                  value: _selected.contains(p.id),
                  onChanged: (v) {
                    setState(() {
                      if (v == true) {
                        _selected.add(p.id!);
                      } else {
                        _selected.remove(p.id!);
                      }
                    });
                  },
                  title: Text(p.descricao),
                  subtitle: Text(p.name, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                  dense: true,
                  controlAffinity: ListTileControlAffinity.leading,
                ))
            .toList(),
      ),
    );
  }

  IconData _moduloIcon(String modulo) => switch (modulo) {
        'maquinas' => Icons.precision_manufacturing,
        'tecnicos' => Icons.engineering,
        'ordens' => Icons.assignment,
        'historico' => Icons.history,
        'dashboard' => Icons.dashboard,
        _ => Icons.shield_outlined,
      };
}
