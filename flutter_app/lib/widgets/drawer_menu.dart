import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constants/api_constants.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../providers/auth_provider.dart';
import '../providers/locale_provider.dart';
import '../providers/theme_provider.dart';
import '../screens/addresses/addresses_screen.dart';
import '../screens/offers/offers_screen.dart';
import '../screens/orders/orders_screen.dart';
import '../screens/wishlist/wishlist_screen.dart';
import '../theme/app_theme.dart';
import '../utils/app_actions.dart';
import '../utils/formatters.dart';
import '../utils/ui_helpers.dart';

class DrawerMenu extends StatelessWidget {
  const DrawerMenu({super.key});

  void _push(BuildContext context, Widget screen) {
    Navigator.of(context).pop();
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _run(BuildContext context, Future<void> Function(BuildContext ctx) action) {
    final navContext = Navigator.of(context).context;
    Navigator.of(context).pop();
    action(navContext);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final locale = context.watch<LocaleProvider>();
    final theme = context.watch<ThemeProvider>();
    final scheme = context.colors;
    final user = auth.user;

    return Drawer(
      backgroundColor: scheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(AppRadius.xl)),
      ),
      child: Column(
        children: [
          // Header
          Container(
            width: double.infinity,
            padding: EdgeInsets.fromLTRB(AppSpacing.xl, MediaQuery.paddingOf(context).top + AppSpacing.xl, AppSpacing.xl, AppSpacing.xl),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppColors.primary, AppColors.primaryDeep],
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.white,
                  child: auth.isAuthenticated && user != null
                      ? Text(user.initials,
                          style: const TextStyle(color: AppColors.primaryDark, fontWeight: FontWeight.w800, fontSize: 20))
                      : const Icon(Icons.shopping_basket_rounded, color: AppColors.primary, size: 30),
                ),
                const SizedBox(height: AppSpacing.md),
                Text(
                  auth.isAuthenticated && user != null ? user.name : context.tr('app_name'),
                  style: context.textStyles.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 2),
                if (auth.isAuthenticated && user != null)
                  Text(Fmt.phone(user.phone), style: TextStyle(color: Colors.white.fade(0.85)))
                else
                  TextButton.icon(
                    onPressed: () => _run(context, (ctx) => ensureLoggedIn(ctx)),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.zero,
                      minimumSize: const Size(0, 36),
                    ),
                    icon: const Icon(Icons.login_rounded, size: 18),
                    label: Text(context.tr('login_signup')),
                  ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.md),
              children: [
                _DrawerTile(icon: Icons.local_offer_outlined, title: context.tr('offers'), onTap: () => _push(context, const OffersScreen())),
                _DrawerTile(icon: Icons.favorite_border_rounded, title: context.tr('wishlist'), onTap: () => _push(context, const WishlistScreen())),
                _DrawerTile(icon: Icons.receipt_long_outlined, title: context.tr('my_orders'), onTap: () => _push(context, const OrdersScreen(standalone: true))),
                _DrawerTile(
                  icon: Icons.local_shipping_outlined,
                  title: context.tr('track_order'),
                  onTap: () => _run(context, AppActions.promptTrackOrder),
                ),
                _DrawerTile(icon: Icons.location_on_outlined, title: context.tr('saved_addresses'), onTap: () => _push(context, const AddressesScreen())),
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: AppSpacing.sm, horizontal: AppSpacing.lg),
                  child: Divider(),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
                  child: Text(context.tr('language'), style: context.textStyles.labelLarge?.copyWith(color: scheme.onSurfaceVariant)),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                  child: SegmentedButton<String>(
                    segments: [
                      ButtonSegment(value: 'en', label: Text(context.tr('lang_en'))),
                      ButtonSegment(value: 'gu', label: Text(context.tr('lang_gu'))),
                    ],
                    selected: {locale.locale.languageCode},
                    showSelectedIcon: false,
                    onSelectionChanged: (s) => AppActions.changeLanguage(context, s.first),
                  ),
                ),
                const SizedBox(height: AppSpacing.md),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
                  child: Text(context.tr('appearance'), style: context.textStyles.labelLarge?.copyWith(color: scheme.onSurfaceVariant)),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                  child: SegmentedButton<ThemeMode>(
                    segments: const [
                      ButtonSegment(value: ThemeMode.light, icon: Icon(Icons.light_mode_rounded)),
                      ButtonSegment(value: ThemeMode.system, icon: Icon(Icons.brightness_auto_rounded)),
                      ButtonSegment(value: ThemeMode.dark, icon: Icon(Icons.dark_mode_rounded)),
                    ],
                    selected: {theme.themeMode},
                    showSelectedIcon: false,
                    onSelectionChanged: (s) => context.read<ThemeProvider>().setThemeMode(s.first),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: AppSpacing.sm, horizontal: AppSpacing.lg),
                  child: Divider(),
                ),
                _DrawerTile(
                  icon: Icons.info_outline_rounded,
                  title: context.tr('about_us'),
                  onTap: () => _run(context, (ctx) => AppActions.openCmsPage(ctx, ApiConstants.slugAbout, ctx.tr('about_us'))),
                ),
                _DrawerTile(
                  icon: Icons.privacy_tip_outlined,
                  title: context.tr('privacy_policy'),
                  onTap: () => _run(context, (ctx) => AppActions.openCmsPage(ctx, ApiConstants.slugPrivacy, ctx.tr('privacy_policy'))),
                ),
                _DrawerTile(
                  icon: Icons.gavel_rounded,
                  title: context.tr('legal_info'),
                  onTap: () => _run(context, (ctx) => AppActions.openCmsPage(ctx, ApiConstants.slugLegal, ctx.tr('legal_info'))),
                ),
                _DrawerTile(
                  icon: Icons.language_rounded,
                  title: context.tr('visit_website'),
                  onTap: () => _run(context, AppActions.openWebsite),
                ),
                if (auth.isAuthenticated)
                  _DrawerTile(
                    icon: Icons.logout_rounded,
                    title: context.tr('logout'),
                    color: scheme.error,
                    onTap: () => _run(context, AppActions.logout),
                  ),
              ],
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Text(
                '${context.tr('app_name')} • v1.1.0',
                style: context.textStyles.labelSmall?.copyWith(color: scheme.onSurfaceVariant),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _DrawerTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final Color? color;

  const _DrawerTile({required this.icon, required this.title, required this.onTap, this.color});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(title, style: TextStyle(color: color, fontWeight: FontWeight.w600)),
      trailing: Icon(Icons.chevron_right_rounded, color: context.colors.onSurfaceVariant.fade(0.6)),
      onTap: onTap,
      visualDensity: VisualDensity.compact,
    );
  }
}
