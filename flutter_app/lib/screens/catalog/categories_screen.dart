import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../models/category.dart';
import '../../providers/product_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/category_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/shimmer_box.dart';
import 'product_list_screen.dart';
import 'subcategories_screen.dart';

class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({super.key});

  @override
  State<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<ProductProvider>();
      if (provider.categories.isEmpty && !provider.isLoadingCategories) provider.fetchCategories();
    });
  }

  void _open(Category category) {
    final Widget screen = category.subCategories.isEmpty
        ? ProductListScreen(title: category.nameFor(context.langCode), categoryId: category.id)
        : SubcategoriesScreen(category: category);
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ProductProvider>();
    final categories = provider.categories;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('categories')),
        actions: [
          IconButton(
            tooltip: context.tr('search'),
            icon: const Icon(Icons.search_rounded),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => ProductListScreen(title: context.tr('search'), startWithSearch: true)),
            ),
          ),
        ],
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final hPad = Responsive.centeredPadding(width);
          final content = width - hPad * 2;
          final columns = content >= 1000 ? 3 : (content >= 600 ? 2 : 1);

          Widget body;
          if (categories.isEmpty && (provider.isLoadingCategories || provider.isLoadingHome)) {
            body = ListSkeleton(itemCount: 6, itemHeight: 104, padding: EdgeInsets.symmetric(horizontal: hPad, vertical: AppSpacing.lg));
          } else if (categories.isEmpty && provider.categoriesError != null) {
            body = ErrorState(error: provider.categoriesError, onRetry: provider.fetchCategories);
          } else if (categories.isEmpty) {
            body = EmptyState(
              icon: Icons.category_outlined,
              title: context.tr('no_categories'),
              actionLabel: context.tr('retry'),
              actionIcon: Icons.refresh_rounded,
              onAction: provider.fetchCategories,
            );
          } else {
            body = GridView.builder(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.xxl),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: columns,
                mainAxisSpacing: AppSpacing.md,
                crossAxisSpacing: AppSpacing.md,
                mainAxisExtent: 112,
              ),
              itemCount: categories.length,
              itemBuilder: (context, i) => _CategoryTile(
                category: categories[i],
                index: i,
                onTap: () => _open(categories[i]),
              ),
            );
          }
          return RefreshIndicator(onRefresh: provider.fetchCategories, child: body);
        },
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  final Category category;
  final int index;
  final VoidCallback onTap;

  const _CategoryTile({required this.category, required this.index, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final lang = context.langCode;
    final subs = category.subCategories;
    return AppCard(
      onTap: onTap,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Row(
        children: [
          Container(
            width: 84,
            height: 84,
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: categoryTint(context, index),
              borderRadius: BorderRadius.circular(AppRadius.md),
            ),
            child: AppNetworkImage(
              url: category.image,
              borderRadius: BorderRadius.circular(AppRadius.sm),
              placeholderIcon: categoryIcon(category.icon),
            ),
          ),
          const SizedBox(width: AppSpacing.lg),
          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  category.nameFor(lang),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 4),
                Text(
                  subs.isEmpty
                      ? (category.description ?? context.tr('browse_products'))
                      : subs.take(3).map((s) => s.nameFor(lang)).join(' • '),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant, height: 1.35),
                ),
                if (subs.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Text(
                    context.tr('subcategories_count', {'count': '${subs.length}'}),
                    style: context.textStyles.labelSmall?.copyWith(color: scheme.primary, fontWeight: FontWeight.w700),
                  ),
                ],
              ],
            ),
          ),
          Icon(Icons.chevron_right_rounded, color: scheme.onSurfaceVariant),
        ],
      ),
    );
  }
}
