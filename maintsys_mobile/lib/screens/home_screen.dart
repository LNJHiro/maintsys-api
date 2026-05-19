import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../theme.dart';
import 'login_screen.dart';
import 'dashboard_screen.dart';
import 'maquinas/maquinas_screen.dart';
import 'tecnicos/tecnicos_screen.dart';
import 'ordens/ordens_screen.dart';
import 'historico/historico_screen.dart';
import 'profile/profile_screen.dart';
import 'usuarios/usuarios_screen.dart';
import 'acessos/acessos_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  static const _destinos = [
    NavigationDestination(
      icon: Icon(Icons.dashboard_outlined),
      selectedIcon: Icon(Icons.dashboard),
      label: 'Dashboard',
    ),
    NavigationDestination(
      icon: Icon(Icons.precision_manufacturing_outlined),
      selectedIcon: Icon(Icons.precision_manufacturing),
      label: 'Máquinas',
    ),
    NavigationDestination(
      icon: Icon(Icons.engineering_outlined),
      selectedIcon: Icon(Icons.engineering),
      label: 'Técnicos',
    ),
    NavigationDestination(
      icon: Icon(Icons.assignment_outlined),
      selectedIcon: Icon(Icons.assignment),
      label: 'Ordens',
    ),
    NavigationDestination(
      icon: Icon(Icons.history_outlined),
      selectedIcon: Icon(Icons.history),
      label: 'Histórico',
    ),
  ];

  final _telas = const [
    DashboardScreen(),
    MaquinasScreen(),
    TecnicosScreen(),
    OrdensScreen(),
    HistoricoScreen(),
  ];

  void _logout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sair'),
        content: const Text('Deseja encerrar sua sessão?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
          TextButton(
            onPressed: () {
              AuthService.logout();
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            child: const Text('Sair', style: TextStyle(color: AppTheme.kPrimary)),
          ),
        ],
      ),
    );
  }

  void _abrir(Widget page) {
    Navigator.pop(context);
    Navigator.push(context, MaterialPageRoute(builder: (_) => page));
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    return Scaffold(
      appBar: AppBar(
        title: const Text('MaintSys'),
        actions: [
          if (user != null)
            Padding(
              padding: const EdgeInsets.only(right: 4),
              child: Row(
                children: [
                  Icon(Icons.account_circle, size: 20, color: Colors.white.withOpacity(0.9)),
                  const SizedBox(width: 6),
                  Text(
                    user.name,
                    style: const TextStyle(fontSize: 14, color: Colors.white),
                  ),
                  const SizedBox(width: 4),
                ],
              ),
            ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Sair',
            onPressed: _logout,
          ),
        ],
      ),
      drawer: _buildDrawer(user),
      body: IndexedStack(index: _currentIndex, children: _telas),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (i) => setState(() => _currentIndex = i),
        destinations: _destinos,
        height: 65,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
      ),
    );
  }

  Widget _buildDrawer(User? user) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DrawerHeader(
            decoration: const BoxDecoration(color: AppTheme.kPrimary),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 28,
                  backgroundColor: Colors.white,
                  child: Text(
                    user?.name.isNotEmpty == true ? user!.name[0].toUpperCase() : '?',
                    style: const TextStyle(color: AppTheme.kPrimary, fontSize: 24, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  user?.name ?? 'Usuário',
                  style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600),
                ),
                if (user != null)
                  Text(
                    user.email,
                    style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 12),
                  ),
                if (user != null)
                  Container(
                    margin: const EdgeInsets.only(top: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      user.roleLabel,
                      style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                    ),
                  ),
              ],
            ),
          ),
          ListTile(
            leading: const Icon(Icons.person_outline),
            title: const Text('Meu Perfil'),
            onTap: () => _abrir(const ProfileScreen()),
          ),
          if (AuthService.isAdmin)
            const Divider(),
          if (AuthService.isAdmin)
            ListTile(
              leading: const Icon(Icons.group_outlined),
              title: const Text('Usuários'),
              onTap: () => _abrir(const UsuariosScreen()),
            ),
          if (AuthService.isAdmin)
            ListTile(
              leading: const Icon(Icons.shield_outlined),
              title: const Text('Controle de Acesso'),
              onTap: () => _abrir(const AcessosScreen()),
            ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.logout),
            title: const Text('Sair'),
            onTap: () {
              Navigator.pop(context);
              _logout();
            },
          ),
        ],
      ),
    );
  }
}
