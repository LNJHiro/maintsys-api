class HistoricoManutencao {
  final int? id;
  final int maquinaId;
  final int tecnicoId;
  final int? ordemId;
  final String tipo;
  final String? descricao;
  final String? solucao;
  final String? pecasUtilizadas;
  final double? tempoParadaHoras;
  final double? custo;
  final String? dataInicio;
  final String? dataFim;
  final String? observacoes;
  final String? maquinaModelo;
  final String? tecnicoNome;
  final String? ordemNumero;

  const HistoricoManutencao({
    this.id,
    required this.maquinaId,
    required this.tecnicoId,
    this.ordemId,
    required this.tipo,
    this.descricao,
    this.solucao,
    this.pecasUtilizadas,
    this.tempoParadaHoras,
    this.custo,
    this.dataInicio,
    this.dataFim,
    this.observacoes,
    this.maquinaModelo,
    this.tecnicoNome,
    this.ordemNumero,
  });

  static const List<String> tipoOptions = ['preventiva', 'corretiva'];

  String get tipoLabel => switch (tipo) {
    'preventiva' => 'Preventiva',
    'corretiva' => 'Corretiva',
    _ => 'Desconhecido',
  };

  Map<String, dynamic> toMap() {
    final map = <String, dynamic>{
      'maquina_id': maquinaId,
      'tecnico_id': tecnicoId,
      'ordem_id': ordemId,
      'tipo': tipo,
      'descricao': descricao,
      'solucao': solucao,
      'pecas_utilizadas': pecasUtilizadas,
      'tempo_parada_horas': tempoParadaHoras,
      'custo': custo,
      'data_inicio': dataInicio,
      'data_fim': dataFim,
      'observacoes': observacoes,
    };
    if (id != null) map['id'] = id;
    return map;
  }

  factory HistoricoManutencao.fromMap(Map<String, dynamic> map) => HistoricoManutencao(
    id: map['id'] as int?,
    maquinaId: map['maquina_id'] as int,
    tecnicoId: map['tecnico_id'] as int,
    ordemId: map['ordem_id'] as int?,
    tipo: map['tipo'] as String,
    descricao: map['descricao'] as String?,
    solucao: map['solucao'] as String?,
    pecasUtilizadas: map['pecas_utilizadas'] as String?,
    tempoParadaHoras: (map['tempo_parada_horas'] as num?)?.toDouble(),
    custo: (map['custo'] as num?)?.toDouble(),
    dataInicio: map['data_inicio'] as String?,
    dataFim: map['data_fim'] as String?,
    observacoes: map['observacoes'] as String?,
    maquinaModelo: map['maquina_modelo'] as String?,
    tecnicoNome: map['tecnico_nome'] as String?,
    ordemNumero: map['ordem_numero'] as String?,
  );
}
