import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../models/remote_theme.dart';
import '../services/api_service.dart';

/// Light / dark / system selection plus the admin-managed [RemoteTheme]
/// (colours, style, radius, font, effects). The last theme is cached so the app
/// starts with the right look even before the network answers.
class ThemeProvider extends ChangeNotifier {
  static const String _prefKey = 'theme_mode';
  static const String _remoteKey = 'remote_theme_v1';
  static const String _userChoiceKey = 'theme_mode_user_set';

  ThemeMode _themeMode = ThemeMode.system;
  RemoteTheme _remote = RemoteTheme.defaults;
  bool _userChose = false;

  ThemeMode get themeMode => _themeMode;
  RemoteTheme get remote => _remote;

  ThemeProvider() {
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    _userChose = prefs.getBool(_userChoiceKey) ?? false;
    _themeMode = _fromString(prefs.getString(_prefKey));
    final cached = prefs.getString(_remoteKey);
    if (cached != null) {
      try {
        _remote = RemoteTheme.fromJson(Map<String, dynamic>.from(jsonDecode(cached)));
      } catch (_) {}
    }
    notifyListeners();
    refreshRemote();
  }

  /// Fetches `/api/theme`; silently keeps the cached look when offline.
  Future<void> refreshRemote() async {
    try {
      final res = await ApiService.get(ApiConstants.theme);
      final data = ApiService.dataOf(res);
      if (data is! Map) return;
      final next = RemoteTheme.fromJson(Map<String, dynamic>.from(data));
      final changed = next.version != _remote.version;
      _remote = next;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_remoteKey, jsonEncode(next.toJson()));
      if (!_userChose) {
        final adminMode = _fromString(next.themeMode);
        if (adminMode != _themeMode) {
          _themeMode = adminMode;
          notifyListeners();
          return;
        }
      }
      if (changed) notifyListeners();
    } catch (_) {/* offline: keep cache */}
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    if (mode == _themeMode) return;
    _themeMode = mode;
    _userChose = true;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_prefKey, mode.name);
    await prefs.setBool(_userChoiceKey, true);
  }

  static ThemeMode _fromString(String? value) {
    switch (value) {
      case 'light':
        return ThemeMode.light;
      case 'dark':
        return ThemeMode.dark;
      default:
        return ThemeMode.system;
    }
  }
}
