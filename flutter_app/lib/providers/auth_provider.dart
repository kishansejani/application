import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  String? _token;
  bool _isLoading = false;

  User? get user => _user;
  String? get token => _token;
  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;

  AuthProvider() {
    initAuth();
  }

  Future<void> initAuth() async {
    _token = await ApiService.getToken();
    if (_token != null) {
      await fetchProfile();
    }
    notifyListeners();
  }

  Future<bool> sendOtp(String phone) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.post(ApiConstants.sendOtp, {'phone': phone});
      _isLoading = false;
      notifyListeners();
      return res['success'] == true;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  Future<bool> verifyOtp(String phone, String otp) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.post(ApiConstants.verifyOtp, {
        'phone': phone,
        'otp': otp,
      });

      if (res['token'] != null) {
        _token = res['token'];
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);

        if (res['user'] != null) {
          _user = User.fromJson(res['user']);
        }
        _isLoading = false;
        notifyListeners();
        return true;
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  Future<void> fetchProfile() async {
    try {
      final res = await ApiService.get(ApiConstants.userProfile, requireAuth: true);
      if (res['user'] != null) {
        _user = User.fromJson(res['user']);
        notifyListeners();
      }
    } catch (e) {
      // Token might have expired
      await logout();
    }
  }

  Future<void> updateProfile({required String name, String? email}) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.post(
        ApiConstants.updateProfile,
        {'name': name, 'email': email},
        requireAuth: true,
      );
      if (res['user'] != null) {
        _user = User.fromJson(res['user']);
      }
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        await ApiService.post(ApiConstants.logout, {}, requireAuth: true);
      }
    } catch (_) {}
    _token = null;
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    notifyListeners();
  }
}
