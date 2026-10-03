import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/api_constants.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/product.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/delivery_slot_banner.dart';
import '../../widgets/error_state.dart';
import '../../widgets/price_text.dart';
import '../../widgets/product_card.dart';
import '../../widgets/section_header.dart';
import '../../widgets/shimmer_box.dart';
import '../cart/cart_screen.dart';

/// Product page. Pass a [product] (instant render, then enriched from the API)
/// or only a [productId] (e.g. from a banner deep link).
class ProductDetailScreen extends StatefulWidget {
  final Product? product;
  final int? productId;

  const ProductDetailScreen({super.key, this.product, this.productId})
      : assert(product != null || productId != null, 'Provide a product or a productId');

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  Product? _product;
  bool _loadingDetail = true;
  Object? _error;
  bool _descriptionExpanded = false;

  int get _id => widget.product?.id ?? widget.productId!;

  @override
  void initState() {
    super.initState();
    _product = widget.product;
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadDetail());
  }

  Future<void> _loadDetail() async {
    setState(() {
      _loadingDetail = true;
      _error = null;
    });
    try {
      final detail = await context.read<ProductProvider>().fetchProductDetail(_id);
      if (!mounted) return;
      setState(() {
        _product = _merge(detail, widget.product);
        _loadingDetail = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e;
        _loadingDetail = false;
      });
    }
  }

  /// The detail endpoint has no name_en/name_gu/slug - keep them from the list item.
  Product _merge(Product detail, Product? base) {
    if (base == null) return detail;
    return Product(
      id: detail.id,
      categoryId: base.categoryId,
      subCategoryId: base.subCategoryId,
      name: detail.name.isNotEmpty ? detail.name : base.name,
      nameEn: base.nameEn,
      nameGu: base.nameGu,
      slug: detail.slug.isNotEmpty ? detail.slug : base.slug,
      shortDescription: detail.shortDescription ?? base.shortDescription,
      description: detail.description ?? base.description,
      price: detail.price,
      strikePrice: detail.strikePrice,
      unit: detail.unit.isNotEmpty ? detail.unit : base.unit,
      stock: detail.stock,
      isInStock: detail.isInStock,
      isFeatured: base.isFeatured,
      mainImage: detail.mainImage ?? base.mainImage,
      galleryImages: detail.galleryImages.isNotEmpty ? detail.galleryImages : base.galleryImages,
      categoryName: detail.categoryName ?? base.categoryName,
      subCategoryName: detail.subCategoryName ?? base.subCategoryName,
      category: base.category,
      subCategory: base.subCategory,
      relatedProducts: detail.relatedProducts,
    );
  }

  @override
  Widget build(BuildContext context) {
    // Provider lookups happen unconditionally at the top of build
    // (never inside the LayoutBuilder callback).
    final isFav = context.select<WishlistProvider, bool>((w) => w.isFavorite(_id));
    final slotInfo = context.watch<ProductProvider>().deliverySlotInfo;
    final product = _product;

    if (product == null) {
      return Scaffold(
        appBar: AppBar(),
        body: _error != null
            ? ErrorState(error: _error, onRetry: _loadDetail)
            : const _DetailSkeleton(),
      );
    }

    return Scaffold(
      body: LayoutBuilder(
        builder: (context, constraints) {
          final headerHeight = constraints.maxWidth.clamp(280.0, 420.0).toDouble();
          final wide = constraints.maxWidth >= Breakpoints.medium;
          if (wide) {
            return Column(
              children: [
                AppBar(actions: _actions(product, isFav)),
                Expanded(
                  child: MaxWidthBox(
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Padding(
                            padding: const EdgeInsets.all(AppSpacing.xl),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(AppRadius.lg),
                              child: _Gallery(product: product, height: constraints.maxHeight * 0.7),
                            ),
                          ),
                        ),
                        Expanded(
                          child: ListView(
                            padding: const EdgeInsets.fromLTRB(0, AppSpacing.xl, AppSpacing.xl, AppSpacing.xxl),
                            children: _details(product, slotInfo),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          }
          return CustomScrollView(
            slivers: [
              SliverAppBar(
                pinned: true,
                expandedHeight: headerHeight,
                backgroundColor: context.theme.scaffoldBackgroundColor,
                leading: Padding(
                  padding: const EdgeInsets.all(6),
                  child: _CircleAction(
                    icon: Icons.arrow_back_rounded,
                    tooltip: MaterialLocalizations.of(context).backButtonTooltip,
                    onTap: () => Navigator.of(context).maybePop(),
                  ),
                ),
                actions: _actions(product, isFav, circled: true),
                flexibleSpace: FlexibleSpaceBar(
                  background: _Gallery(product: product, height: headerHeight + MediaQuery.paddingOf(context).top),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.xxl),
                sliver: SliverList(delegate: SliverChildListDelegate(_details(product, slotInfo))),
              ),
            ],
          );
        },
      ),
      bottomNavigationBar: _BottomBar(product: product),
    );
  }

  List<Widget> _actions(Product product, bool isFav, {bool circled = false}) {
    final share = product.slug.isNotEmpty
        ? () => AppActions.copyToClipboard(
              context,
              ApiConstants.productWebUrl(product.slug),
              message: context.tr('link_copied'),
            )
        : null;
    final favIcon = isFav ? Icons.favorite_rounded : Icons.favorite_border_rounded;
    final favColor = isFav ? AppColors.discount : null;

    if (circled) {
      return [
        if (share != null) _CircleAction(icon: Icons.share_rounded, tooltip: context.tr('share'), onTap: share),
        const SizedBox(width: AppSpacing.sm),
        _CircleAction(
          icon: favIcon,
          color: favColor,
          tooltip: context.tr('wishlist'),
          onTap: () => AppActions.toggleWishlist(context, product),
        ),
        const SizedBox(width: AppSpacing.md),
      ];
    }
    return [
      if (share != null) IconButton(icon: const Icon(Icons.share_rounded), tooltip: context.tr('share'), onPressed: share),
      IconButton(
        icon: Icon(favIcon, color: favColor),
        tooltip: context.tr('wishlist'),
        onPressed: () => AppActions.toggleWishlist(context, product),
      ),
      const SizedBox(width: AppSpacing.sm),
    ];
  }

  List<Widget> _details(Product product, Map<String, dynamic>? slotInfo) {
    final scheme = context.colors;
    final lang = context.langCode;
    final description = product.description ?? product.shortDescription;

    return [
      if (product.categoryName != null)
        Text(
          [product.categoryName, product.subCategoryName].whereType<String>().where((s) => s.isNotEmpty).join(' › '),
          style: context.textStyles.labelMedium?.copyWith(color: scheme.primary, fontWeight: FontWeight.w700),
        ),
      const SizedBox(height: AppSpacing.xs),
      Text(
        product.nameFor(lang),
        style: context.textStyles.headlineSmall?.copyWith(fontWeight: FontWeight.w800, height: 1.2),
      ),
      const SizedBox(height: AppSpacing.xs),
      Text(
        product.unit,
        style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
      ),
      const SizedBox(height: AppSpacing.lg),
      Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          PriceText(price: product.price, strikePrice: product.strikePrice, fontSize: 26),
          const SizedBox(width: AppSpacing.md),
          if (product.hasDiscount) DiscountBadge(percent: product.discountPercentage, large: true),
        ],
      ),
      if (product.hasDiscount) ...[
        const SizedBox(height: AppSpacing.xs),
        Text(
          context.tr('you_save', {'amount': Fmt.price(product.savings)}),
          style: context.textStyles.bodyMedium?.copyWith(color: AppColors.success, fontWeight: FontWeight.w700),
        ),
      ],
      Text(context.tr('inclusive_taxes'), style: context.textStyles.labelSmall?.copyWith(color: scheme.onSurfaceVariant)),
      const SizedBox(height: AppSpacing.md),
      _StockPill(product: product),
      const SizedBox(height: AppSpacing.xl),
      DeliverySlotBanner(slotInfo: slotInfo, compact: true),
      const SizedBox(height: AppSpacing.xl),
      const _Highlights(),
      if (description != null || _loadingDetail) ...[
        SectionHeader(title: context.tr('product_details')),
        if (_loadingDetail && description == null)
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ShimmerBox(height: 12, width: double.infinity),
              SizedBox(height: 8),
              ShimmerBox(height: 12, width: 220),
            ],
          )
        else if (description != null)
          AnimatedSize(
            duration: const Duration(milliseconds: 250),
            alignment: Alignment.topCenter,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _stripHtml(description),
                  maxLines: _descriptionExpanded ? null : 4,
                  overflow: _descriptionExpanded ? TextOverflow.visible : TextOverflow.ellipsis,
                  style: context.textStyles.bodyMedium?.copyWith(height: 1.6, color: scheme.onSurfaceVariant),
                ),
                if (_stripHtml(description).length > 180)
                  TextButton(
                    style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: const Size(0, 36)),
                    onPressed: () => setState(() => _descriptionExpanded = !_descriptionExpanded),
                    child: Text(context.tr(_descriptionExpanded ? 'read_less' : 'read_more')),
                  ),
              ],
            ),
          ),
      ],
      if (product.relatedProducts.isNotEmpty) ...[
        SectionHeader(title: context.tr('you_may_also_like')),
        SizedBox(
          height: 290,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: product.relatedProducts.length,
            separatorBuilder: (_, __) => const SizedBox(width: AppSpacing.md),
            itemBuilder: (_, i) => SizedBox(width: 168, child: ProductCard(product: product.relatedProducts[i])),
          ),
        ),
      ],
    ];
  }

  static String _stripHtml(String html) {
    return html
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'</p>', caseSensitive: false), '\n\n')
        .replaceAll(RegExp(r'<[^>]*>'), '')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll(RegExp(r'\n{3,}'), '\n\n')
        .trim();
  }
}

class _Gallery extends StatefulWidget {
  final Product product;
  final double height;

  const _Gallery({required this.product, required this.height});

  @override
  State<_Gallery> createState() => _GalleryState();
}

class _GalleryState extends State<_Gallery> {
  final _controller = PageController();
  int _index = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _openViewer(List<String> images, int index) {
    Navigator.of(context).push(
      MaterialPageRoute(fullscreenDialog: true, builder: (_) => _ImageViewer(images: images, initialIndex: index)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final images = widget.product.allImages;
    final scheme = context.colors;
    return Container(
      height: widget.height,
      color: context.isDark ? scheme.surfaceContainerLow : Colors.white,
      child: Stack(
        children: [
          if (images.isEmpty)
            const Positioned.fill(child: AppNetworkImage(url: null, placeholderIcon: Icons.image_outlined))
          else
            PageView.builder(
              controller: _controller,
              itemCount: images.length,
              onPageChanged: (i) => setState(() => _index = i),
              itemBuilder: (_, i) => GestureDetector(
                onTap: () => _openViewer(images, i),
                child: AppNetworkImage(url: images[i], fit: BoxFit.cover),
              ),
            ),
          if (widget.product.hasDiscount)
            Positioned(
              left: AppSpacing.lg,
              bottom: AppSpacing.lg,
              child: DiscountBadge(percent: widget.product.discountPercentage, large: true),
            ),
          if (images.length > 1)
            Positioned(
              bottom: AppSpacing.lg,
              left: 0,
              right: 0,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(images.length, (i) {
                  final active = i == _index;
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 220),
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    width: active ? 20 : 7,
                    height: 7,
                    decoration: BoxDecoration(
                      color: active ? scheme.primary : Colors.white.fade(0.8),
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                      boxShadow: [BoxShadow(color: Colors.black.fade(0.15), blurRadius: 4)],
                    ),
                  );
                }),
              ),
            ),
        ],
      ),
    );
  }
}

class _ImageViewer extends StatelessWidget {
  final List<String> images;
  final int initialIndex;

  const _ImageViewer({required this.images, required this.initialIndex});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: PageView.builder(
        controller: PageController(initialPage: initialIndex),
        itemCount: images.length,
        itemBuilder: (_, i) => InteractiveViewer(
          minScale: 1,
          maxScale: 4,
          child: Center(child: AppNetworkImage(url: images[i], fit: BoxFit.contain)),
        ),
      ),
    );
  }
}

class _CircleAction extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final String? tooltip;
  final Color? color;

  const _CircleAction({required this.icon, required this.onTap, this.tooltip, this.color});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Tooltip(
      message: tooltip ?? '',
      child: Material(
        color: scheme.surface.fade(0.92),
        shape: const CircleBorder(),
        elevation: 1,
        shadowColor: Colors.black.fade(0.2),
        child: InkWell(
          customBorder: const CircleBorder(),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(9),
            child: Icon(icon, size: 22, color: color ?? scheme.onSurface),
          ),
        ),
      ),
    );
  }
}

class _StockPill extends StatelessWidget {
  final Product product;
  const _StockPill({required this.product});

  @override
  Widget build(BuildContext context) {
    final Color color;
    final String label;
    final IconData icon;
    if (!product.isInStock) {
      color = AppColors.error;
      label = context.tr('out_of_stock');
      icon = Icons.remove_shopping_cart_rounded;
    } else if (product.isLowStock) {
      color = AppColors.warning;
      label = context.tr('only_left', {'count': '${product.stock}'});
      icon = Icons.local_fire_department_rounded;
    } else {
      color = AppColors.success;
      label = context.tr('in_stock');
      icon = Icons.check_circle_rounded;
    }
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: color.fade(0.12),
          borderRadius: BorderRadius.circular(AppRadius.pill),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 6),
            Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 12.5)),
          ],
        ),
      ),
    );
  }
}

class _Highlights extends StatelessWidget {
  const _Highlights();

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final items = [
      (Icons.eco_rounded, context.tr('farm_fresh')),
      (Icons.verified_rounded, context.tr('quality_checked')),
      (Icons.payments_rounded, context.tr('cod_available')),
    ];
    return Row(
      children: [
        for (final item in items)
          Expanded(
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 4),
              padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: AppSpacing.xs),
              decoration: BoxDecoration(
                color: scheme.surfaceContainerLow,
                borderRadius: BorderRadius.circular(AppRadius.sm),
                border: Border.all(color: scheme.outlineVariant.fade(0.5)),
              ),
              child: Column(
                children: [
                  Icon(item.$1, color: scheme.primary, size: 22),
                  const SizedBox(height: 6),
                  Text(
                    item.$2,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    style: context.textStyles.labelSmall?.copyWith(fontWeight: FontWeight.w700),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _BottomBar extends StatelessWidget {
  final Product product;
  const _BottomBar({required this.product});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final qty = cart.getProductQuantity(product.id);
    final scheme = context.colors;

    return Container(
      decoration: BoxDecoration(
        color: scheme.surface,
        border: Border(top: BorderSide(color: scheme.outlineVariant.fade(0.6))),
        boxShadow: [BoxShadow(color: Colors.black.fade(0.05), blurRadius: 16, offset: const Offset(0, -4))],
      ),
      child: SafeArea(
        top: false,
        child: MaxWidthBox(
          maxWidth: 900,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        qty > 0 ? context.tr('in_your_cart', {'count': '$qty'}) : product.unit,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: context.textStyles.labelMedium?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                      Text(
                        Fmt.price(qty > 0 ? product.price * qty : product.price),
                        style: context.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                      ),
                    ],
                  ),
                ),
                if (qty > 0) ...[
                  CartControl(product: product, large: true),
                  const SizedBox(width: AppSpacing.sm),
                  FilledButton.icon(
                    onPressed: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CartScreen(standalone: true)),
                    ),
                    style: FilledButton.styleFrom(minimumSize: const Size(0, 48), padding: const EdgeInsets.symmetric(horizontal: 16)),
                    icon: const Icon(Icons.shopping_bag_rounded, size: 20),
                    label: Text(context.tr('view_cart')),
                  ),
                ] else
                  SizedBox(
                    height: 52,
                    child: FilledButton.icon(
                      onPressed: product.isInStock && !cart.isBusy(product.id)
                          ? () => AppActions.addToCart(context, product, showFeedback: true)
                          : null,
                      icon: cart.isBusy(product.id)
                          ? SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: scheme.onPrimary))
                          : const Icon(Icons.add_shopping_cart_rounded),
                      label: Text(context.tr(product.isInStock ? 'add_to_cart' : 'out_of_stock')),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _DetailSkeleton extends StatelessWidget {
  const _DetailSkeleton();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      children: const [
        ShimmerBox(height: 300, radius: AppRadius.lg),
        SizedBox(height: AppSpacing.xl),
        ShimmerBox(height: 24, width: 240),
        SizedBox(height: AppSpacing.sm),
        ShimmerBox(height: 16, width: 120),
        SizedBox(height: AppSpacing.xl),
        ShimmerBox(height: 32, width: 160),
        SizedBox(height: AppSpacing.xl),
        ShimmerBox(height: 72, radius: AppRadius.md),
      ],
    );
  }
}
