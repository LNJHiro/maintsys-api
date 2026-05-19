class Maquina {
  final int? id;
  final String numeroSerie;
  final String modelo;
  final String fabricante;
  final String localizacao;
  final String dataCadastro;
  final String status;
  final String? descricao;

  const Maquina({
    this.id,
    required this.numeroSerie,
    required this.modelo,
    required this.fabricante,
    required this.localizacao,
    required this.dataCadastro,
    this.status = 'operacional',
    this.descricao,
  });

  static const List<String> statusOptions = [
    'operacional',
    'em_manutencao',
    'parada_critica',
    'inativa',
  ];

  String get statusLabel => switch (status) {
    'operacional' => 'Operacional',
    'em_manutencao' => 'Em Manutenção',
    'parada_critica' => 'Parada Crítica',
    'inativa' => 'Inativa',
    _ => 'Desconhecido',
  };

  Map<String, dynamic> toMap() {
    final map = <String, dynamic>{
      'numero_serie': numeroSerie,
      'modelo': modelo,
      'fabricante': fabricante,
      'localizacao': localizacao,
      'data_cadastro': dataCadastro,
      'status': status,
      'descricao': descricao,
    };
    if (id != null) map['id'] = id;
    return map;
  }

  factory Maquina.fromMap(Map<String, dynamic> map) => Maquina(
    id: map['id'] as int?,
    numeroSerie: map['numero_serie'] as String,
    modelo: map['modelo'] as String,
    fabricante: map['fabricante'] as String,
    localizacao: map['localizacao'] as String,
    dataCadastro: map['data_cadastro'] as String,
    status: map['status'] as String? ?? 'operacional',
    descricao: map['descricao'] as String?,
  );

  Maquina copyWith({
    int? id,
    String? numeroSerie,
    String? modelo,
    String? fabricante,
    String? localizacao,
    String? dataCadastro,
    String? status,
    String? descricao,
  }) =>
      Maquina(
        id: id ?? this.id,
        numeroSerie: numeroSerie ?? this.numeroSerie,
        modelo: modelo ?? this.modelo,
        fabricante: fabricante ?? this.fabricante,
        localizacao: localizacao ?? this.localizacao,
        dataCadastro: dataCadastro ?? this.dataCadastro,
        status: status ?? this.status,
        descricao: descricao ?? this.descricao,
      );
}
