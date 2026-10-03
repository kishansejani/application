import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/api_constants.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/locale_provider.dart';
import '../../providers/order_provider.dart';
import '../../providers/theme_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_dropdown.dart';
import '../addresses/addresses_screen.dart';
import '../offers/offers_screen.dart';
import '../orders/orders_screen.dart';
import '../wishlist/wishlist_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  void _push(BuildContext context, Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  Future<void> _editProfile(BuildContext context) async {
    final auth = context.read<AuthProvider>();
    final user = auth.user;
    if (user == null) return;
    final nameController = TextEditingController(text: user.name);
    final emailController = TextEditingController(text: user.email ?? '');
    final formKey = GlobalKey<FormState>();

    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.viewInsetsOf(ctx).bottom),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(AppSpacing.xl, 0, AppSpacing.xl, AppSpacing.xl),
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(ctx.tr('edit_profile'), style: ctx.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
                  const SizedBox(height: AppSpacing.lg),
                  TextFormField(
                    controller: nameController,
                    textCapitalization: TextCapitalization.words,
                    decoration: InputDecoration(labelText: ctx.tr('full_name'), prefixIcon: const Icon(Icons.person_outline_rounded)),
                    validator: (v) => (v == null || v.trim().isEmpty) ? ctx.tr('required_field') : null,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  TextFormField(
                    controller: emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: '${ctx.tr('email')} (${ctx.tr('optional')})',
                      prefixIcon: const Icon(Icons.email_outlined),
                    ),
                    validator: (v) {
                      final value = v?.trim() ?? '';
                      if (value.isEmpty) return null;
                      return RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value) ? null : ctx.tr('invalid_email');
                    },
                  ),
                  const SizedBox(height: AppSpacing.md),
                  TextFormField(
                    initialValue: Fmt.phone(user.phone),
                    enabled: false,
                    decoration: InputDecoration(labelText: ctx.tr('mobile_number'), prefixIcon: const Icon(Icons.phone_outlined)),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: () {
                        if (formKey.currentState!.validate()) Navigator.of(ctx).pop(true);
                      },
                      child: Text(ctx.tr('save')),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    if (saved != true || !context.mounted) return;
    try {
      await auth.updateProfile(
        name: nameController.text.trim(),
        email: emailController.text.trim(),
        language: context.read<LocaleProvider>().locale.languageCode,
      );
      if (context.mounted) showAppSnack(context, context.tr('profile_updated'), type: SnackType.success);
    } catch (e) {
      if (context.mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final locale = context.watch<LocaleProvider>();
    final theme = context.watch<ThemeProvider>();
    final wishCount = context.select<WishlistProvider, int>((w) => w.count);
    final orderCount = context.select<OrderProvider, int>((o) => o.orders.length);
    final addressCount = context.select<AddressProvider, int>((a) => a.addresses.length);
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(title: Text(context.tr('profile'))),
      body: RefreshIndicator(
        onRefresh: () async {
          if (auth.isAuthenticated) await auth.fetchProfile();
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.xxl),
          children: [
            MaxWidthBox(
              maxWidth: 720,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _ProfileHeader(
                    name: auth.isAuthenticated && user != null ? user.name : context.tr('guest_user'),
                    subtitle: auth.isAuthenticated && user != null
                        ? [Fmt.phone(user.phone), if (user.email != null) user.email!].join('  •  ')
                        : context.tr('login_to_continue'),
                    initials: auth.isAuthenticated && user != null ? user.initials : null,
                    onEdit: auth.isAuthenticated ? () => _editProfile(context) : null,
                    onLogin: auth.isAuthenticated ? null : () => ensureLoggedIn(context),
                  ),
                  if (auth.isAuthenticated) ...[
                    const SizedBox(height: AppSpacing.md),
                    Row(
                      children: [
                        _StatTile(
                          icon: Icons.receipt_long_rounded,
                          value: '${orderCount > 0 ? orderCount : (user?.ordersCount ?? 0)}',
                          label: context.tr('orders'),
                          onTap: () => _push(context, const OrdersScreen(standalone: true)),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        _StatTile(
                          icon: Icons.favorite_rounded,
                          value: '$wishCount',
                          label: context.tr('wishlist'),
                          onTap: () => _push(context, const WishlistScreen()),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        _StatTile(
                          icon: Icons.location_on_rounded,
                          value: '${addressCount > 0 ? addressCount : (user?.addressesCount ?? 0)}',
                          label: context.tr('addresses'),
                          onTap: () => _push(context, const AddressesScreen()),
                        ),
                      ],
                    ),
                  ],
                  _SectionLabel(context.tr('my_account')),
                  _TileGroup(children: [
                    _SettingsTile(
                      icon: Icons.receipt_long_outlined,
                      title: context.tr('my_orders'),
                      onTap: () => _push(context, const OrdersScreen(standalone: true)),
                    ),
                    _SettingsTile(
                      icon: Icons.favorite_border_rounded,
                      title: context.tr('wishlist'),
                      onTap: () => _push(context, const WishlistScreen()),
                    ),
                    _SettingsTile(
                      icon: Icons.location_on_outlined,
                      title: context.tr('saved_addresses'),
                      onTap: () => _push(context, const AddressesScreen()),
                    ),
                    _SettingsTile(
                      icon: Icons.local_offer_outlined,
                      title: context.tr('offers_coupons'),
                      onTap: () => _push(context, const OffersScreen()),
                    ),
                    _SettingsTile(
                      icon: Icons.local_shipping_outlined,
                      title: context.tr('track_order'),
                      onTap: () => AppActions.promptTrackOrder(context),
                    ),
                  ]),
                  _SectionLabel(context.tr('preferences')),
                  _TileGroup(children: [
                    Padding(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          AppDropdownField<String>(
                            value: locale.locale.languageCode,
                            label: context.tr('language'),
                            prefixIcon: Icons.translate_rounded,
                            items: [
                              AppDropdownItem(value: 'en', label: context.tr('lang_en')),
                              AppDropdownItem(value: 'gu', label: context.tr('lang_gu')),
                            ],
                            onChanged: (code) => AppActions.changeLanguage(context, code),
                          ),
                          const SizedBox(height: AppSpacing.xl),
                          Row(
                            children: [
                              Icon(Icons.palette_outlined, color: context.colors.onSurfaceVariant),
                              const SizedBox(width: AppSpacing.lg),
                              Text(context.tr('appearance'), style: const TextStyle(fontWeight: FontWeight.w600)),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.md),
                          SizedBox(
                            width: double.infinity,
                            child: SegmentedButton<ThemeMode>(
                              segments: [
                                ButtonSegment(
                                  value: ThemeMode.light,
                                  icon: const Icon(Icons.light_mode_rounded),
                                  label: Text(context.tr('theme_light')),
                                ),
                                ButtonSegment(
                                  value: ThemeMode.system,
                                  icon: const Icon(Icons.brightness_auto_rounded),
                                  label: Text(context.tr('theme_system')),
                                ),
                                ButtonSegment(
                                  value: ThemeMode.dark,
                                  icon: const Icon(Icons.dark_mode_rounded),
                                  label: Text(context.tr('theme_dark')),
                                ),
                              ],
                              selected: {theme.themeMode},
                              showSelectedIcon: false,
                              onSelectionChanged: (s) => context.read<ThemeProvider>().setThemeMode(s.first),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ]),
                  _SectionLabel(context.tr('support_info')),
                  _TileGroup(children: [
                    _SettingsTile(
                      icon: Icons.info_outline_rounded,
                      title: context.tr('about_us'),
                      onTap: () => AppActions.openCmsPage(context, ApiConstants.slugAbout, context.tr('about_us')),
                    ),
                    _SettingsTile(
                      icon: Icons.privacy_tip_outlined,
                      title: context.tr('privacy_policy'),
                      onTap: () => AppActions.openCmsPage(context, ApiConstants.slugPrivacy, context.tr('privacy_policy')),
                    ),
                    _SettingsTile(
                      icon: Icons.gavel_rounded,
                      title: context.tr('terms_conditions'),
                      onTap: () => AppActions.openCmsPage(context, ApiConstants.slugLegal, context.tr('terms_conditions')),
                    ),
                    _SettingsTile(
                      icon: Icons.language_rounded,
                      title: context.tr('visit_website'),
                      trailing: Icon(Icons.open_in_new_rounded, size: 18, color: context.colors.onSurfaceVariant),
                      onTap: () => AppActions.openWebsite(context),
                    ),
                    _SettingsTile(
                      icon: Icons.share_outlined,
                      title: context.tr('share_app'),
                      onTap: () => AppActions.copyToClipboard(
                        context,
                        ApiConstants.websiteUrl,
                        message: context.tr('share_link_copied'),
                      ),
                    ),
                    _SettingsTile(
                      icon: Icons.star_outline_rounded,
                      title: context.tr('rate_app'),
                      onTap: () => showAppSnack(context, context.tr('coming_soon')),
                    ),
                  ]),
                  if (auth.isAuthenticated) ...[
                    const SizedBox(height: AppSpacing.xl),
                    OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: context.colors.error,
                        side: BorderSide(color: context.colors.error.fade(0.5)),
                      ),
                      onPressed: () => AppActions.logout(context),
                      icon: const Icon(Icons.logout_rounded),
                      label: Text(context.tr('logout')),
                    ),
                  ],
                  const SizedBox(height: AppSpacing.xl),
                  Center(
                    child: Text(
                      '${context.tr('app_name')} • ${context.tr('version')} 1.1.0',
                      style: context.textStyles.labelSmall?.copyWith(color: context.colors.onSurfaceVariant),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  final String name;
  final String subtitle;
  final String? initials;
  final VoidCallback? onEdit;
  final VoidCallback? onLogin;

  const _ProfileHeader({required this.name, required this.subtitle, this.initials, this.onEdit, this.onLogin});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.xl),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.primary, AppColors.primaryDeep],
        ),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        boxShadow: [BoxShadow(color: AppColors.primary.fade(0.25), blurRadius: 20, offset: const Offset(0, 8))],
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 32,
            backgroundColor: Colors.white,
            child: initials != null
                ? Text(initials!, style: const TextStyle(color: AppColors.primaryDark, fontWeight: FontWeight.w800, fontSize: 22))
                : const Icon(Icons.person_rounded, color: AppColors.primary, size: 34),
          ),
          const SizedBox(width: AppSpacing.lg),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.bodySmall?.copyWith(color: Colors.white.fade(0.85)),
                ),
                if (onLogin != null) ...[
                  const SizedBox(height: AppSpacing.md),
                  FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: AppColors.primaryDark,
                      minimumSize: const Size(0, 40),
                    ),
                    onPressed: onLogin,
                    icon: const Icon(Icons.login_rounded, size: 18),
                    label: Text(context.tr('login_signup')),
                  ),
                ],
              ],
            ),
          ),
          if (onEdit != null)
            IconButton.filled(
              tooltip: context.tr('edit_profile'),
              style: IconButton.styleFrom(backgroundColor: Colors.white.fade(0.2), foregroundColor: Colors.white),
              onPressed: onEdit,
              icon: const Icon(Icons.edit_rounded, size: 20),
            ),
        ],
      ),
    );
  }
}

class _StatTile extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final VoidCallback onTap;

  const _StatTile({required this.icon, required this.value, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: AppCard(
        onTap: onTap,
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: AppSpacing.sm),
        child: Column(
          children: [
            Icon(icon, color: context.colors.primary),
            const SizedBox(height: 4),
            Text(value, style: context.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
            Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: context.textStyles.labelSmall?.copyWith(color: context.colors.onSurfaceVariant),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel(this.text);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(AppSpacing.xs, AppSpacing.xl, AppSpacing.xs, AppSpacing.sm),
      child: Text(
        text.toUpperCase(),
        style: context.textStyles.labelMedium?.copyWith(
          color: context.colors.onSurfaceVariant,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.8,
        ),
      ),
    );
  }
}

class _TileGroup extends StatelessWidget {
  final List<Widget> children;
  const _TileGroup({required this.children});

  @override
  Widget build(BuildContext context) {
    final items = <Widget>[];
    for (var i = 0; i < children.length; i++) {
      items.add(children[i]);
      if (i < children.length - 1) items.add(const Divider(indent: 56));
    }
    return AppCard(padding: EdgeInsets.zero, child: Column(children: items));
  }
}

class _SettingsTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final Widget? trailing;

  const _SettingsTile({required this.icon, required this.title, required this.onTap, this.trailing});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      trailing: trailing ?? Icon(Icons.chevron_right_rounded, color: context.colors.onSurfaceVariant.fade(0.6)),
      onTap: onTap,
      shape: const RoundedRectangleBorder(),
    );
  }
}
