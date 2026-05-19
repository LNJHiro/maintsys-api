class User {
  final int? id;
  final String name;
  final String email;
  final String password;
  final String role;

  const User({
    this.id,
    required this.name,
    required this.email,
    required this.password,
    required this.role,
  });

  bool get isMaster => role == 'admin_master';
  bool get isAdmin => role == 'admin';
  bool get canManageUsers => role == 'admin_master' || role == 'admin';

  String get roleLabel => switch (role) {
        'admin_master' => 'Admin Master',
        'admin' => 'Administrador',
        'usuario' => 'Usuário',
        _ => 'Usuário',
      };

  Map<String, dynamic> toMap() => {
        if (id != null) 'id': id,
        'name': name,
        'email': email,
        'password': password,
        'role': role,
      };

  static User fromMap(Map<String, dynamic> m) => User(
        id: m['id'] as int?,
        name: m['name'] as String,
        email: m['email'] as String,
        password: (m['password'] as String?) ?? '',
        role: m['role'] as String,
      );

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? password,
    String? role,
  }) =>
      User(
        id: id ?? this.id,
        name: name ?? this.name,
        email: email ?? this.email,
        password: password ?? this.password,
        role: role ?? this.role,
      );
}
