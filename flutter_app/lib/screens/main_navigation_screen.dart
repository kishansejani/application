import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../providers/cart_provider.dart';
import '../theme/app_theme.dart';
import '../utils/responsive.dart';
import 'cart/cart_screen.dart';
import 'catalog/categories_screen.dart';
import 'home/home_screen.dart';
import 'orders/orders_screen.dart';
import 'profile/profile_screen.dart';

/// App shell: M3 NavigationBar on phones, NavigationRail at >= 840dp.
class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({super.key, this.initialIndex = 0});

  static const int homeTab = 0;
  static const int categoriesTab = 1;
  static const int cartTab = 2;
  static const int ordersTab = 3;
  static const int profileTab = 4;

  /// Nearest shell state (null when called from a pushed route).
  static MainNavigationScreenState? of(BuildContext context) =>
      context.findAncestorStateOfType<MainNavigationScreenState>();

  /// Switches tab when inside the shell, otherwise resets the stack to the shell.
  static void goToTab(BuildContext context, int index) {
    final shell = of(context);
    if (shell != null) {
      shell.switchTab(index);
      return;
    }
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => MainNavigationScreen(initialIndex: index)),
      (route) => false,
    );
  }

  @override
  State<MainNavigationScreen> createState() => MainNavigationScreenState();
}

class MainNavigationScreenState extends State<MainNavigationScreen> {
  late int _currentIndex;

  final List<Widget> _pages = const [
    HomeScreen(),
    CategoriesScreen(),
    CartScreen(),
    OrdersScreen(),
    ProfileScreen(),
  ];

  int get currentIndex => _currentIndex;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex.clamp(0, 4).toInt();
  }

  void switchTab(int index) {
    if (index == _currentIndex) return;
    setState(() => _currentIndex = index);
  }

  List<_NavItem> _items(BuildContext context) => [
        _NavItem(Icons.home_outlined, Icons.home_rounded, context.tr('home')),
        _NavItem(Icons.grid_view_outlined, Icons.grid_view_rounded, context.tr('categories')),
        _NavItem(Icons.shopping_bag_outlined, Icons.shopping_bag_rounded, context.tr('cart')),
        _NavItem(Icons.receipt_long_outlined, Icons.receipt_long_rounded, context.tr('orders')),
        _NavItem(Icons.person_outline_rounded, Icons.person_rounded, context.tr('profile')),
      ];

  Widget _icon(IconData icon, int index, int cartCount) {
    final child = Icon(icon);
    if (index != MainNavigationScreen.cartTab) return child;
    return Badge(
      isLabelVisible: cartCount > 0,
      label: Text(cartCount > 99 ? '99+' : '$cartCount'),
      child: child,
    );
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.select<CartProvider, int>((c) => c.itemCount);
    final items = _items(context);
    final useRail = Responsive.useRail(context);
    final extended = Responsive.width(context) >= Breakpoints.expanded;

    final body = IndexedStack(index: _currentIndex, children: _pages);

    final Widget scaffold;
    if (useRail) {
      scaffold = Scaffold(
        body: Row(
          children: [
            SafeArea(
              right: false,
              child: NavigationRail(
                selectedIndex: _currentIndex,
                onDestinationSelected: switchTab,
                extended: extended,
                labelType: extended ? NavigationRailLabelType.none : NavigationRailLabelType.all,
                minExtendedWidth: 220,
                groupAlignment: -0.85,
                leading: Padding(
                  padding: const EdgeInsets.symmetric(vertical: AppSpacing.lg),
                  child: _RailLogo(extended: extended),
                ),
                destinations: [
                  for (var i = 0; i < items.length; i++)
                    NavigationRailDestination(
                      icon: _icon(items[i].icon, i, cartCount),
                      selectedIcon: _icon(items[i].selectedIcon, i, cartCount),
                      label: Text(items[i].label),
                    ),
                ],
              ),
            ),
            const VerticalDivider(width: 1, thickness: 1),
            Expanded(child: body),
          ],
        ),
      );
    } else {
      scaffold = Scaffold(
        body: body,
        bottomNavigationBar: DecoratedBox(
          decoration: BoxDecoration(
            border: Border(top: BorderSide(color: context.colors.outlineVariant.fade(0.5))),
          ),
          child: NavigationBar(
            selectedIndex: _currentIndex,
            onDestinationSelected: switchTab,
            destinations: [
              for (var i = 0; i < items.length; i++)
                NavigationDestination(
                  icon: _icon(items[i].icon, i, cartCount),
                  selectedIcon: _icon(items[i].selectedIcon, i, cartCount),
                  label: items[i].label,
                ),
            ],
          ),
        ),
      );
    }

    // Back from a secondary tab goes to Home first.
    return PopScope<Object?>(
      canPop: _currentIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) setState(() => _currentIndex = 0);
      },
      child: scaffold,
    );
  }
}

class _NavItem {
  final IconData icon;
  final IconData selectedIcon;
  final String label;
  const _NavItem(this.icon, this.selectedIcon, this.label);
}

class _RailLogo extends StatelessWidget {
  final bool extended;
  const _RailLogo({required this.extended});

  @override
  Widget build(BuildContext context) {
    final logo = Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [AppColors.primary, AppColors.primaryDeep]),
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: const Icon(Icons.shopping_basket_rounded, color: Colors.white),
    );
    if (!extended) return logo;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        logo,
        const SizedBox(width: AppSpacing.md),
        Text(context.tr('app_name'), style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
      ],
    );
  }
}
