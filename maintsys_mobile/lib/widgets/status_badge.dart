import 'package:flutter/material.dart';

class StatusBadge extends StatelessWidget {
  final String label;
  final Color color;
  final double fontSize;

  const StatusBadge({
    super.key,
    required this.label,
    required this.color,
    this.fontSize = 11,
  });

  factory StatusBadge.maquina(String status) {
    final (label, color) = switch (status) {
      'operacional' => ('Operacional', const Color(0xFF2E7D32)),
      'em_manutencao' => ('Em Manutenção', const Color(0xFFE65100)),
      'parada_critica' => ('Parada Crítica', const Color(0xFFC62828)),
      'inativa' => ('Inativa', const Color(0xFF546E7A)),
      _ => ('Desconhecido', Colors.grey),
    };
    return StatusBadge(label: label, color: color);
  }

  factory StatusBadge.ordem(String status) {
    final (label, color) = switch (status) {
      'aberta' => ('Aberta', const Color(0xFF1565C0)),
      'em_andamento' => ('Em Andamento', const Color(0xFFE65100)),
      'concluida' => ('Concluída', const Color(0xFF2E7D32)),
      'cancelada' => ('Cancelada', const Color(0xFF546E7A)),
      _ => ('Desconhecido', Colors.grey),
    };
    return StatusBadge(label: label, color: color);
  }

  factory StatusBadge.prioridade(String prioridade) {
    final (label, color) = switch (prioridade) {
      'baixa' => ('Baixa', const Color(0xFF2E7D32)),
      'media' => ('Média', const Color(0xFF1565C0)),
      'alta' => ('Alta', const Color(0xFFE65100)),
      'critica' => ('Crítica', const Color(0xFFC62828)),
      _ => ('Normal', Colors.grey),
    };
    return StatusBadge(label: label, color: color);
  }

  factory StatusBadge.tipo(String tipo) {
    final (label, color) = switch (tipo) {
      'preventiva' => ('Preventiva', const Color(0xFF1565C0)),
      'corretiva' => ('Corretiva', const Color(0xFFE65100)),
      _ => ('Desconhecido', Colors.grey),
    };
    return StatusBadge(label: label, color: color);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.4)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: fontSize,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
