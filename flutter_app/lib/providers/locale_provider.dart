import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class LocaleProvider extends ChangeNotifier {
  static const List<Locale> supported = [Locale('en'), Locale('gu')];

  Locale _locale = const Locale('gu');
  bool _changedByUser = false;

  Locale get locale => _locale;
  bool get isGujarati => _locale.languageCode == 'gu';

  LocaleProvider() {
    ApiService.setLanguage(_locale.languageCode);
    _loadSavedLocale();
  }

  Future<void> _loadSavedLocale() async {
    final prefs = await SharedPreferences.getInstance();
    // The user may already have switched language while prefs were loading.
    if (_changedByUser) return;
    final langCode = prefs.getString('locale') == 'en' ? 'en' : 'gu';
    _locale = Locale(langCode);
    ApiService.setLanguage(langCode);
    notifyListeners();
  }

  /// Switches the whole app immediately: `MaterialApp.locale` rebuilds every
  /// screen and [ApiService] sends the new `Accept-Language` on the next call.
  Future<void> setLocale(Locale newLocale) async {
    if (!['en', 'gu'].contains(newLocale.languageCode)) return;
    if (newLocale.languageCode == _locale.languageCode) return;
    _changedByUser = true;
    _locale = Locale(newLocale.languageCode);
    ApiService.setLanguage(newLocale.languageCode);
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('locale', newLocale.languageCode);
  }

  Future<void> toggleLocale() {
    return setLocale(Locale(_locale.languageCode == 'gu' ? 'en' : 'gu'));
  }
}
