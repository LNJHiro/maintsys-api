import '../models/user.dart';
import 'database_service.dart';

class AuthService {
  static User? _currentUser;
  static Set<String> _permissions = const <String>{};

  static User? get currentUser => _currentUser;
  static bool get isLoggedIn => _currentUser != null;
  static bool get isMaster => _currentUser?.isMaster ?? false;
  static bool get isAdmin => _currentUser?.canManageUsers ?? false;

  static Future<bool> login(String email, String password) async {
    final user = await DatabaseService.authenticate(email, password);
    if (user == null) return false;
    _currentUser = user;
    await reloadPermissions();
    return true;
  }

  static Future<void> reloadPermissions() async {
    if (_currentUser == null) {
      _permissions = const <String>{};
      return;
    }
    _permissions = await DatabaseService.getEffectivePermissions(_currentUser!);
  }

  static void logout() {
    _currentUser = null;
    _permissions = const <String>{};
  }

  static bool hasPermission(String name) {
    if (isMaster) return true;
    return _permissions.contains(name);
  }

  static bool canAny(Iterable<String> names) {
    if (isMaster) return true;
    return names.any(_permissions.contains);
  }

  static void updateCurrent(User user) {
    _currentUser = user;
  }
}
