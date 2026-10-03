import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../models/category.dart';
import '../../models/product.dart';
import '../../providers/product_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_dropdown.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/product_card.dart';
import '../../widgets/shimmer_box.dart';

/// Product listing with search, sort/filter sheet, sub-category chips and
/// infinite scrolling. Responsive grid: 2 cols phone, 3-4 tablet, 5 desktop.
class ProductListScreen extends StatefulWidget {
  final String? title;
  final int? categoryId;
  final int? subCategoryId;
  final String? initialSort;
  final String? initialQuery;
  final bool startWithSearch;
  final List<SubCategory> subCategories;

  const ProductListScreen({
    super.key,
    this.title,
    this.categoryId,
    this.subCategoryId,
    this.initialSort,
    this.initialQuery,
    this.startWithSearch = false,
    this.subCategories = const [],
  });

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  static const List<String> _sortOptions = ['featured', 'newest', 'price_asc', 'price_desc'];

  final _scrollController = ScrollController();
  final _searchController = TextEditingController();
  Timer? _debounce;

  List<Product> _products = [];
  int _page = 1;
  int _lastPage = 1;
  int _total = 0;
  bool _loading = true;
  bool _loadingMore = false;
  Object? _error;
  late String _sort;
  late bool _searchMode;
  bool _inStockOnly = false;
  int? _subCategoryId;
  int _requestId = 0;

  @override
  void initState() {
    super.initState();
    _sort = widget.initialSort ?? 'featured';
    _searchMode = widget.startWithSearch;
    _subCategoryId = widget.subCategoryId;
    _searchController.text = widget.initialQuery ?? '';
    _scrollController.addListener(_onScroll);
    if (_searchMode && _searchController.text.isEmpty) {
      _loading = false;
    } else {
      _load();
    }
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  String get _query => _searchController.text.trim();

  bool get _showSearchHome => _searchMode && _query.isEmpty && widget.categoryId == null && _subCategoryId == null;

  Future<void> _load() async {
    final requestId = ++_requestId;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final page = await context.read<ProductProvider>().fetchProductPage(
            categoryId: widget.categoryId,
            subCategoryId: _subCategoryId,
            search: _query,
            sort: _sort,
          );
      if (!mounted || requestId != _requestId) return;
      setState(() {
        _products = page.items;
        _page = page.currentPage;
        _lastPage = page.lastPage;
        _total = page.total;
        _loading = false;
      });
    } catch (e) {
      if (!mounted || requestId != _requestId) return;
      setState(() {
        _error = e;
        _loading = false;
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    final requestId = _requestId;
    try {
      final page = await context.read<ProductProvider>().fetchProductPage(
            categoryId: widget.categoryId,
            subCategoryId: _subCategoryId,
            search: _query,
            sort: _sort,
            page: _page + 1,
          );
      if (!mounted || requestId != _requestId) return;
      setState(() {
        _products = [..._products, ...page.items];
        _page = page.currentPage;
        _lastPage = page.lastPage;
      });
    } catch (_) {
      // Silent - user can scroll again to retry.
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  void _onScroll() {
    if (!_scrollController.hasClients) return;
    final pos = _scrollController.position;
    if (pos.pixels >= pos.maxScrollExtent - 600) _loadMore();
  }

  void _onSearchChanged(String value) {
    setState(() {});
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 450), () {
      if (!mounted) return;
      if (_showSearchHome) {
        setState(() {
          _products = [];
          _loading = false;
          _error = null;
        });
      } else {
        _load();
      }
    });
  }

  void _submitSearch(String value) {
    _debounce?.cancel();
    context.read<ProductProvider>().addRecentSearch(value);
    if (!_showSearchHome) _load();
  }

  void _applyQuery(String query) {
    _searchController.text = query;
    _searchController.selection = TextSelection.collapsed(offset: query.length);
    _submitSearch(query);
    setState(() {});
  }

  List<Product> get _visibleProducts =>
      _inStockOnly ? _products.where((p) => p.isInStock).toList() : _products;

  Future<void> _openFilters() async {
    String sort = _sort;
    bool inStock = _inStockOnly;
    final applied = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheet) => SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(AppSpacing.xl, 0, AppSpacing.xl, AppSpacing.lg),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(ctx.tr('sort_filter'),
                          style: ctx.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
                    ),
                    TextButton(
                      onPressed: () => setSheet(() {
                        sort = 'featured';
                        inStock = false;
                      }),
                      child: Text(ctx.tr('reset')),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                AppDropdownField<String>(
                  value: sort,
                  label: ctx.tr('sort_by'),
                  items: _sortItems(ctx),
                  onChanged: (v) => setSheet(() => sort = v),
                ),
                const Divider(height: AppSpacing.xl),
                SwitchListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm),
                  secondary: const Icon(Icons.inventory_2_outlined),
                  title: Text(ctx.tr('in_stock_only')),
                  value: inStock,
                  onChanged: (v) => setSheet(() => inStock = v),
                ),
                const SizedBox(height: AppSpacing.lg),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: () => Navigator.of(ctx).pop(true),
                    child: Text(ctx.tr('apply')),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
    if (applied != true || !mounted) return;
    final sortChanged = sort != _sort;
    setState(() {
      _sort = sort;
      _inStockOnly = inStock;
    });
    if (sortChanged) _load();
  }

  List<AppDropdownItem<String>> _sortItems(BuildContext ctx) => [
        for (final option in _sortOptions)
          AppDropdownItem<String>(value: option, icon: _sortIcon(option), label: ctx.tr('sort_$option')),
      ];

  void _changeSort(String sort) {
    if (sort == _sort) return;
    setState(() => _sort = sort);
    _load();
  }

  IconData _sortIcon(String option) {
    switch (option) {
      case 'newest':
        return Icons.fiber_new_rounded;
      case 'price_asc':
        return Icons.trending_up_rounded;
      case 'price_desc':
        return Icons.trending_down_rounded;
      default:
        return Icons.star_rounded;
    }
  }

  PreferredSizeWidget _buildAppBar() {
    if (_searchMode) {
      return AppBar(
        titleSpacing: 0,
        title: TextField(
          controller: _searchController,
          autofocus: _query.isEmpty,
          textInputAction: TextInputAction.search,
          onChanged: _onSearchChanged,
          onSubmitted: _submitSearch,
          decoration: InputDecoration(
            hintText: context.tr('search_placeholder'),
            prefixIcon: const Icon(Icons.search_rounded),
            suffixIcon: _query.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.close_rounded),
                    tooltip: context.tr('clear'),
                    onPressed: () {
                      _searchController.clear();
                      _onSearchChanged('');
                    },
                  )
                : null,
            contentPadding: const EdgeInsets.symmetric(vertical: 12),
          ),
        ),
        actions: [
          IconButton(
            tooltip: context.tr('sort_filter'),
            icon: const Icon(Icons.tune_rounded),
            onPressed: _openFilters,
          ),
          const SizedBox(width: AppSpacing.xs),
        ],
      );
    }
    return AppBar(
      title: Text(widget.title ?? context.tr('products')),
      actions: [
        IconButton(
          tooltip: context.tr('search'),
          icon: const Icon(Icons.search_rounded),
          onPressed: () => setState(() => _searchMode = true),
        ),
        IconButton(
          tooltip: context.tr('sort_filter'),
          icon: Badge(
            isLabelVisible: _sort != 'featured' || _inStockOnly,
            smallSize: 8,
            child: const Icon(Icons.tune_rounded),
          ),
          onPressed: _openFilters,
        ),
        const SizedBox(width: AppSpacing.xs),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: _buildAppBar(),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final hPad = Responsive.centeredPadding(width);
          final columns = Responsive.gridColumns(width - hPad * 2);

          return Column(
            children: [
              if (widget.subCategories.isNotEmpty) _buildSubCategoryChips(hPad),
              if (!_showSearchHome) _buildToolbar(hPad),
              Expanded(child: _buildBody(hPad, columns)),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSubCategoryChips(double hPad) {
    final lang = context.langCode;
    final chips = <Widget>[
      ChoiceChip(
        label: Text(context.tr('all')),
        selected: _subCategoryId == null,
        onSelected: (_) {
          setState(() => _subCategoryId = null);
          _load();
        },
      ),
      for (final sub in widget.subCategories)
        ChoiceChip(
          label: Text(sub.nameFor(lang)),
          selected: _subCategoryId == sub.id,
          onSelected: (_) {
            setState(() => _subCategoryId = sub.id);
            _load();
          },
        ),
    ];
    return SizedBox(
      height: 52,
      child: ListView.separated(
        padding: EdgeInsets.symmetric(horizontal: hPad, vertical: AppSpacing.sm),
        scrollDirection: Axis.horizontal,
        itemCount: chips.length,
        separatorBuilder: (_, __) => const SizedBox(width: AppSpacing.sm),
        itemBuilder: (_, i) => chips[i],
      ),
    );
  }

  Widget _buildToolbar(double hPad) {
    final scheme = context.colors;
    final count = _inStockOnly ? _visibleProducts.length : _total;
    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, AppSpacing.xs, hPad, AppSpacing.sm),
      child: Row(
        children: [
          Expanded(
            child: Text(
              _loading ? context.tr('loading') : context.tr('items_found', {'count': '$count'}),
              style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant, fontWeight: FontWeight.w600),
            ),
          ),
          AppSelectChip<String>(
            value: _sort,
            leadingIcon: Icons.swap_vert_rounded,
            tooltip: context.tr('sort_by'),
            items: _sortItems(context),
            onChanged: _changeSort,
          ),
          if (_inStockOnly) ...[
            const SizedBox(width: AppSpacing.sm),
            InputChip(
              label: Text(context.tr('in_stock')),
              onDeleted: () => setState(() => _inStockOnly = false),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildBody(double hPad, int columns) {
    if (_showSearchHome) return _SearchSuggestions(onQuery: _applyQuery, horizontalPadding: hPad);

    if (_loading) {
      return ProductGridSkeleton(
        columns: columns,
        itemCount: columns * 3,
        padding: EdgeInsets.fromLTRB(hPad, AppSpacing.xs, hPad, AppSpacing.lg),
      );
    }
    if (_error != null) return ErrorState(error: _error, onRetry: _load);

    final products = _visibleProducts;
    if (products.isEmpty) {
      return EmptyState(
        icon: Icons.search_off_rounded,
        title: context.tr('no_products_found'),
        message: context.tr(_query.isNotEmpty ? 'no_products_search_msg' : 'no_products_msg'),
        actionLabel: _inStockOnly ? context.tr('show_all_products') : null,
        onAction: _inStockOnly ? () => setState(() => _inStockOnly = false) : null,
      );
    }

    final extra = _loadingMore ? columns : 0;
    return RefreshIndicator(
      onRefresh: _load,
      child: GridView.builder(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: EdgeInsets.fromLTRB(hPad, AppSpacing.xs, hPad, AppSpacing.xxl),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: columns,
          mainAxisSpacing: AppSpacing.md,
          crossAxisSpacing: AppSpacing.md,
          mainAxisExtent: 290,
        ),
        itemCount: products.length + extra,
        itemBuilder: (context, i) {
          if (i >= products.length) return const ProductCardSkeleton();
          return ProductCard(product: products[i]);
        },
      ),
    );
  }
}

class _SearchSuggestions extends StatelessWidget {
  final ValueChanged<String> onQuery;
  final double horizontalPadding;

  const _SearchSuggestions({required this.onQuery, required this.horizontalPadding});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ProductProvider>();
    final recent = provider.recentSearches;
    final lang = context.langCode;
    final scheme = context.colors;

    return ListView(
      padding: EdgeInsets.fromLTRB(horizontalPadding, AppSpacing.lg, horizontalPadding, AppSpacing.xl),
      children: [
        if (recent.isNotEmpty) ...[
          Row(
            children: [
              Expanded(
                child: Text(context.tr('recent_searches'),
                    style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800)),
              ),
              TextButton(onPressed: provider.clearRecentSearches, child: Text(context.tr('clear'))),
            ],
          ),
          Wrap(
            spacing: AppSpacing.sm,
            runSpacing: AppSpacing.sm,
            children: [
              for (final q in recent)
                ActionChip(
                  avatar: Icon(Icons.history_rounded, size: 18, color: scheme.onSurfaceVariant),
                  label: Text(q),
                  onPressed: () => onQuery(q),
                ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),
        ],
        if (provider.categories.isNotEmpty) ...[
          Text(context.tr('popular_categories'),
              style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800)),
          const SizedBox(height: AppSpacing.sm),
          Wrap(
            spacing: AppSpacing.sm,
            runSpacing: AppSpacing.sm,
            children: [
              for (final c in provider.categories)
                ActionChip(
                  avatar: Icon(Icons.trending_up_rounded, size: 18, color: scheme.primary),
                  label: Text(c.nameFor(lang)),
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => ProductListScreen(
                        title: c.nameFor(lang),
                        categoryId: c.id,
                        subCategories: c.subCategories,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
        const SizedBox(height: AppSpacing.xxl),
        Center(
          child: Column(
            children: [
              Icon(Icons.manage_search_rounded, size: 64, color: scheme.outlineVariant),
              const SizedBox(height: AppSpacing.sm),
              Text(
                context.tr('search_hint'),
                textAlign: TextAlign.center,
                style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
