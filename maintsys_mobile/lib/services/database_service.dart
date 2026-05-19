import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'package:intl/intl.dart';
import '../models/maquina.dart';
import '../models/tecnico.dart';
import '../models/ordem_servico.dart';
import '../models/historico_manutencao.dart';
import '../models/user.dart';
import '../models/permission.dart';

class DatabaseService {
  static Database? _db;

  static Future<Database> get db async {
    _db ??= await _init();
    return _db!;
  }

  static Future<Database> _init() async {
    final path = join(await getDatabasesPath(), 'maintsys.db');
    return openDatabase(
      path,
      version: 2,
      onCreate: _create,
      onUpgrade: _upgrade,
    );
  }

  static Future<void> _create(Database db, int version) async {
    await db.execute('''
      CREATE TABLE maquinas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_serie TEXT NOT NULL,
        modelo TEXT NOT NULL,
        fabricante TEXT NOT NULL,
        localizacao TEXT NOT NULL,
        data_cadastro TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'operacional',
        descricao TEXT
      )
    ''');

    await db.execute('''
      CREATE TABLE tecnicos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        matricula TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL,
        especialidade TEXT NOT NULL,
        telefone TEXT,
        ativo INTEGER NOT NULL DEFAULT 1
      )
    ''');

    await db.execute('''
      CREATE TABLE ordens_servico (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero TEXT NOT NULL UNIQUE,
        tipo TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'aberta',
        prioridade TEXT NOT NULL DEFAULT 'media',
        descricao TEXT,
        solucao TEXT,
        maquina_id INTEGER NOT NULL,
        tecnico_id INTEGER NOT NULL,
        data_abertura TEXT NOT NULL,
        data_prevista TEXT,
        data_conclusao TEXT,
        FOREIGN KEY (maquina_id) REFERENCES maquinas(id),
        FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id)
      )
    ''');

    await db.execute('''
      CREATE TABLE historico_manutencoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        maquina_id INTEGER NOT NULL,
        tecnico_id INTEGER NOT NULL,
        ordem_id INTEGER,
        tipo TEXT NOT NULL,
        descricao TEXT,
        solucao TEXT,
        pecas_utilizadas TEXT,
        tempo_parada_horas REAL,
        custo REAL,
        data_inicio TEXT,
        data_fim TEXT,
        observacoes TEXT,
        FOREIGN KEY (maquina_id) REFERENCES maquinas(id),
        FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id),
        FOREIGN KEY (ordem_id) REFERENCES ordens_servico(id)
      )
    ''');

    await _createAuthTables(db);
    await _seed(db);
    await _seedUsersAndPermissions(db);
  }

  static Future<void> _upgrade(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      await _createAuthTables(db);
      await _seedUsersAndPermissions(db);
    }
  }

  static Future<void> _createAuthTables(Database db) async {
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'usuario'
      )
    ''');

    await db.execute('''
      CREATE TABLE permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        descricao TEXT NOT NULL,
        modulo TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE role_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role TEXT NOT NULL,
        permission_id INTEGER NOT NULL,
        UNIQUE(role, permission_id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
      )
    ''');

    await db.execute('''
      CREATE TABLE user_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        UNIQUE(user_id, permission_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
      )
    ''');
  }

  static Future<void> _seed(Database db) async {
    final hoje = DateFormat('yyyy-MM-dd').format(DateTime.now());

    final m1 = await db.insert('maquinas', {
      'numero_serie': 'SN-001-2024',
      'modelo': 'Torno CNC XK7136',
      'fabricante': 'Siemens',
      'localizacao': 'Galpão A - Setor 1',
      'data_cadastro': hoje,
      'status': 'operacional',
      'descricao': 'Torno de precisão para usinagem de peças metálicas',
    });

    final m2 = await db.insert('maquinas', {
      'numero_serie': 'SN-002-2024',
      'modelo': 'Fresadora VMC 850',
      'fabricante': 'Romi',
      'localizacao': 'Galpão A - Setor 2',
      'data_cadastro': hoje,
      'status': 'em_manutencao',
      'descricao': 'Centro de usinagem vertical',
    });

    final m3 = await db.insert('maquinas', {
      'numero_serie': 'SN-003-2024',
      'modelo': 'Compressor Atlas GA30',
      'fabricante': 'Atlas Copco',
      'localizacao': 'Sala de Compressores',
      'data_cadastro': hoje,
      'status': 'parada_critica',
      'descricao': 'Compressor de ar industrial',
    });

    final t1 = await db.insert('tecnicos', {
      'nome': 'Carlos Souza',
      'matricula': 'TEC-001',
      'email': 'carlos.souza@maintsys.com',
      'especialidade': 'Mecânica Industrial',
      'telefone': '(11) 99999-0001',
      'ativo': 1,
    });

    final t2 = await db.insert('tecnicos', {
      'nome': 'Ana Lima',
      'matricula': 'TEC-002',
      'email': 'ana.lima@maintsys.com',
      'especialidade': 'Eletricidade',
      'telefone': '(11) 99999-0002',
      'ativo': 1,
    });

    final prefixo = 'OS-${hoje.replaceAll('-', '')}-';

    final os1 = await db.insert('ordens_servico', {
      'numero': '${prefixo}0001',
      'tipo': 'corretiva',
      'status': 'em_andamento',
      'prioridade': 'alta',
      'descricao': 'Fresadora com vibração excessiva durante operação',
      'maquina_id': m2,
      'tecnico_id': t1,
      'data_abertura': DateTime.now().toIso8601String(),
    });

    await db.insert('ordens_servico', {
      'numero': '${prefixo}0002',
      'tipo': 'preventiva',
      'status': 'aberta',
      'prioridade': 'critica',
      'descricao': 'Manutenção preventiva emergencial do compressor',
      'maquina_id': m3,
      'tecnico_id': t2,
      'data_abertura': DateTime.now().toIso8601String(),
    });

    await db.insert('historico_manutencoes', {
      'maquina_id': m1,
      'tecnico_id': t1,
      'ordem_id': null,
      'tipo': 'preventiva',
      'descricao': 'Revisão geral do torno CNC',
      'solucao': 'Troca de óleo, limpeza e calibração realizadas',
      'pecas_utilizadas': 'Óleo lubrificante 5L, filtro de ar',
      'tempo_parada_horas': 4.0,
      'custo': 350.0,
      'data_inicio': DateTime.now().subtract(const Duration(days: 30)).toIso8601String(),
      'data_fim': DateTime.now().subtract(const Duration(days: 30)).add(const Duration(hours: 4)).toIso8601String(),
      'observacoes': 'Máquina em perfeito estado após manutenção',
    });

    debugPrint('Seed concluído. OS1 id: $os1');
  }

  static Future<void> _seedUsersAndPermissions(Database db) async {
    await db.insert('users', {
      'name': 'Admin Master SENAI',
      'email': 'master@senai.br',
      'password': 'master123',
      'role': 'admin_master',
    });
    await db.insert('users', {
      'name': 'Administrador SENAI',
      'email': 'admin@senai.br',
      'password': 'admin123',
      'role': 'admin',
    });
    await db.insert('users', {
      'name': 'Usuário SENAI',
      'email': 'usuario@senai.br',
      'password': 'usuario123',
      'role': 'usuario',
    });

    final permissions = <Map<String, String>>[
      {'name': 'maquinas.visualizar', 'descricao': 'Ver máquinas', 'modulo': 'maquinas'},
      {'name': 'maquinas.criar', 'descricao': 'Criar máquina', 'modulo': 'maquinas'},
      {'name': 'maquinas.editar', 'descricao': 'Editar máquina', 'modulo': 'maquinas'},
      {'name': 'maquinas.deletar', 'descricao': 'Deletar máquina', 'modulo': 'maquinas'},
      {'name': 'tecnicos.visualizar', 'descricao': 'Ver técnicos', 'modulo': 'tecnicos'},
      {'name': 'tecnicos.criar', 'descricao': 'Criar técnico', 'modulo': 'tecnicos'},
      {'name': 'tecnicos.editar', 'descricao': 'Editar técnico', 'modulo': 'tecnicos'},
      {'name': 'tecnicos.deletar', 'descricao': 'Deletar técnico', 'modulo': 'tecnicos'},
      {'name': 'ordens.visualizar', 'descricao': 'Ver ordens', 'modulo': 'ordens'},
      {'name': 'ordens.criar', 'descricao': 'Criar ordem', 'modulo': 'ordens'},
      {'name': 'ordens.editar', 'descricao': 'Editar ordem', 'modulo': 'ordens'},
      {'name': 'ordens.deletar', 'descricao': 'Deletar ordem', 'modulo': 'ordens'},
      {'name': 'historico.visualizar', 'descricao': 'Ver histórico', 'modulo': 'historico'},
      {'name': 'historico.criar', 'descricao': 'Registrar manutenção', 'modulo': 'historico'},
      {'name': 'historico.deletar', 'descricao': 'Deletar registro', 'modulo': 'historico'},
      {'name': 'dashboard.maquinas', 'descricao': 'Ver cards de máquinas', 'modulo': 'dashboard'},
      {'name': 'dashboard.tecnicos', 'descricao': 'Ver card de técnicos', 'modulo': 'dashboard'},
      {'name': 'dashboard.ordens', 'descricao': 'Ver cards e tabela de ordens', 'modulo': 'dashboard'},
      {'name': 'dashboard.alertas', 'descricao': 'Ver alertas de parada crítica', 'modulo': 'dashboard'},
      {'name': 'dashboard.historico', 'descricao': 'Ver últimas manutenções', 'modulo': 'dashboard'},
    ];

    final ids = <String, int>{};
    for (final p in permissions) {
      final id = await db.insert('permissions', p);
      ids[p['name']!] = id;
    }

    for (final pid in ids.values) {
      await db.insert('role_permissions', {'role': 'admin', 'permission_id': pid});
    }

    final usuarioPerms = ids.entries
        .where((e) =>
            e.key.endsWith('.visualizar') ||
            e.key == 'ordens.criar' ||
            e.key == 'ordens.editar' ||
            e.key == 'dashboard.maquinas' ||
            e.key == 'dashboard.ordens')
        .map((e) => e.value);
    for (final pid in usuarioPerms) {
      await db.insert('role_permissions', {'role': 'usuario', 'permission_id': pid});
    }
  }

  // ─── MÁQUINAS ───────────────────────────────────────────────────────────────

  static Future<List<Maquina>> getMaquinas() async {
    final d = await db;
    final rows = await d.query('maquinas', orderBy: 'modelo ASC');
    return rows.map(Maquina.fromMap).toList();
  }

  static Future<Maquina?> getMaquinaById(int id) async {
    final d = await db;
    final rows = await d.query('maquinas', where: 'id = ?', whereArgs: [id]);
    return rows.isEmpty ? null : Maquina.fromMap(rows.first);
  }

  static Future<int> insertMaquina(Maquina m) async {
    final d = await db;
    return d.insert('maquinas', m.toMap());
  }

  static Future<void> updateMaquina(Maquina m) async {
    final d = await db;
    await d.update('maquinas', m.toMap(), where: 'id = ?', whereArgs: [m.id]);
  }

  static Future<bool> canDeleteMaquina(int id) async {
    final d = await db;
    final count = Sqflite.firstIntValue(await d.rawQuery(
        'SELECT COUNT(*) FROM ordens_servico WHERE maquina_id = ?', [id])) ?? 0;
    return count == 0;
  }

  static Future<void> deleteMaquina(int id) async {
    final d = await db;
    await d.delete('maquinas', where: 'id = ?', whereArgs: [id]);
  }

  // ─── TÉCNICOS ───────────────────────────────────────────────────────────────

  static Future<List<Tecnico>> getTecnicos() async {
    final d = await db;
    final rows = await d.query('tecnicos', orderBy: 'nome ASC');
    return rows.map(Tecnico.fromMap).toList();
  }

  static Future<List<Tecnico>> getTecnicosAtivos() async {
    final d = await db;
    final rows = await d.query('tecnicos', where: 'ativo = 1', orderBy: 'nome ASC');
    return rows.map(Tecnico.fromMap).toList();
  }

  static Future<Tecnico?> getTecnicoById(int id) async {
    final d = await db;
    final rows = await d.query('tecnicos', where: 'id = ?', whereArgs: [id]);
    return rows.isEmpty ? null : Tecnico.fromMap(rows.first);
  }

  static Future<int> insertTecnico(Tecnico t) async {
    final d = await db;
    return d.insert('tecnicos', t.toMap());
  }

  static Future<void> updateTecnico(Tecnico t) async {
    final d = await db;
    await d.update('tecnicos', t.toMap(), where: 'id = ?', whereArgs: [t.id]);
  }

  static Future<bool> canDeleteTecnico(int id) async {
    final d = await db;
    final count = Sqflite.firstIntValue(await d.rawQuery(
        'SELECT COUNT(*) FROM ordens_servico WHERE tecnico_id = ?', [id])) ?? 0;
    return count == 0;
  }

  static Future<void> deleteTecnico(int id) async {
    final d = await db;
    await d.delete('tecnicos', where: 'id = ?', whereArgs: [id]);
  }

  // ─── ORDENS DE SERVIÇO ──────────────────────────────────────────────────────

  static Future<String> _gerarNumero() async {
    final d = await db;
    final prefix = 'OS-${DateFormat('yyyyMMdd').format(DateTime.now())}-';
    final rows = await d.rawQuery(
      "SELECT numero FROM ordens_servico WHERE numero LIKE ? ORDER BY numero DESC LIMIT 1",
      ['$prefix%'],
    );
    if (rows.isEmpty) return '${prefix}0001';
    final ultimo = rows.first['numero'] as String;
    final seq = int.parse(ultimo.substring(ultimo.length - 4)) + 1;
    return '$prefix${seq.toString().padLeft(4, '0')}';
  }

  static const _ordensJoin = '''
    SELECT o.*, m.modelo AS maquina_modelo, t.nome AS tecnico_nome
    FROM ordens_servico o
    LEFT JOIN maquinas m ON o.maquina_id = m.id
    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
  ''';

  static Future<List<OrdemServico>> getOrdens({String? statusFiltro}) async {
    final d = await db;
    String sql = '$_ordensJoin ORDER BY o.data_abertura DESC';
    List<Object?> args = [];
    if (statusFiltro != null) {
      sql = '$_ordensJoin WHERE o.status = ? ORDER BY o.data_abertura DESC';
      args = [statusFiltro];
    }
    final rows = await d.rawQuery(sql, args);
    return rows.map(OrdemServico.fromMap).toList();
  }

  static Future<OrdemServico?> getOrdemById(int id) async {
    final d = await db;
    final rows = await d.rawQuery('$_ordensJoin WHERE o.id = ?', [id]);
    return rows.isEmpty ? null : OrdemServico.fromMap(rows.first);
  }

  static Future<List<OrdemServico>> getOrdensByMaquina(int maquinaId) async {
    final d = await db;
    final rows = await d.rawQuery(
      '$_ordensJoin WHERE o.maquina_id = ? ORDER BY o.data_abertura DESC',
      [maquinaId],
    );
    return rows.map(OrdemServico.fromMap).toList();
  }

  static Future<int> insertOrdem(OrdemServico o) async {
    final d = await db;
    final numero = await _gerarNumero();
    final map = o.toMap();
    map['numero'] = numero;
    map['data_abertura'] = DateTime.now().toIso8601String();
    final id = await d.insert('ordens_servico', map);
    await d.update('maquinas', {'status': 'em_manutencao'},
        where: 'id = ?', whereArgs: [o.maquinaId]);
    return id;
  }

  static Future<void> updateOrdem(OrdemServico o) async {
    final d = await db;
    await d.update('ordens_servico', o.toMap(), where: 'id = ?', whereArgs: [o.id]);
  }

  static Future<void> iniciarOrdem(int id) async {
    final d = await db;
    await d.update('ordens_servico', {'status': 'em_andamento'},
        where: 'id = ?', whereArgs: [id]);
  }

  static Future<void> cancelarOrdem(int id) async {
    final d = await db;
    final o = await getOrdemById(id);
    if (o == null) return;
    await d.update('ordens_servico', {'status': 'cancelada'},
        where: 'id = ?', whereArgs: [id]);
    final abertas = Sqflite.firstIntValue(await d.rawQuery(
          "SELECT COUNT(*) FROM ordens_servico WHERE maquina_id = ? AND status NOT IN ('concluida','cancelada') AND id != ?",
          [o.maquinaId, id])) ??
        0;
    if (abertas == 0) {
      await d.update('maquinas', {'status': 'operacional'},
          where: 'id = ?', whereArgs: [o.maquinaId]);
    }
  }

  static Future<void> concluirOrdem(
    int ordemId,
    String solucao, {
    String? pecasUtilizadas,
    double? tempoParadaHoras,
    double? custo,
    String? observacoes,
  }) async {
    final d = await db;
    final o = await getOrdemById(ordemId);
    if (o == null) return;

    final now = DateTime.now().toIso8601String();

    await d.update(
      'ordens_servico',
      {'status': 'concluida', 'solucao': solucao, 'data_conclusao': now},
      where: 'id = ?',
      whereArgs: [ordemId],
    );

    await d.insert('historico_manutencoes', {
      'maquina_id': o.maquinaId,
      'tecnico_id': o.tecnicoId,
      'ordem_id': ordemId,
      'tipo': o.tipo,
      'descricao': o.descricao,
      'solucao': solucao,
      'pecas_utilizadas': pecasUtilizadas,
      'tempo_parada_horas': tempoParadaHoras,
      'custo': custo,
      'data_inicio': o.dataAbertura,
      'data_fim': now,
      'observacoes': observacoes,
    });

    final abertas = Sqflite.firstIntValue(await d.rawQuery(
          "SELECT COUNT(*) FROM ordens_servico WHERE maquina_id = ? AND status NOT IN ('concluida','cancelada') AND id != ?",
          [o.maquinaId, ordemId])) ??
        0;
    if (abertas == 0) {
      await d.update('maquinas', {'status': 'operacional'},
          where: 'id = ?', whereArgs: [o.maquinaId]);
    }
  }

  static Future<void> deleteOrdem(int id) async {
    final d = await db;
    await d.delete('ordens_servico', where: 'id = ?', whereArgs: [id]);
  }

  // ─── HISTÓRICO ──────────────────────────────────────────────────────────────

  static const _historicoJoin = '''
    SELECT h.*, m.modelo AS maquina_modelo, t.nome AS tecnico_nome,
           o.numero AS ordem_numero
    FROM historico_manutencoes h
    LEFT JOIN maquinas m ON h.maquina_id = m.id
    LEFT JOIN tecnicos t ON h.tecnico_id = t.id
    LEFT JOIN ordens_servico o ON h.ordem_id = o.id
  ''';

  static Future<List<HistoricoManutencao>> getHistorico() async {
    final d = await db;
    final rows =
        await d.rawQuery('$_historicoJoin ORDER BY h.data_inicio DESC');
    return rows.map(HistoricoManutencao.fromMap).toList();
  }

  static Future<HistoricoManutencao?> getHistoricoById(int id) async {
    final d = await db;
    final rows = await d.rawQuery('$_historicoJoin WHERE h.id = ?', [id]);
    return rows.isEmpty ? null : HistoricoManutencao.fromMap(rows.first);
  }

  static Future<List<HistoricoManutencao>> getHistoricoByMaquina(
      int maquinaId) async {
    final d = await db;
    final rows = await d.rawQuery(
      '$_historicoJoin WHERE h.maquina_id = ? ORDER BY h.data_inicio DESC',
      [maquinaId],
    );
    return rows.map(HistoricoManutencao.fromMap).toList();
  }

  static Future<int> insertHistorico(HistoricoManutencao h) async {
    final d = await db;
    return d.insert('historico_manutencoes', h.toMap());
  }

  static Future<void> deleteHistorico(int id) async {
    final d = await db;
    await d.delete('historico_manutencoes', where: 'id = ?', whereArgs: [id]);
  }

  // ─── DASHBOARD ──────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> getDashboardStats() async {
    final d = await db;

    final maqStats = await d.rawQuery(
        'SELECT status, COUNT(*) AS cnt FROM maquinas GROUP BY status');
    final ordStats = await d.rawQuery(
        'SELECT status, COUNT(*) AS cnt FROM ordens_servico GROUP BY status');

    final tecAtivos = Sqflite.firstIntValue(
            await d.rawQuery('SELECT COUNT(*) FROM tecnicos WHERE ativo = 1')) ??
        0;
    final totalHist = Sqflite.firstIntValue(
            await d.rawQuery('SELECT COUNT(*) FROM historico_manutencoes')) ??
        0;

    final maqMap = {for (final r in maqStats) r['status'] as String: r['cnt'] as int};
    final ordMap = {for (final r in ordStats) r['status'] as String: r['cnt'] as int};

    return {
      'maquinas': maqMap,
      'ordens': ordMap,
      'tecnicos_ativos': tecAtivos,
      'total_historico': totalHist,
      'paradas_criticas': maqMap['parada_critica'] ?? 0,
    };
  }

  // ─── USERS ──────────────────────────────────────────────────────────────────

  static Future<User?> authenticate(String email, String password) async {
    final d = await db;
    final rows = await d.query(
      'users',
      where: 'email = ? AND password = ?',
      whereArgs: [email.trim(), password],
      limit: 1,
    );
    return rows.isEmpty ? null : User.fromMap(rows.first);
  }

  static Future<List<User>> getUsers({bool excludeMaster = true}) async {
    final d = await db;
    final rows = await d.query(
      'users',
      where: excludeMaster ? "role != 'admin_master'" : null,
      orderBy: 'name ASC',
    );
    return rows.map(User.fromMap).toList();
  }

  static Future<List<User>> getAllUsers() => getUsers(excludeMaster: false);

  static Future<User?> getUserById(int id) async {
    final d = await db;
    final rows = await d.query('users', where: 'id = ?', whereArgs: [id]);
    return rows.isEmpty ? null : User.fromMap(rows.first);
  }

  static Future<bool> emailExists(String email, {int? exceptId}) async {
    final d = await db;
    final rows = exceptId == null
        ? await d.query('users', where: 'email = ?', whereArgs: [email])
        : await d.query('users',
            where: 'email = ? AND id != ?', whereArgs: [email, exceptId]);
    return rows.isNotEmpty;
  }

  static Future<int> insertUser(User u) async {
    final d = await db;
    return d.insert('users', u.toMap());
  }

  static Future<void> updateUser(User u, {String? newPassword}) async {
    final d = await db;
    final map = {
      'name': u.name,
      'email': u.email,
      'role': u.role,
    };
    if (newPassword != null && newPassword.isNotEmpty) {
      map['password'] = newPassword;
    }
    await d.update('users', map, where: 'id = ?', whereArgs: [u.id]);
  }

  static Future<void> updatePassword(int userId, String newPassword) async {
    final d = await db;
    await d.update('users', {'password': newPassword},
        where: 'id = ?', whereArgs: [userId]);
  }

  static Future<void> deleteUser(int id) async {
    final d = await db;
    await d.delete('users', where: 'id = ?', whereArgs: [id]);
  }

  // ─── PERMISSIONS ────────────────────────────────────────────────────────────

  static Future<List<Permission>> getPermissions() async {
    final d = await db;
    final rows = await d.query('permissions', orderBy: 'modulo, name');
    return rows.map(Permission.fromMap).toList();
  }

  static Future<Map<String, List<Permission>>> getPermissionsByModulo() async {
    final perms = await getPermissions();
    final out = <String, List<Permission>>{};
    for (final p in perms) {
      out.putIfAbsent(p.modulo, () => []).add(p);
    }
    return out;
  }

  static Future<List<int>> getRolePermissionIds(String role) async {
    final d = await db;
    final rows = await d.query('role_permissions',
        columns: ['permission_id'], where: 'role = ?', whereArgs: [role]);
    return rows.map((r) => r['permission_id'] as int).toList();
  }

  static Future<List<int>> getUserPermissionIds(int userId) async {
    final d = await db;
    final rows = await d.query('user_permissions',
        columns: ['permission_id'], where: 'user_id = ?', whereArgs: [userId]);
    return rows.map((r) => r['permission_id'] as int).toList();
  }

  static Future<bool> userHasIndividualPermissions(int userId) async {
    final d = await db;
    final c = Sqflite.firstIntValue(await d.rawQuery(
            'SELECT COUNT(*) FROM user_permissions WHERE user_id = ?', [userId])) ??
        0;
    return c > 0;
  }

  static Future<Set<String>> getEffectivePermissions(User user) async {
    if (user.isMaster) {
      final d = await db;
      final rows = await d.query('permissions', columns: ['name']);
      return rows.map((r) => r['name'] as String).toSet();
    }
    final d = await db;
    final hasIndividual = await userHasIndividualPermissions(user.id!);
    final List<Map<String, Object?>> rows;
    if (hasIndividual) {
      rows = await d.rawQuery('''
        SELECT p.name FROM permissions p
        INNER JOIN user_permissions up ON up.permission_id = p.id
        WHERE up.user_id = ?
      ''', [user.id]);
    } else {
      rows = await d.rawQuery('''
        SELECT p.name FROM permissions p
        INNER JOIN role_permissions rp ON rp.permission_id = p.id
        WHERE rp.role = ?
      ''', [user.role]);
    }
    return rows.map((r) => r['name'] as String).toSet();
  }

  static Future<void> setRolePermissions(String role, List<int> permissionIds) async {
    final d = await db;
    await d.transaction((txn) async {
      await txn.delete('role_permissions', where: 'role = ?', whereArgs: [role]);
      for (final pid in permissionIds) {
        await txn.insert('role_permissions',
            {'role': role, 'permission_id': pid});
      }
    });
  }

  static Future<void> setUserPermissions(int userId, List<int> permissionIds) async {
    final d = await db;
    await d.transaction((txn) async {
      await txn.delete('user_permissions',
          where: 'user_id = ?', whereArgs: [userId]);
      for (final pid in permissionIds) {
        await txn.insert('user_permissions',
            {'user_id': userId, 'permission_id': pid});
      }
    });
  }

  static Future<void> clearUserPermissions(int userId) async {
    final d = await db;
    await d.delete('user_permissions',
        where: 'user_id = ?', whereArgs: [userId]);
  }
}
