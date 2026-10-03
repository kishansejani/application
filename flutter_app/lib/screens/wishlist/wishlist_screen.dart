import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/product_card.dart';
import '../../widgets/shimmer_box.dart';
import '../main_navigation_screen.dart';

class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (context.read<AuthProvider>().isAuthenticated) {
        context.read<WishlistProvider>().fetchWishlist();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final isAuth = context.select<AuthProvider, bool>((a) => a.isAuthenticated);
    final wishlist = context.watch<WishlistProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(context.tr('wishlist')),
            if (isAuth && wishlist.count > 0)
              Text(
                context.tr('items_count', {'count': '${wishlist.count}'}),
                style: context.textStyles.bodySmall?.copyWith(color: context.colors.onSurfaceVariant),
              ),
          ],
        ),
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final hPad = Responsive.centeredPadding(constraints.maxWidth);
          final columns = Responsive.gridColumns(constraints.maxWidth - hPad * 2);

          if (!isAuth) return const LoginRequiredState(icon: Icons.favorite_border_rounded);
          if (wishlist.isLoading && !wishlist.hasLoaded) {
            return ProductGridSkeleton(columns: columns, padding: EdgeInsets.symmetric(horizontal: hPad, vertical: AppSpacing.lg));
          }
          if (wishlist.error != null && !wishlist.hasLoaded) {
            return ErrorState(error: wishlist.error, onRetry: wishlist.fetchWishlist);
          }
          if (wishlist.items.isEmpty) {
            return RefreshIndicator(
              onRefresh: wishlist.fetchWishlist,
              child: EmptyState(
                icon: Icons.favorite_border_rounded,
                title: context.tr('empty_wishlist'),
                message: context.tr('empty_wishlist_msg'),
                actionLabel: context.tr('start_shopping'),
                actionIcon: Icons.storefront_rounded,
                onAction: () => MainNavigationScreen.goToTab(context, MainNavigationScreen.homeTab),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: wishlist.fetchWishlist,
            child: GridView.builder(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.xxl),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: columns,
                mainAxisSpacing: AppSpacing.md,
                crossAxisSpacing: AppSpacing.md,
                mainAxisExtent: 290,
              ),
              itemCount: wishlist.items.length,
              itemBuilder: (context, i) => ProductCard(product: wishlist.items[i]),
            ),
          );
        },
      ),
    );
  }
}
