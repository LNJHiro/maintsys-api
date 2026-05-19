import 'package:flutter/material.dart';
import '../../models/permission.dart';
import '../../services/database_service.dart';
import '../../theme.dart';

class AcessosScreen extends StatefulWidget {
  const AcessosScreen({super.key});

  @override
  State<AcessosScreen> createState() => _AcessosScreenState();
}

class _AcessosScreenState extends State<AcessosScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Controle de Acesso'),
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(icon: Icon(Icons.admin_panel_settings), text: 'Administrador'),
            Tab(icon: Icon(Icons.person), text: 'Usuário'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabs,
        children: const [
          _RolePermissionsTab(role: 'admin', label: 'Administrador'),
          _RolePermissionsTab(role: 'usuario', label: 'Usuário'),
        ],
      ),
    );
  }
}

class _RolePermissionsTab extends StatefulWidget {
  final String role;
  final String label;
  const _RolePermissionsTab({required this.role, required this.label});

  @override
  State<_RolePermissionsTab> createState() => _RolePermissionsTabState();
}

class _RolePermissionsTabState extends State<_RolePermissionsTab>
    with AutomaticKeepAliveClientMixin {
  Map<String, List<Permission>> _byModulo = {};
  Set<int> _selected = {};
  bool _loading = true;
  bool _saving = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final byModulo = await DatabaseService.getPermissionsByModulo();
    final ids = await DatabaseService.getRolePermissionIds(widget.role);
    setState(() {
      _byModulo = byModulo;
      _selected = ids.toSet();
      _loading = false;
    });
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    await DatabaseService.setRolePermissions(widget.role, _selected.toList());
    setState(() => _saving = false);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Permissões do role "${widget.label}" atualizadas.'),
        backgroundColor: Colors.green,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    if (_loading) return const Center(child: CircularProgressIndicator());

    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          color: Colors.blue.shade50,
          child: Row(
            children: [
              Icon(Icons.info_outline, color: Colors.blue.shade700, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Definindo permissões padrão do role "${widget.label}". Usuários sem configuração individual recebem essas permissões.',
                  style: const TextStyle(fontSize: 12),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: _byModulo.entries.map(_buildModulo).toList(),
          ),
        ),
        SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: ElevatedButton.icon(
              onPressed: _saving ? null : _save,
              icon: _saving
                  ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Icon(Icons.save),
              label: Text('Salvar Permissões (${widget.label})'),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildModulo(MapEntry<String, List<Permission>> entry) {
    final perms = entry.value;
    final label = perms.first.moduloLabel;
    final allSelected = perms.every((p) => _selected.contains(p.id));

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ExpansionTile(
        initiallyExpanded: true,
        leading: Icon(_moduloIcon(entry.key), color: AppTheme.kPrimary),
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
