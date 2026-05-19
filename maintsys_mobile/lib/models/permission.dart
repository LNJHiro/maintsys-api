class Permission {
  final int? id;
  final String name;
  final String descricao;
  final String modulo;

  const Permission({
    this.id,
    required this.name,
    required this.descricao,
    required this.modulo,
  });

  String get moduloLabel => switch (modulo) {
        'maquinas' => 'Máquinas',
        'tecnicos' => 'Técnicos',
        'ordens' => 'Ordens de Serviço',
        'historico' => 'Histórico',
        'dashboard' => 'Dashboard',
        _ => modulo,
      };

  Map<String, dynamic> toMap() => {
        if (id != null) 'id': id,
        'name': name,
        'descricao': descricao,
        'modulo': modulo,
      };

  static Permission fromMap(Map<String, dynamic> m) => Permission(
        id: m['id'] as int?,
        name: m['name'] as String,
        descricao: m['descricao'] as String,
        modulo: m['modulo'] as String,
      );
}
