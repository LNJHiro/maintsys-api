import 'package:flutter/material.dart';
import '../../models/user.dart';
import '../../services/auth_service.dart';
import '../../services/database_service.dart';
import '../../theme.dart';
import '../../widgets/empty_state.dart';
import 'usuario_form_screen.dart';
import 'usuario_permissions_screen.dart';

class UsuariosScreen extends StatefulWidget {
  const UsuariosScreen({super.key});

  @override
  State<UsuariosScreen> createState() => _UsuariosScreenState();
}

class _UsuariosScreenState extends State<UsuariosScreen> {
  late Future<List<User>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = DatabaseService.getUsers(excludeMaster: !AuthService.isMaster);
  }

  Future<void> _refresh() async {
    setState(() => _load());
    await _future;
  }

  Future<void> _abrirForm({User? user}) async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => UsuarioFormScreen(user: user)),
    );
    if (ok == true) _refresh();
  }

  Future<void> _confirmarDelete(User user) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Excluir usuário'),
        content: Text('Tem certeza que deseja excluir "${user.name}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Excluir', style: TextStyle(color: AppTheme.kPrimary)),
          ),
        ],
      ),
    );
    if (ok != true) return;
    await DatabaseService.deleteUser(user.id!);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Usuário "${user.name}" excluído.'), backgroundColor: Colors.green),
    );
    _refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Usuários')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        color: AppTheme.kPrimary,
        child: FutureBuilder<List<User>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            final users = snap.data ?? const <User>[];
            if (users.isEmpty) {
              return ListView(children: const [
                SizedBox(height: 80),
                EmptyState(
                  icon: Icons.group_outlined,
                  message: 'Nenhum usuário cadastrado.',
                ),
              ]);
            }
            return ListView.builder(
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: users.length,
              itemBuilder: (_, i) => _UserCard(
                user: users[i],
                onEdit: () => _abrirForm(user: users[i]),
                onDelete: users[i].isMaster ? null : () => _confirmarDelete(users[i]),
                onPermissions: users[i].isMaster
                    ? null
                    : () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => UsuarioPermissionsScreen(user: users[i]),
                          ),
                        ).then((_) => _refresh()),
              ),
            );
          },
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _abrirForm(),
        icon: const Icon(Icons.person_add),
        label: const Text('Novo Usuário'),
      ),
    );
  }
}

class _UserCard extends StatelessWidget {
  final User user;
  final VoidCallback onEdit;
  final VoidCallback? onDelete;
  final VoidCallback? onPermissions;

  const _UserCard({
    required this.user,
    required this.onEdit,
    this.onDelete,
    this.onPermissions,
  });

  Color get _roleColor => switch (user.role) {
        'admin_master' => const Color(0xFFC62828),
        'admin' => const Color(0xFF1565C0),
        _ => const Color(0xFF2E7D32),
      };

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            CircleAvatar(
              backgroundColor: _roleColor.withOpacity(0.15),
              child: Text(
                user.name.isNotEmpty ? user.name[0].toUpperCase() : '?',
                style: TextStyle(color: _roleColor, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(user.name, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 2),
                  Text(user.email, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: _roleColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      user.roleLabel,
                      style: TextStyle(color: _roleColor, fontSize: 11, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert),
              onSelected: (v) {
                if (v == 'edit') onEdit();
                if (v == 'perm' && onPermissions != null) onPermissions!();
                if (v == 'del' && onDelete != null) onDelete!();
              },
              itemBuilder: (_) => [
                const PopupMenuItem(value: 'edit', child: ListTile(leading: Icon(Icons.edit), title: Text('Editar'), dense: true)),
                if (onPermissions != null)
                  const PopupMenuItem(value: 'perm', child: ListTile(leading: Icon(Icons.shield_outlined), title: Text('Permissões'), dense: true)),
                if (onDelete != null)
                  const PopupMenuItem(value: 'del', child: ListTile(leading: Icon(Icons.delete, color: Colors.red), title: Text('Excluir', style: TextStyle(color: Colors.red)), dense: true)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
