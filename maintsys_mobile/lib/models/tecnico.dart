class Tecnico {
  final int? id;
  final String nome;
  final String matricula;
  final String email;
  final String especialidade;
  final String? telefone;
  final bool ativo;

  const Tecnico({
    this.id,
    required this.nome,
    required this.matricula,
    required this.email,
    required this.especialidade,
    this.telefone,
    this.ativo = true,
  });

  Map<String, dynamic> toMap() {
    final map = <String, dynamic>{
      'nome': nome,
      'matricula': matricula,
      'email': email,
      'especialidade': especialidade,
      'telefone': telefone,
      'ativo': ativo ? 1 : 0,
    };
    if (id != null) map['id'] = id;
    return map;
  }

  factory Tecnico.fromMap(Map<String, dynamic> map) => Tecnico(
    id: map['id'] as int?,
    nome: map['nome'] as String,
    matricula: map['matricula'] as String,
    email: map['email'] as String,
    especialidade: map['especialidade'] as String,
    telefone: map['telefone'] as String?,
    ativo: (map['ativo'] as int? ?? 1) == 1,
  );

  Tecnico copyWith({
    int? id,
    String? nome,
    String? matricula,
    String? email,
    String? especialidade,
    String? telefone,
    bool? ativo,
  }) =>
      Tecnico(
        id: id ?? this.id,
        nome: nome ?? this.nome,
        matricula: matricula ?? this.matricula,
        email: email ?? this.email,
        especialidade: especialidade ?? this.especialidade,
        telefone: telefone ?? this.telefone,
        ativo: ativo ?? this.ativo,
      );
}
