import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/category.dart';
import '../../models/product.dart';
import '../../models/slider_item.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/banner_carousel.dart';
import '../../widgets/category_card.dart';
import '../../widgets/delivery_slot_banner.dart';
import '../../widgets/drawer_menu.dart';
import '../../widgets/error_state.dart';
import '../../widgets/offer_card.dart';
import '../../widgets/product_card.dart';
import '../../widgets/section_header.dart';
import '../../widgets/shimmer_box.dart';
import '../catalog/product_detail_screen.dart';
import '../catalog/product_list_screen.dart';
import '../catalog/subcategories_screen.dart';
import '../main_navigation_screen.dart';
import '../offers/offers_screen.dart';
import '../webview/app_webview_screen.dart';
import '../wishlist/wishlist_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<ProductProvider>();
      if (!provider.homeLoaded && !provider.isLoadingHome) provider.fetchHomeData();
    });
  }

  Future<void> _refresh() => context.read<ProductProvider>().fetchHomeData();

  void _openCategory(Category category) {
    final Widget screen = category.subCategories.isEmpty
        ? ProductListScreen(title: category.nameFor(context.langCode), categoryId: category.id)
        : SubcategoriesScreen(category: category);
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _onSliderTap(SliderItem item) {
    final provider = context.read<ProductProvider>();
    final target = item.targetId;
    switch (item.linkType) {
      case 'category':
        if (target == null) return;
        final category = provider.categoryById(target);
        if (category != null) {
          _openCategory(category);
        } else {
          _push(ProductListScreen(title: item.title, categoryId: target));
        }
        break;
      case 'subcategory':
        if (target == null) return;
        _push(ProductListScreen(title: item.title, subCategoryId: target));
        break;
      case 'product':
        if (target == null) return;
        _push(ProductDetailScreen(productId: target));
        break;
      case 'offer':
        _push(const OffersScreen());
        break;
      case 'custom':
        final url = item.linkUrl;
        if (url != null && url.startsWith('http')) {
          AppWebViewScreen.open(context, url: url, title: item.title);
        }
        break;
      default:
        break;
    }
  }

  void _push(Widget screen) => Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ProductProvider>();
    final showSkeleton = provider.isLoadingHome && !provider.homeLoaded;
    final showError = provider.homeError != null && !provider.homeLoaded;

    return Scaffold(
      drawer: const DrawerMenu(),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final hPad = Responsive.centeredPadding(width);
          final contentWidth = width - hPad * 2;
          final columns = Responsive.gridColumns(contentWidth);

          return RefreshIndicator(
            onRefresh: _refresh,
            edgeOffset: 120,
            child: CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                _HomeAppBar(horizontalPadding: hPad),
                if (showError)
                  SliverFillRemaining(
                    hasScrollBody: false,
                    child: ErrorState(error: provider.homeError, onRetry: _refresh, scrollable: false),
                  )
                else ...[
                  SliverPadding(
                    padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, 0),
                    sliver: SliverToBoxAdapter(
                      child: DeliverySlotBanner(slotInfo: provider.deliverySlotInfo),
                    ),
                  ),
                  // Banner carousel
                  SliverPadding(
                    padding: EdgeInsets.fromLTRB(hPad, AppSpacing.lg, hPad, 0),
                    sliver: SliverToBoxAdapter(
                      child: showSkeleton
                          ? ShimmerBox(height: contentWidth >= 700 ? 240 : 170, radius: AppRadius.lg)
                          : (provider.sliders.isEmpty
                              ? const SizedBox.shrink()
                              : BannerCarousel(
                                  items: provider.sliders,
                                  height: contentWidth >= 700 ? 240 : 170,
                                  onTap: _onSliderTap,
                                )),
                    ),
                  ),
                  // Categories
                  SliverPadding(
                    padding: EdgeInsets.symmetric(horizontal: hPad),
                    sliver: SliverToBoxAdapter(
                      child: SectionHeader(
                        title: context.tr('featured_categories'),
                        onAction: () => MainNavigationScreen.goToTab(context, MainNavigationScreen.categoriesTab),
                      ),
                    ),
                  ),
                  _CategoryGrid(
                    categories: provider.categories,
                    loading: showSkeleton,
                    horizontalPadding: hPad,
                    contentWidth: contentWidth,
                    onTap: _openCategory,
                  ),
                  // Offers strip
                  if (provider.offers.isNotEmpty) ...[
                    SliverPadding(
                      padding: EdgeInsets.symmetric(horizontal: hPad),
                      sliver: SliverToBoxAdapter(
                        child: SectionHeader(
                          title: context.tr('offers_for_you'),
                          onAction: () => _push(const OffersScreen()),
                        ),
                      ),
                    ),
                    SliverToBoxAdapter(
                      child: SizedBox(
                        height: 132,
                        child: ListView.separated(
                          padding: EdgeInsets.symmetric(horizontal: hPad),
                          scrollDirection: Axis.horizontal,
                          itemCount: provider.offers.length,
                          separatorBuilder: (_, __) => const SizedBox(width: AppSpacing.md),
                          itemBuilder: (context, i) => SizedBox(
                            width: 300,
                            child: OfferCard(offer: provider.offers[i], compact: true),
                          ),
                        ),
                      ),
                    ),
                  ],
                  // Featured rail
                  SliverPadding(
                    padding: EdgeInsets.symmetric(horizontal: hPad),
                    sliver: SliverToBoxAdapter(
                      child: SectionHeader(
                        title: context.tr('featured_products'),
                        subtitle: context.tr('featured_products_sub'),
                        onAction: () => _push(ProductListScreen(title: context.tr('featured_products'), initialSort: 'featured')),
                      ),
                    ),
                  ),
                  SliverToBoxAdapter(
                    child: _ProductRail(
                      products: provider.featuredProducts,
                      loading: showSkeleton,
                      horizontalPadding: hPad,
                      cardWidth: contentWidth >= 700 ? 200 : 168,
                    ),
                  ),
                  // Fresh arrivals grid
                  if (showSkeleton || provider.newProducts.isNotEmpty) ...[
                    SliverPadding(
                      padding: EdgeInsets.symmetric(horizontal: hPad),
                      sliver: SliverToBoxAdapter(
                        child: SectionHeader(
                          title: context.tr('new_arrivals'),
                          subtitle: context.tr('new_arrivals_sub'),
                          onAction: () => _push(ProductListScreen(title: context.tr('new_arrivals'), initialSort: 'newest')),
                        ),
                      ),
                    ),
                    SliverPadding(
                      padding: EdgeInsets.symmetric(horizontal: hPad),
                      sliver: SliverGrid(
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: columns,
                          mainAxisSpacing: AppSpacing.md,
                          crossAxisSpacing: AppSpacing.md,
                          mainAxisExtent: 290,
                        ),
                        delegate: SliverChildBuilderDelegate(
                          (context, i) => showSkeleton
                              ? const ProductCardSkeleton()
                              : ProductCard(product: provider.newProducts[i]),
                          childCount: showSkeleton ? columns * 2 : _gridCount(provider.newProducts.length, columns),
                        ),
                      ),
                    ),
                  ],
                  SliverPadding(
                    padding: EdgeInsets.fromLTRB(hPad, AppSpacing.xl, hPad, AppSpacing.xxl),
                    sliver: const SliverToBoxAdapter(child: _TrustRow()),
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }

  /// Show complete rows only (max 3 rows).
  int _gridCount(int available, int columns) {
    final maxItems = columns * 3;
    if (available >= maxItems) return maxItems;
    if (available <= columns) return available;
    return available - (available % columns);
  }
}

class _HomeAppBar extends StatelessWidget {
  final double horizontalPadding;
  const _HomeAppBar({required this.horizontalPadding});

  String _greetingKey() {
    final hour = DateTime.now().hour;
    if (hour < 12) return 'good_morning';
    if (hour < 17) return 'good_afternoon';
    return 'good_evening';
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final address = context.watch<AddressProvider>().defaultAddress;
    final wishCount = context.select<WishlistProvider, int>((w) => w.count);
    final scheme = context.colors;
    final name = auth.user?.name.split(' ').first;

    return SliverAppBar(
      floating: true,
      snap: true,
      pinned: false,
      toolbarHeight: 64,
      titleSpacing: 0,
      leading: Builder(
        builder: (ctx) => IconButton(
          icon: const Icon(Icons.menu_rounded),
          tooltip: ctx.tr('menu'),
          onPressed: () => Scaffold.of(ctx).openDrawer(),
        ),
      ),
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            auth.isAuthenticated && name != null && name.isNotEmpty
                ? '${context.tr(_greetingKey())}, $name 👋'
                : context.tr(_greetingKey()),
            style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          Row(
            children: [
              Icon(Icons.location_on_rounded, size: 14, color: scheme.primary),
              const SizedBox(width: 2),
              Flexible(
                child: Text(
                  address != null
                      ? '${context.tr('deliver_to')}: ${address.city}, ${address.pincode}'
                      : context.tr('delivering_in_gujarat'),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                ),
              ),
            ],
          ),
        ],
      ),
      actions: [
        IconButton(
          tooltip: context.tr('offers'),
          icon: const Icon(Icons.local_offer_outlined),
          onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const OffersScreen())),
        ),
        IconButton(
          tooltip: context.tr('wishlist'),
          icon: Badge(
            isLabelVisible: wishCount > 0,
            label: Text('$wishCount'),
            child: const Icon(Icons.favorite_border_rounded),
          ),
          onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const WishlistScreen())),
        ),
        const SizedBox(width: AppSpacing.xs),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(64),
        child: Padding(
          padding: EdgeInsets.fromLTRB(horizontalPadding, 0, horizontalPadding, AppSpacing.md),
          child: _SearchField(
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => ProductListScreen(title: context.tr('search'), startWithSearch: true)),
            ),
          ),
        ),
      ),
    );
  }
}

class _SearchField extends StatelessWidget {
  final VoidCallback onTap;
  const _SearchField({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Semantics(
      button: true,
      label: context.tr('search_placeholder'),
      child: Material(
        color: context.isDark ? scheme.surfaceContainerHigh : Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          side: BorderSide(color: scheme.outlineVariant.fade(0.8)),
        ),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppRadius.md),
          child: SizedBox(
            height: 50,
            child: Row(
              children: [
                const SizedBox(width: AppSpacing.lg),
                Icon(Icons.search_rounded, color: scheme.primary),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Text(
                    context.tr('search_placeholder'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
                  ),
                ),
                Container(
                  margin: const EdgeInsets.all(6),
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: scheme.primaryContainer,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  child: Icon(Icons.tune_rounded, size: 18, color: scheme.onPrimaryContainer),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _CategoryGrid extends StatelessWidget {
  final List<Category> categories;
  final bool loading;
  final double horizontalPadding;
  final double contentWidth;
  final ValueChanged<Category> onTap;

  const _CategoryGrid({
    required this.categories,
    required this.loading,
    required this.horizontalPadding,
    required this.contentWidth,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final columns = contentWidth >= 1000 ? 8 : (contentWidth >= 600 ? 6 : 4);
    final maxItems = columns * 2;
    final count = loading ? maxItems : (categories.length > maxItems ? maxItems : categories.length);
    final tileWidth = (contentWidth - (columns - 1) * AppSpacing.sm) / columns;

    return SliverPadding(
      padding: EdgeInsets.symmetric(horizontal: horizontalPadding),
      sliver: SliverGrid(
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: columns,
          crossAxisSpacing: AppSpacing.sm,
          mainAxisSpacing: AppSpacing.sm,
          mainAxisExtent: tileWidth + 48,
        ),
        delegate: SliverChildBuilderDelegate(
          (context, i) {
            if (loading) {
              return Column(
                children: [
                  AspectRatio(aspectRatio: 1, child: ShimmerBox(radius: AppRadius.md)),
                  const SizedBox(height: 6),
                  ShimmerBox(width: tileWidth * 0.7, height: 10),
                ],
              );
            }
            return CategoryCard(
              category: categories[i],
              index: i,
              onTap: () => onTap(categories[i]),
            );
          },
          childCount: count,
        ),
      ),
    );
  }
}

class _ProductRail extends StatelessWidget {
  final List<Product> products;
  final bool loading;
  final double horizontalPadding;
  final double cardWidth;

  const _ProductRail({
    required this.products,
    required this.loading,
    required this.horizontalPadding,
    required this.cardWidth,
  });

  @override
  Widget build(BuildContext context) {
    if (!loading && products.isEmpty) {
      return Padding(
        padding: EdgeInsets.symmetric(horizontal: horizontalPadding),
        child: Text(context.tr('no_products_found'), style: TextStyle(color: context.colors.onSurfaceVariant)),
      );
    }
    return SizedBox(
      height: 290,
      child: ListView.separated(
        padding: EdgeInsets.symmetric(horizontal: horizontalPadding),
        scrollDirection: Axis.horizontal,
        itemCount: loading ? 4 : products.length,
        separatorBuilder: (_, __) => const SizedBox(width: AppSpacing.md),
        itemBuilder: (context, i) => SizedBox(
          width: cardWidth,
          child: loading ? const ProductCardSkeleton() : ProductCard(product: products[i]),
        ),
      ),
    );
  }
}

class _TrustRow extends StatelessWidget {
  const _TrustRow();

  @override
  Widget build(BuildContext context) {
    final items = [
      (Icons.bolt_rounded, context.tr('trust_fast'), context.tr('trust_fast_sub')),
      (Icons.eco_rounded, context.tr('trust_fresh'), context.tr('trust_fresh_sub')),
      (Icons.verified_rounded, context.tr('trust_secure'), context.tr('trust_secure_sub')),
    ];
    return Container(
      padding: const EdgeInsets.symmetric(vertical: AppSpacing.lg, horizontal: AppSpacing.sm),
      decoration: BoxDecoration(
        color: context.colors.primaryContainer.fade(context.isDark ? 0.25 : 0.45),
        borderRadius: BorderRadius.circular(AppRadius.lg),
      ),
      child: Row(
        children: [
          for (final item in items)
            Expanded(
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(color: context.colors.surface, shape: BoxShape.circle),
                    child: Icon(item.$1, color: AppColors.primary),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Text(
                    item.$2,
                    textAlign: TextAlign.center,
                    style: context.textStyles.labelLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  Text(
                    item.$3,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    style: context.textStyles.labelSmall?.copyWith(color: context.colors.onSurfaceVariant),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
