import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'en.dart';
import 'gu.dart';

class AppLocalizations {
  final Locale locale;

  AppLocalizations(this.locale);

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate = _AppLocalizationsDelegate();

  static final Map<String, Map<String, String>> _localizedValues = {
    'en': enTranslations,
    'gu': guTranslations,
  };

  String translate(String key, [Map<String, String>? args]) {
    final langCode = locale.languageCode;
    String value;
    if (_localizedValues.containsKey(langCode) && _localizedValues[langCode]!.containsKey(key)) {
      value = _localizedValues[langCode]![key]!;
    } else {
      // Fallback to English, then to the key itself.
      value = _localizedValues['en']?[key] ?? key;
    }
    if (args != null) {
      args.forEach((name, replacement) {
        value = value.replaceAll('{$name}', replacement);
      });
    }
    return value;
  }

  bool get isGujarati => locale.languageCode == 'gu';
}

class _AppLocalizationsDelegate extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => ['en', 'gu'].contains(locale.languageCode);

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(AppLocalizations(locale));
  }

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

extension LocalizationExtension on BuildContext {
  /// `context.tr('key')` or `context.tr('items_count', {'count': '3'})`
  /// - placeholders use `{name}` syntax.
  String tr(String key, [Map<String, String>? args]) {
    return AppLocalizations.of(this)?.translate(key, args) ?? key;
  }

  /// Current UI language code (`en` / `gu`).
  String get langCode => Localizations.localeOf(this).languageCode;
}
