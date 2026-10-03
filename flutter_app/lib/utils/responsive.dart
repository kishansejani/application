import 'dart:math' as math;
import 'package:flutter/material.dart';

/// Material 3 window size classes.
class Breakpoints {
  Breakpoints._();
  static const double compact = 600;
  static const double medium = 840;
  static const double expanded = 1200;

  /// Max width for main content on large screens.
  static const double maxContentWidth = 1200;

  /// Max width for forms / reading content.
  static const double maxFormWidth = 640;
}

class Responsive {
  Responsive._();

  static double width(BuildContext context) => MediaQuery.sizeOf(context).width;

  static bool isCompact(BuildContext context) => width(context) < Breakpoints.compact;

  static bool isTablet(BuildContext context) => width(context) >= Breakpoints.compact;

  /// Switch from bottom NavigationBar to NavigationRail.
  static bool useRail(BuildContext context) => width(context) >= Breakpoints.medium;

  /// Product grid columns for an available width: 2 phone, 3-4 tablet, 5 desktop.
  static int gridColumns(double availableWidth) {
    if (availableWidth >= 1100) return 5;
    if (availableWidth >= 840) return 4;
    if (availableWidth >= 560) return 3;
    return 2;
  }

  /// Horizontal padding that keeps content centred within [Breakpoints.maxContentWidth].
  static double centeredPadding(double availableWidth, {double maxWidth = Breakpoints.maxContentWidth, double min = 16}) {
    return math.max(min, (availableWidth - maxWidth) / 2);
  }
}

/// Centres [child] and limits its width on tablets / desktops.
class MaxWidthBox extends StatelessWidget {
  final Widget child;
  final double maxWidth;
  final AlignmentGeometry alignment;

  const MaxWidthBox({
    super.key,
    required this.child,
    this.maxWidth = Breakpoints.maxContentWidth,
    this.alignment = Alignment.topCenter,
  });

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: alignment,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: child,
      ),
    );
  }
}
