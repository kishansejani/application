import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/app_dropdown.dart';
import '../../widgets/primary_button.dart';

/// Add / edit an address with an OpenStreetMap pin picker and GPS location.
/// Pops with the saved [Address] (or null).
class AddAddressMapScreen extends StatefulWidget {
  final Address? address;

  const AddAddressMapScreen({super.key, this.address});

  @override
  State<AddAddressMapScreen> createState() => _AddAddressMapScreenState();
}

class _AddAddressMapScreenState extends State<AddAddressMapScreen> {
  // Default: Ahmedabad
  static const LatLng _defaultCenter = LatLng(23.0225, 72.5714);

  final _formKey = GlobalKey<FormState>();
  final _mapController = MapController();
  late final TextEditingController _nameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _houseController;
  late final TextEditingController _streetController;
  late final TextEditingController _landmarkController;
  late final TextEditingController _cityController;
  late final TextEditingController _pincodeController;

  late String _type;
  late bool _isDefault;
  late LatLng _pin;
  bool _pinSet = false;
  bool _saving = false;
  bool _locating = false;

  bool get _isEdit => widget.address != null;

  @override
  void initState() {
    super.initState();
    final a = widget.address;
    final user = context.read<AuthProvider>().user;
    _nameController = TextEditingController(text: a?.recipientName ?? user?.name ?? '');
    _phoneController = TextEditingController(text: a?.recipientPhone ?? user?.phone ?? '');
    _houseController = TextEditingController(text: a?.houseNo ?? '');
    _streetController = TextEditingController(text: a?.streetAddress ?? '');
    _landmarkController = TextEditingController(text: a?.landmark ?? '');
    _cityController = TextEditingController(text: a?.city ?? 'Ahmedabad');
    _pincodeController = TextEditingController(text: a?.pincode ?? '');
    _type = a?.type ?? 'Home';
    _isDefault = a?.isDefault ?? false;
    if (a?.latitude != null && a?.longitude != null) {
      _pin = LatLng(a!.latitude!, a.longitude!);
      _pinSet = true;
    } else {
      _pin = _defaultCenter;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _houseController.dispose();
    _streetController.dispose();
    _landmarkController.dispose();
    _cityController.dispose();
    _pincodeController.dispose();
    super.dispose();
  }

  Future<void> _useCurrentLocation() async {
    setState(() => _locating = true);
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        if (mounted) showAppSnack(context, context.tr('location_service_off'), type: SnackType.error);
        return;
      }
      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
        if (mounted) showAppSnack(context, context.tr('location_permission_denied'), type: SnackType.error);
        return;
      }
      final position = await Geolocator.getCurrentPosition();
      if (!mounted) return;
      final point = LatLng(position.latitude, position.longitude);
      setState(() {
        _pin = point;
        _pinSet = true;
      });
      _mapController.move(point, 16);
      HapticFeedback.selectionClick();
    } catch (e) {
      if (mounted) showAppSnack(context, context.tr('location_error'), type: SnackType.error);
    } finally {
      if (mounted) setState(() => _locating = false);
    }
  }

  void _zoom(double delta) {
    try {
      final camera = _mapController.camera;
      _mapController.move(camera.center, (camera.zoom + delta).clamp(3.0, 18.0).toDouble());
    } catch (_) {}
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    setState(() => _saving = true);
    final address = Address(
      id: widget.address?.id ?? 0,
      type: _type,
      recipientName: _nameController.text.trim(),
      recipientPhone: _phoneController.text.trim(),
      houseNo: _houseController.text.trim(),
      streetAddress: _streetController.text.trim(),
      landmark: _landmarkController.text.trim().isEmpty ? null : _landmarkController.text.trim(),
      city: _cityController.text.trim(),
      pincode: _pincodeController.text.trim(),
      latitude: _pinSet ? _pin.latitude : null,
      longitude: _pinSet ? _pin.longitude : null,
      isDefault: _isDefault,
    );
    try {
      final provider = context.read<AddressProvider>();
      final saved = _isEdit ? await provider.updateAddress(widget.address!.id, address) : await provider.addAddress(address);
      if (!mounted) return;
      showAppSnack(context, context.tr('address_saved'), type: SnackType.success);
      Navigator.of(context).pop(saved ?? address);
    } catch (e) {
      if (mounted) {
        setState(() => _saving = false);
        showAppSnack(context, errorMessage(e), type: SnackType.error);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(context.tr(_isEdit ? 'edit_address' : 'add_new_address'))),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final wide = constraints.maxWidth >= Breakpoints.medium;
          if (wide) {
            return Row(
              children: [
                Expanded(child: Padding(padding: const EdgeInsets.all(AppSpacing.lg), child: _buildMap(radius: AppRadius.lg))),
                SizedBox(
                  width: 460,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(0, AppSpacing.lg, AppSpacing.xl, AppSpacing.xxl),
                    child: _buildForm(),
                  ),
                ),
              ],
            );
          }
          final mapHeight = (constraints.maxHeight * 0.36).clamp(220.0, 320.0).toDouble();
          return SingleChildScrollView(
            child: Column(
              children: [
                SizedBox(height: mapHeight, child: _buildMap()),
                Padding(
                  padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.xxl),
                  child: MaxWidthBox(maxWidth: Breakpoints.maxFormWidth, child: _buildForm()),
                ),
              ],
            ),
          );
        },
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.md),
          child: MaxWidthBox(
            maxWidth: Breakpoints.maxFormWidth,
            child: PrimaryButton(
              label: context.tr('save_address'),
              icon: Icons.check_rounded,
              isLoading: _saving,
              onPressed: _save,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildMap({double radius = 0}) {
    final scheme = context.colors;
    return ClipRRect(
      borderRadius: BorderRadius.circular(radius),
      child: Stack(
        children: [
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: _pin,
              initialZoom: _pinSet ? 16 : 13,
              onTap: (tapPosition, point) {
                HapticFeedback.selectionClick();
                setState(() {
                  _pin = point;
                  _pinSet = true;
                });
              },
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.freshexpress.grocery_app',
              ),
              MarkerLayer(
                markers: [
                  Marker(
                    point: _pin,
                    width: 48,
                    height: 48,
                    alignment: Alignment.topCenter,
                    child: const Icon(Icons.location_on_rounded, color: AppColors.discount, size: 48),
                  ),
                ],
              ),
            ],
          ),
          Positioned(
            top: AppSpacing.md,
            left: AppSpacing.md,
            right: 72,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: scheme.surface.fade(0.95),
                borderRadius: BorderRadius.circular(AppRadius.sm),
                boxShadow: [BoxShadow(color: Colors.black.fade(0.1), blurRadius: 8)],
              ),
              child: Row(
                children: [
                  Icon(_pinSet ? Icons.check_circle_rounded : Icons.touch_app_rounded, size: 18, color: scheme.primary),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      _pinSet ? context.tr('pin_set') : context.tr('tap_map_to_pin'),
                      style: context.textStyles.labelMedium?.copyWith(fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Positioned(
            right: AppSpacing.md,
            top: AppSpacing.md,
            child: Column(
              children: [
                _MapButton(icon: Icons.add_rounded, onTap: () => _zoom(1)),
                const SizedBox(height: AppSpacing.sm),
                _MapButton(icon: Icons.remove_rounded, onTap: () => _zoom(-1)),
              ],
            ),
          ),
          Positioned(
            right: AppSpacing.md,
            bottom: AppSpacing.xl,
            child: FloatingActionButton.small(
              heroTag: 'locate-me',
              tooltip: context.tr('use_current_location'),
              onPressed: _locating ? null : _useCurrentLocation,
              child: _locating
                  ? SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: scheme.onPrimary))
                  : const Icon(Icons.my_location_rounded),
            ),
          ),
          Positioned(
            left: 6,
            bottom: 4,
            child: Text(
              '© OpenStreetMap contributors',
              style: TextStyle(fontSize: 10, color: Colors.black.fade(0.6), backgroundColor: Colors.white.fade(0.7)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildForm() {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AppDropdownField<String>(
            value: _type,
            label: context.tr('address_type'),
            items: [
              AppDropdownItem(value: 'Home', icon: Icons.home_rounded, label: context.tr('home_type')),
              AppDropdownItem(value: 'Work', icon: Icons.work_rounded, label: context.tr('work_type')),
              AppDropdownItem(value: 'Other', icon: Icons.place_rounded, label: context.tr('other_type')),
            ],
            onChanged: (v) => setState(() => _type = v),
          ),
          const SizedBox(height: AppSpacing.xl),
          _field(
            controller: _nameController,
            label: context.tr('recipient_name'),
            icon: Icons.person_outline_rounded,
            validator: (v) => (v == null || v.trim().isEmpty) ? context.tr('required_field') : null,
            capitalization: TextCapitalization.words,
          ),
          _field(
            controller: _phoneController,
            label: context.tr('mobile_number'),
            icon: Icons.phone_outlined,
            keyboard: TextInputType.phone,
            formatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(10)],
            validator: (v) => RegExp(r'^[0-9]{10}$').hasMatch(v?.trim() ?? '') ? null : context.tr('invalid_phone'),
          ),
          _field(
            controller: _houseController,
            label: context.tr('house_flat'),
            icon: Icons.apartment_rounded,
            validator: (v) => (v == null || v.trim().isEmpty) ? context.tr('required_field') : null,
          ),
          _field(
            controller: _streetController,
            label: context.tr('street_area'),
            icon: Icons.map_outlined,
            validator: (v) => (v == null || v.trim().isEmpty) ? context.tr('required_field') : null,
          ),
          _field(
            controller: _landmarkController,
            label: '${context.tr('landmark')} (${context.tr('optional')})',
            icon: Icons.flag_outlined,
          ),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: _field(
                  controller: _cityController,
                  label: context.tr('city'),
                  icon: Icons.location_city_rounded,
                  validator: (v) => (v == null || v.trim().isEmpty) ? context.tr('required_field') : null,
                  capitalization: TextCapitalization.words,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: _field(
                  controller: _pincodeController,
                  label: context.tr('pincode'),
                  icon: Icons.markunread_mailbox_outlined,
                  keyboard: TextInputType.number,
                  formatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(6)],
                  validator: (v) => RegExp(r'^[0-9]{6}$').hasMatch(v?.trim() ?? '') ? null : context.tr('invalid_pincode'),
                ),
              ),
            ],
          ),
          SwitchListTile(
            contentPadding: EdgeInsets.zero,
            value: _isDefault,
            onChanged: (v) => setState(() => _isDefault = v),
            title: Text(context.tr('make_default')),
            secondary: const Icon(Icons.star_outline_rounded),
          ),
        ],
      ),
    );
  }

  Widget _field({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType? keyboard,
    List<TextInputFormatter>? formatters,
    TextCapitalization capitalization = TextCapitalization.sentences,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.md),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboard,
        inputFormatters: formatters,
        textCapitalization: capitalization,
        textInputAction: TextInputAction.next,
        validator: validator,
        decoration: InputDecoration(labelText: label, prefixIcon: Icon(icon)),
      ),
    );
  }
}

class _MapButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  const _MapButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: context.colors.surface,
      borderRadius: BorderRadius.circular(AppRadius.sm),
      elevation: 2,
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.sm),
        onTap: onTap,
        child: SizedBox(width: 40, height: 40, child: Icon(icon, size: 20)),
      ),
    );
  }
}
