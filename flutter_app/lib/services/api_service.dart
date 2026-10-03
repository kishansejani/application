import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:ui' show Locale;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../l10n/app_localizations.dart';

/// Error thrown by [ApiService]. `toString()` returns the human readable
/// message so existing `e.toString()` call-sites keep working.
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final bool isNetworkError;

  /// Decoded JSON error body (when the server returned one).
  final Map<String, dynamic>? body;

  ApiException(this.message, {this.statusCode, this.isNetworkError = false, this.body});

  bool get isUnauthorized => statusCode == 401;

  bool _flag(String key) {
    final b = body;
    if (b == null) return false;
    final v = b[key] ?? (b['data'] is Map ? (b['data'] as Map)[key] : null);
    return v == true || v == 1 || v == '1' || v == 'true';
  }

  /// `POST /auth/otp/send` for a phone number that has no account yet.
  bool get needsRegistration => _flag('needs_registration');

  /// `POST /auth/register` for a phone number that already has an account.
  bool get alreadyRegistered => _flag('already_registered');

  /// OTP error reason from the API (`invalid`, `expired`, `too_many_attempts`,
  /// `not_found`, `cooldown`, `hourly_limit`, `delivery_failed`).
  String? get reason {
    final v = body?['reason'];
    return v?.toString();
  }

  /// Seconds until the action may be retried (OTP cooldown), when provided.
  int? get retryAfter {
    final v = body?['retry_after'];
    if (v is int) return v;
    return int.tryParse(v?.toString() ?? '');
  }

  @override
  String toString() => message;
}

class ApiService {
  ApiService._();

  static const Duration _timeout = Duration(seconds: 20);

  /// In-memory UI language, set by `LocaleProvider` the moment the user switches
  /// language, so the very next request already uses it (no SharedPreferences race).
  static String? _language;

  static void setLanguage(String languageCode) {
    _language = languageCode == 'en' ? 'en' : 'gu';
  }

  /// Current UI language (`gu` / `en`) - used for headers and local error texts.
  static String get languageCode => _language ?? 'gu';

  static String _t(String key) => AppLocalizations(Locale(languageCode)).translate(key);

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<String> getLocale() async {
    if (_language != null) return _language!;
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString('locale') ?? 'gu';
    return saved == 'en' ? 'en' : 'gu';
  }

  static Future<Map<String, String>> _getHeaders({bool requireAuth = false}) async {
    final locale = await getLocale();
    // The Laravel API reads Accept-Language / X-Locale to localise names & messages.
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Accept-Language': locale,
      'X-Locale': locale,
    };

    if (requireAuth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  /// Laravel responses are `{status, message, data}`; returns `data` when present.
  static dynamic dataOf(dynamic res) {
    if (res is Map && res.containsKey('data')) return res['data'];
    return res;
  }

  static Future<dynamic> get(String url, {bool requireAuth = false}) async {
    final headers = await _getHeaders(requireAuth: requireAuth);
    return _send(() => http.get(Uri.parse(url), headers: headers));
  }

  static Future<dynamic> post(String url, Map<String, dynamic> body, {bool requireAuth = false}) async {
    final headers = await _getHeaders(requireAuth: requireAuth);
    return _send(() => http.post(Uri.parse(url), headers: headers, body: jsonEncode(body)));
  }

  static Future<dynamic> put(String url, Map<String, dynamic> body, {bool requireAuth = false}) async {
    final headers = await _getHeaders(requireAuth: requireAuth);
    return _send(() => http.put(Uri.parse(url), headers: headers, body: jsonEncode(body)));
  }

  static Future<dynamic> patch(String url, Map<String, dynamic> body, {bool requireAuth = false}) async {
    final headers = await _getHeaders(requireAuth: requireAuth);
    return _send(() => http.patch(Uri.parse(url), headers: headers, body: jsonEncode(body)));
  }

  static Future<dynamic> delete(String url, {bool requireAuth = false}) async {
    final headers = await _getHeaders(requireAuth: requireAuth);
    return _send(() => http.delete(Uri.parse(url), headers: headers));
  }

  static Future<dynamic> _send(Future<http.Response> Function() request) async {
    final http.Response response;
    try {
      response = await request().timeout(_timeout);
    } on TimeoutException {
      throw ApiException(_t('err_timeout'), isNetworkError: true);
    } on IOException {
      throw ApiException(_t('err_offline'), isNetworkError: true);
    } on http.ClientException {
      throw ApiException(_t('err_unreachable'), isNetworkError: true);
    }
    return _processResponse(response);
  }

  static dynamic _processResponse(http.Response response) {
    dynamic body;
    try {
      body = response.bodyBytes.isEmpty ? <String, dynamic>{} : jsonDecode(utf8.decode(response.bodyBytes));
    } catch (_) {
      body = null;
    }

    final code = response.statusCode;
    if (code >= 200 && code < 300) {
      if (body == null) {
        throw ApiException(_t('err_invalid_response'), statusCode: code);
      }
      return body;
    }

    String msg = _t('err_generic').replaceAll('{code}', '$code');
    if (body is Map) {
      final errors = body['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) {
          msg = first.first.toString();
        } else if (first != null) {
          msg = first.toString();
        }
      } else if (body['message'] != null && body['message'].toString().isNotEmpty) {
        msg = body['message'].toString();
      }
    }
    if (code == 401) {
      msg = _t('err_session_expired');
    } else if (code == 404 && msg.contains('No query results')) {
      msg = _t('err_not_found');
    }
    throw ApiException(
      msg,
      statusCode: code,
      body: body is Map ? Map<String, dynamic>.from(body) : null,
    );
  }
}
