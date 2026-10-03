import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../models/json_utils.dart';
import '../models/user.dart';
import '../services/api_service.dart';

/// Why an OTP was sent - also the `purpose` sent to `POST /auth/otp/resend`.
enum OtpPurpose { login, register, reset }

class AuthProvider extends ChangeNotifier {
  static const int defaultOtpLength = 4;
  static const int defaultResendAfter = 30;

  User? _user;
  String? _token;
  bool _isLoading = false;
  String? _lastDemoOtp;
  int _otpLength = defaultOtpLength;
  int _resendAfter = defaultResendAfter;
  Future<void>? _initFuture;

  User? get user => _user;
  String? get token => _token;
  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;

  /// Only present when the backend runs in demo mode - shown as a hint, never relied on.
  String? get lastDemoOtp => _lastDemoOtp;

  /// OTP length from the last send / register / resend response (default 4).
  int get otpLength => _otpLength;

  /// Resend cooldown in seconds from the last OTP response (default 30).
  int get resendAfter => _resendAfter;

  AuthProvider() {
    initAuth();
  }

  /// Safe to call multiple times - the work only happens once.
  Future<void> initAuth() => _initFuture ??= _restoreSession();

  Future<void> _restoreSession() async {
    _token = await ApiService.getToken();
    if (_token != null) {
      await fetchProfile();
    }
    notifyListeners();
  }

  /// Reads `otp_length`, `resend_after` and the optional `demo_otp` from an
  /// OTP response (either inside `data` or at the root of the envelope).
  void _applyOtpMeta(dynamic res) {
    final root = res is Map ? asMap(res) : <String, dynamic>{};
    final data = asMap(ApiService.dataOf(res));
    dynamic pick(String key) => data[key] ?? root[key];

    _lastDemoOtp = asStringOrNull(pick('demo_otp'));
    final length = asIntOrNull(pick('otp_length'));
    _otpLength = (length != null && length >= 4 && length <= 8) ? length : defaultOtpLength;
    final resend = asIntOrNull(pick('resend_after') ?? pick('resend_in'));
    _resendAfter = (resend != null && resend > 0 && resend <= 600) ? resend : defaultResendAfter;
  }

  bool _isOk(dynamic res) => res is Map && (res['status'] == true || res['success'] == true);

  /// Login step 1. Throws [ApiException] with `needsRegistration == true` (404)
  /// when no account exists for [phone].
  Future<bool> sendOtp(String phone) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.post(ApiConstants.sendOtp, {'phone': phone});
      _applyOtpMeta(res);
      return _isOk(res);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Sign-up step 1: sends an OTP; the account is created on [verifyOtp].
  /// Throws [ApiException] with `alreadyRegistered == true` (422) when the
  /// phone already has an account.
  Future<bool> register({
    required String name,
    required String phone,
    String? email,
    String? language,
  }) async {
    _isLoading = true;
    notifyListeners();
    try {
      final body = <String, dynamic>{'name': name.trim(), 'phone': phone};
      final cleanEmail = email?.trim() ?? '';
      if (cleanEmail.isNotEmpty) body['email'] = cleanEmail;
      if (language != null) body['language'] = language;
      final res = await ApiService.post(ApiConstants.register, body);
      _applyOtpMeta(res);
      return _isOk(res);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Resends the OTP for [purpose]. Does not toggle [isLoading] (the OTP screen
  /// shows its own state).
  Future<bool> resendOtp(String phone, OtpPurpose purpose) async {
    try {
      final res = await ApiService.post(ApiConstants.resendOtp, {'phone': phone, 'purpose': purpose.name});
      _applyOtpMeta(res);
      return _isOk(res);
    } on ApiException catch (e) {
      // Older backends without /otp/resend: a login OTP can simply be re-sent.
      final missingRoute = (e.statusCode == 404 || e.statusCode == 405) && !e.needsRegistration;
      if (missingRoute && purpose == OtpPurpose.login) {
        final res = await ApiService.post(ApiConstants.sendOtp, {'phone': phone});
        _applyOtpMeta(res);
        return _isOk(res);
      }
      rethrow;
    }
  }

  /// Verifies the code; for a pending sign-up this creates the account.
  Future<bool> verifyOtp(String phone, String otp, {OtpPurpose? purpose}) async {
    _isLoading = true;
    notifyListeners();
    try {
      final body = <String, dynamic>{'phone': phone, 'otp': otp};
      if (purpose == OtpPurpose.login || purpose == OtpPurpose.register) {
        body['purpose'] = purpose == OtpPurpose.login ? 'login' : 'register';
      }
      final res = await ApiService.post(ApiConstants.verifyOtp, body);
      final data = asMap(ApiService.dataOf(res));
      final token = asStringOrNull(data['token'] ?? (res is Map ? res['token'] : null));
      if (token == null) return false;

      _token = token;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);

      if (data['user'] is Map) {
        _user = User.fromJson(asMap(data['user']));
      }
      // Fetch counts (orders / addresses) in the background.
      fetchProfile();
      return true;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchProfile() async {
    if (_token == null) return;
    try {
      final res = await ApiService.get(ApiConstants.userProfile, requireAuth: true);
      final data = ApiService.dataOf(res);
      if (data is Map) {
        _user = User.fromJson(asMap(data));
        notifyListeners();
      }
    } on ApiException catch (e) {
      // Only drop the session when the token is really invalid - not when offline.
      if (e.isUnauthorized) {
        await _clearSession();
      }
    } catch (_) {}
  }

  Future<void> updateProfile({required String name, String? email, String? language}) async {
    _isLoading = true;
    notifyListeners();
    try {
      final body = <String, dynamic>{'name': name, 'email': (email == null || email.isEmpty) ? null : email};
      if (language != null) body['language'] = language;
      final res = await ApiService.post(ApiConstants.updateProfile, body, requireAuth: true);
      final data = ApiService.dataOf(res);
      if (data is Map) {
        final updated = User.fromJson(asMap(data));
        // The update endpoint does not return counts - keep the previous ones.
        _user = User(
          id: updated.id,
          name: updated.name,
          email: updated.email,
          phone: updated.phone.isNotEmpty ? updated.phone : (_user?.phone ?? ''),
          role: updated.role,
          preferredLanguage: updated.preferredLanguage,
          ordersCount: _user?.ordersCount ?? 0,
          addressesCount: _user?.addressesCount ?? 0,
        );
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        await ApiService.post(ApiConstants.logout, {}, requireAuth: true);
      }
    } catch (_) {}
    await _clearSession();
  }

  Future<void> _clearSession() async {
    _token = null;
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    notifyListeners();
  }
}
