import 'package:flutter/material.dart';
import '../services/database_service.dart';
import '../theme.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<Map<String, dynamic>> _statsFuture;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _statsFuture = DatabaseService.getDashboardStats();
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () async {
        setState(() => _load());
        await _statsFuture;
      },
      color: AppTheme.kPrimary,
      child: FutureBuilder<Map<String, dynamic>>(
        future: _statsFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Text('Erro: ${snap.error}'));
          }
          final stats = snap.data!;
          final maq = stats['maquinas'] as Map<String, int>;
          final ord = stats['ordens'] as Map<String, int>;
          final tecAtivos = stats['tecnicos_ativos'] as int;
          final totalHist = stats['total_historico'] as int;
          final paradas = stats['paradas_criticas'] as int;

          final totalMaq = maq.values.fold(0, (a, b) => a + b);
          final totalOrd = ord.values.fold(0, (a, b) => a + b);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (paradas > 0) _buildAlerta(paradas),
              const SizedBox(height: 8),
              _buildSecao('Máquinas', Icons.precision_manufacturing),
              const SizedBox(height: 8),
              Row(children: [
                _buildCard('Total', '$totalMaq', Colors.blueGrey, Icons.storage),
                const SizedBox(width: 8),
                _buildCard('Operacionais', '${maq['operacional'] ?? 0}',
                    const Color(0xFF2E7D32), Icons.check_circle_outline),
              ]),
              const SizedBox(height: 8),
              Row(children: [
                _buildCard('Em Manutenção', '${maq['em_manutencao'] ?? 0}',
                    const Color(0xFFE65100), Icons.build_outlined),
                const SizedBox(width: 8),
                _buildCard('Parada Crítica', '${maq['parada_critica'] ?? 0}',
                    const Color(0xFFC62828), Icons.warning_outlined),
              ]),
              const SizedBox(height: 20),
              _buildSecao('Ordens de Serviço', Icons.assignment),
              const SizedBox(height: 8),
              Row(children: [
                _buildCard('Total', '$totalOrd', Colors.blueGrey, Icons.list_alt),
                const SizedBox(width: 8),
                _buildCard('Abertas', '${ord['aberta'] ?? 0}',
                    const Color(0xFF1565C0), Icons.inbox_outlined),
              ]),
              const SizedBox(height: 8),
              Row(children: [
                _buildCard('Em Andamento', '${ord['em_andamento'] ?? 0}',
                    const Color(0xFFE65100), Icons.sync),
                const SizedBox(width: 8),
                _buildCard('Concluídas', '${ord['concluida'] ?? 0}',
                    const Color(0xFF2E7D32), Icons.task_alt),
              ]),
              const SizedBox(height: 20),
              _buildSecao('Equipe & Histórico', Icons.group),
              const SizedBox(height: 8),
              Row(children: [
                _buildCard('Técnicos Ativos', '$tecAtivos',
                    const Color(0xFF6A1B9A), Icons.engineering),
                const SizedBox(width: 8),
                _buildCard('Histórico', '$totalHist',
                    const Color(0xFF00695C), Icons.history),
              ]),
              const SizedBox(height: 16),
            ],
          );
        },
      ),
    );
  }

  Widget _buildAlerta(int qtd) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFEBEE),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFEF9A9A)),
      ),
      child: Row(
        children: [
          const Icon(Icons.warning, color: Color(0xFFC62828), size: 28),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Alerta de Parada Crítica',
                  style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFC62828)),
                ),
                Text(
                  '$qtd máquina${qtd > 1 ? 's' : ''} em parada crítica. Atenção imediata necessária!',
                  style: const TextStyle(fontSize: 13, color: Color(0xFFC62828)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSecao(String titulo, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppTheme.kPrimary),
        const SizedBox(width: 8),
        Text(
          titulo,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }

  Widget _buildCard(String titulo, String valor, Color color, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.25)),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.08),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(icon, color: color, size: 22),
                Text(
                  valor,
                  style: TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              titulo,
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
            ),
          ],
        ),
      ),
    );
  }
}
