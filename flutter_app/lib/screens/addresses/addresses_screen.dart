import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_dropdown.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/shimmer_box.dart';
import 'add_address_map_screen.dart';

/// Saved addresses. In [selectMode] tapping an address pops it.
class AddressesScreen extends StatefulWidget {
  final bool selectMode;

  const AddressesScreen({super.key, this.selectMode = false});

  @override
  State<AddressesScreen> createState() => _AddressesScreenState();
}

class _AddressesScreenState extends State<AddressesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (context.read<AuthProvider>().isAuthenticated) context.read<AddressProvider>().fetchAddresses();
    });
  }

  Future<void> _openEditor([Address? address]) async {
    await Navigator.of(context).push<Address>(
      MaterialPageRoute(builder: (_) => AddAddressMapScreen(address: address)),
    );
  }

  Future<void> _delete(Address address) async {
    final ok = await confirmDialog(
      context,
      title: context.tr('delete_address'),
      message: context.tr('delete_address_confirm'),
      confirmLabel: context.tr('delete'),
      destructive: true,
      icon: Icons.delete_outline_rounded,
    );
    if (!ok || !mounted) return;
    try {
      await context.read<AddressProvider>().deleteAddress(address.id);
      if (mounted) showAppSnack(context, context.tr('address_deleted'), type: SnackType.success);
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  Future<void> _setDefault(Address address) async {
    try {
      await context.read<AddressProvider>().setDefault(address.id);
      if (mounted) showAppSnack(context, context.tr('default_address_set'), type: SnackType.success);
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isAuth = context.select<AuthProvider, bool>((a) => a.isAuthenticated);
    final provider = context.watch<AddressProvider>();

    Widget body;
    if (!isAuth) {
      body = const LoginRequiredState(icon: Icons.location_on_outlined);
    } else if (provider.isLoading && !provider.hasLoaded) {
      body = const ListSkeleton(itemCount: 3, itemHeight: 130);
    } else if (provider.error != null && !provider.hasLoaded) {
      body = ErrorState(error: provider.error, onRetry: provider.fetchAddresses);
    } else if (provider.addresses.isEmpty) {
      body = EmptyState(
        icon: Icons.location_off_outlined,
        title: context.tr('no_addresses'),
        message: context.tr('no_address_msg'),
        actionLabel: context.tr('add_new_address'),
        actionIcon: Icons.add_location_alt_rounded,
        onAction: () => _openEditor(),
      );
    } else {
      body = RefreshIndicator(
        onRefresh: provider.fetchAddresses,
        child: ListView.separated(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, 100),
          itemCount: provider.addresses.length,
          separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
          itemBuilder: (context, i) {
            final a = provider.addresses[i];
            return MaxWidthBox(
              maxWidth: 720,
              child: _AddressCard(
                address: a,
                onTap: widget.selectMode ? () => Navigator.of(context).pop(a) : () => _openEditor(a),
                onEdit: () => _openEditor(a),
                onDelete: () => _delete(a),
                onSetDefault: a.isDefault ? null : () => _setDefault(a),
              ),
            );
          },
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: Text(context.tr(widget.selectMode ? 'select_address' : 'saved_addresses'))),
      body: body,
      floatingActionButton: isAuth && provider.addresses.isNotEmpty
          ? FloatingActionButton.extended(
              onPressed: () => _openEditor(),
              icon: const Icon(Icons.add_location_alt_rounded),
              label: Text(context.tr('add_new_address')),
            )
          : null,
    );
  }
}

class _AddressCard extends StatelessWidget {
  final Address address;
  final VoidCallback onTap;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  final VoidCallback? onSetDefault;

  const _AddressCard({
    required this.address,
    required this.onTap,
    required this.onEdit,
    required this.onDelete,
    this.onSetDefault,
  });

  IconData get _icon {
    switch (address.type) {
      case 'Work':
        return Icons.work_rounded;
      case 'Other':
        return Icons.place_rounded;
      default:
        return Icons.home_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return AppCard(
      onTap: onTap,
      borderColor: address.isDefault ? scheme.primary.fade(0.6) : null,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: scheme.primaryContainer, borderRadius: BorderRadius.circular(AppRadius.xs)),
                child: Icon(_icon, size: 18, color: scheme.onPrimaryContainer),
              ),
              const SizedBox(width: AppSpacing.md),
              Text(
                context.tr('address_type_${address.type.toLowerCase()}'),
                style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800),
              ),
              if (address.isDefault) ...[
                const SizedBox(width: AppSpacing.sm),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(color: scheme.primary, borderRadius: BorderRadius.circular(AppRadius.pill)),
                  child: Text(
                    context.tr('default'),
                    style: TextStyle(color: scheme.onPrimary, fontSize: 11, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
              const Spacer(),
              PopupMenuButton<String>(
                tooltip: context.tr('more'),
                icon: const Icon(Icons.more_vert_rounded),
                onSelected: (v) {
                  if (v == 'edit') onEdit();
                  if (v == 'delete') onDelete();
                  if (v == 'default') onSetDefault?.call();
                },
                itemBuilder: (ctx) => [
                  appMenuItem<String>(value: 'edit', icon: Icons.edit_rounded, label: ctx.tr('edit')),
                  if (onSetDefault != null)
                    appMenuItem<String>(
                      value: 'default',
                      icon: Icons.check_circle_outline_rounded,
                      label: ctx.tr('set_default'),
                    ),
                  appMenuItem<String>(
                    value: 'delete',
                    icon: Icons.delete_outline_rounded,
                    label: ctx.tr('delete'),
                    destructive: true,
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.sm),
          Text(
            address.recipientName,
            style: context.textStyles.bodyLarge?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 2),
          Text(
            address.fullAddress,
            style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant, height: 1.45),
          ),
          const SizedBox(height: AppSpacing.xs),
          Row(
            children: [
              Icon(Icons.phone_rounded, size: 14, color: scheme.onSurfaceVariant),
              const SizedBox(width: 4),
              Text(Fmt.phone(address.recipientPhone), style: context.textStyles.bodySmall),
              if (address.latitude != null) ...[
                const SizedBox(width: AppSpacing.md),
                Icon(Icons.my_location_rounded, size: 14, color: scheme.primary),
                const SizedBox(width: 4),
                Text(context.tr('pinned_on_map'), style: context.textStyles.bodySmall?.copyWith(color: scheme.primary)),
              ],
            ],
          ),
        ],
      ),
    );
  }
}
