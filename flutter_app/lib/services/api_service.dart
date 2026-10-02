import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

class ApiService {
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<String> getLocale() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('locale') ?? 'gu';
  }

  static Future<Map<String, String>> _getHeaders({bool requireAuth = false}) async {
    final locale = await getLocale();
    Map<String, String> headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Accept-Language': locale,
    };

    if (requireAuth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  // Generic GET
  static Future<dynamic> get(String url, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      final response = await http.get(Uri.parse(url), headers: headers);
      return _processResponse(response);
    } catch (e) {
      throw Exception('Network Error: $e');
    }
  }

  // Generic POST
  static Future<dynamic> post(String url, Map<String, dynamic> body, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(body),
      );
      return _processResponse(response);
    } catch (e) {
      throw Exception('Network Error: $e');
    }
  }

  // Generic PUT
  static Future<dynamic> put(String url, Map<String, dynamic> body, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      final response = await http.put(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(body),
      );
      return _processResponse(response);
    } catch (e) {
      throw Exception('Network Error: $e');
    }
  }

  // Generic DELETE
  static Future<dynamic> delete(String url, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      final response = await http.delete(Uri.parse(url), headers: headers);
      return _processResponse(response);
    } catch (e) {
      throw Exception('Network Error: $e');
    }
  }

  static dynamic _processResponse(http.Response response) {
    final body = jsonDecode(utf8.decode(response.bodyBytes));
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    } else {
      String msg = body['message'] ?? 'Something went wrong';
      throw Exception(msg);
    }
  }
}
