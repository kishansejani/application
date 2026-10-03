import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../utils/image_url.dart';
import 'shimmer_box.dart';

/// Cached network image with shimmer placeholder and a graceful fallback.
class AppNetworkImage extends StatelessWidget {
  final String? url;
  final BoxFit fit;
  final double? width;
  final double? height;
  final BorderRadius? borderRadius;
  final IconData placeholderIcon;
  final Color? backgroundColor;

  const AppNetworkImage({
    super.key,
    required this.url,
    this.fit = BoxFit.cover,
    this.width,
    this.height,
    this.borderRadius,
    this.placeholderIcon = Icons.shopping_basket_outlined,
    this.backgroundColor,
  });

  @override
  Widget build(BuildContext context) {
    final resolved = ImageUrl.resolve(url);
    Widget child;
    if (resolved == null) {
      child = _fallback(context);
    } else {
      child = CachedNetworkImage(
        imageUrl: resolved,
        fit: fit,
        width: width,
        height: height,
        fadeInDuration: const Duration(milliseconds: 250),
        placeholder: (context, _) => ShimmerBox(width: width, height: height, radius: 0),
        errorWidget: (context, _, __) => _fallback(context),
      );
    }
    if (backgroundColor != null) {
      child = ColoredBox(color: backgroundColor!, child: child);
    }
    if (borderRadius != null) {
      child = ClipRRect(borderRadius: borderRadius!, child: child);
    }
    return child;
  }

  Widget _fallback(BuildContext context) {
    final scheme = context.colors;
    return Container(
      width: width,
      height: height,
      color: scheme.surfaceContainerHighest.fade(0.6),
      alignment: Alignment.center,
      child: Icon(placeholderIcon, color: scheme.onSurfaceVariant.fade(0.5), size: 28),
    );
  }
}
