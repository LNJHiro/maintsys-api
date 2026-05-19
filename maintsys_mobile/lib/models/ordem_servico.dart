class OrdemServico {
  final int? id;
  final String numero;
  final String tipo;
  final String status;
  final String prioridade;
  final String? descricao;
  final String? solucao;
  final int maquinaId;
  final int tecnicoId;
  final String dataAbertura;
  final String? dataPrevista;
  final String? dataConclusao;
  final String? maquinaModelo;
  final String? tecnicoNome;

  const OrdemServico({
    this.id,
    required this.numero,
    required this.tipo,
    this.status = 'aberta',
    this.prioridade = 'media',
    this.descricao,
    this.solucao,
    required this.maquinaId,
    required this.tecnicoId,
    required this.dataAbertura,
    this.dataPrevista,
    this.dataConclusao,
    this.maquinaModelo,
    this.tecnicoNome,
  });

  static const List<String> tipoOptions = ['preventiva', 'corretiva'];
  static const List<String> statusOptions = ['aberta', 'em_andamento', 'concluida', 'cancelada'];
  static const List<String> prioridadeOptions = ['baixa', 'media', 'alta', 'critica'];

  String get tipoLabel => switch (tipo) {
    'preventiva' => 'Preventiva',
    'corretiva' => 'Corretiva',
    _ => 'Desconhecido',
  };

  String get statusLabel => switch (status) {
    'aberta' => 'Aberta',
    'em_andamento' => 'Em Andamento',
    'concluida' => 'Concluída',
    'cancelada' => 'Cancelada',
    _ => 'Desconhecido',
  };

  String get prioridadeLabel => switch (prioridade) {
    'baixa' => 'Baixa',
    'media' => 'Média',
    'alta' => 'Alta',
    'critica' => 'Crítica',
    _ => 'Normal',
  };

  bool get isAberta => status == 'aberta';
  bool get isEmAndamento => status == 'em_andamento';
  bool get isConcluida => status == 'concluida';
  bool get isCancelada => status == 'cancelada';

  Map<String, dynamic> toMap() {
    final map = <String, dynamic>{
      'numero': numero,
      'tipo': tipo,
      'status': status,
      'prioridade': prioridade,
      'descricao': descricao,
      'solucao': solucao,
      'maquina_id': maquinaId,
      'tecnico_id': tecnicoId,
      'data_abertura': dataAbertura,
      'data_prevista': dataPrevista,
      'data_conclusao': dataConclusao,
    };
    if (id != null) map['id'] = id;
    return map;
  }

  factory OrdemServico.fromMap(Map<String, dynamic> map) => OrdemServico(
    id: map['id'] as int?,
    numero: map['numero'] as String,
    tipo: map['tipo'] as String,
    status: map['status'] as String? ?? 'aberta',
    prioridade: map['prioridade'] as String? ?? 'media',
    descricao: map['descricao'] as String?,
    solucao: map['solucao'] as String?,
    maquinaId: map['maquina_id'] as int,
    tecnicoId: map['tecnico_id'] as int,
    dataAbertura: map['data_abertura'] as String,
    dataPrevista: map['data_prevista'] as String?,
    dataConclusao: map['data_conclusao'] as String?,
    maquinaModelo: map['maquina_modelo'] as String?,
    tecnicoNome: map['tecnico_nome'] as String?,
  );

  OrdemServico copyWith({
    int? id,
    String? numero,
    String? tipo,
    String? status,
    String? prioridade,
    String? descricao,
    String? solucao,
    int? maquinaId,
    int? tecnicoId,
    String? dataAbertura,
    String? dataPrevista,
    String? dataConclusao,
    String? maquinaModelo,
    String? tecnicoNome,
  }) =>
      OrdemServico(
        id: id ?? this.id,
        numero: numero ?? this.numero,
        tipo: tipo ?? this.tipo,
        status: status ?? this.status,
        prioridade: prioridade ?? this.prioridade,
        descricao: descricao ?? this.descricao,
        solucao: solucao ?? this.solucao,
        maquinaId: maquinaId ?? this.maquinaId,
        tecnicoId: tecnicoId ?? this.tecnicoId,
        dataAbertura: dataAbertura ?? this.dataAbertura,
        dataPrevista: dataPrevista ?? this.dataPrevista,
        dataConclusao: dataConclusao ?? this.dataConclusao,
        maquinaModelo: maquinaModelo ?? this.maquinaModelo,
        tecnicoNome: tecnicoNome ?? this.tecnicoNome,
      );
}
