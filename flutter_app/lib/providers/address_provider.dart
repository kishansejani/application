import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/address.dart';
import '../models/json_utils.dart';
import '../services/api_service.dart';

class AddressProvider extends ChangeNotifier {
  List<Address> _addresses = [];
  bool _isLoading = false;
  bool _hasLoaded = false;
  Object? _error;

  List<Address> get addresses => _addresses;
  bool get isLoading => _isLoading;
  bool get hasLoaded => _hasLoaded;
  Object? get error => _error;

  Address? get defaultAddress {
    if (_addresses.isEmpty) return null;
    for (final a in _addresses) {
      if (a.isDefault) return a;
    }
    return _addresses.first;
  }

  Address? byId(int? id) {
    if (id == null) return null;
    for (final a in _addresses) {
      if (a.id == id) return a;
    }
    return null;
  }

  Future<void> fetchAddresses() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.addresses, requireAuth: true);
      _addresses = mapList(ApiService.dataOf(res), (e) => Address.fromJson(e));
      // Default address first.
      _addresses.sort((a, b) => (b.isDefault ? 1 : 0) - (a.isDefault ? 1 : 0));
      _error = null;
      _hasLoaded = true;
    } catch (e) {
      _error = e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<Address?> addAddress(Address address) async {
    final res = await ApiService.post(ApiConstants.addresses, address.toJson(), requireAuth: true);
    final data = ApiService.dataOf(res);
    await fetchAddresses();
    return data is Map ? Address.fromJson(asMap(data)) : null;
  }

  Future<Address?> updateAddress(int id, Address address) async {
    final res = await ApiService.put(ApiConstants.address(id), address.toJson(), requireAuth: true);
    if (address.isDefault) {
      await ApiService.patch(ApiConstants.addressDefault(id), {}, requireAuth: true);
    }
    final data = ApiService.dataOf(res);
    await fetchAddresses();
    return data is Map ? Address.fromJson(asMap(data)) : null;
  }

  Future<void> deleteAddress(int id) async {
    await ApiService.delete(ApiConstants.address(id), requireAuth: true);
    _addresses.removeWhere((a) => a.id == id);
    notifyListeners();
    await fetchAddresses();
  }

  Future<void> setDefault(int id) async {
    await ApiService.patch(ApiConstants.addressDefault(id), {}, requireAuth: true);
    await fetchAddresses();
  }

  void clear() {
    _addresses = [];
    _hasLoaded = false;
    _error = null;
    notifyListeners();
  }
}
