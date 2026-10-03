import 'package:flutter/material.dart';
import '../l10n/app_localizations.dart';
import '../services/api_service.dart';
import 'empty_state.dart';

/// Error / offline state with a retry button.
class ErrorState extends StatelessWidget {
  final Object? error;
  final String? message;
  final VoidCallback? onRetry;
  final bool forceOffline;
  final bool scrollable;

  const ErrorState({
    super.key,
    this.error,
    this.message,
    this.onRetry,
    this.forceOffline = false,
    this.scrollable = true,
  });

  bool get _offline => forceOffline || (error is ApiException && (error as ApiException).isNetworkError);

  @override
  Widget build(BuildContext context) {
    final offline = _offline;
    String? detail = message;
    if (detail == null && error != null && !offline) {
      detail = error is ApiException ? (error as ApiException).message : null;
    }
    return EmptyState(
      icon: offline ? Icons.wifi_off_rounded : Icons.cloud_off_rounded,
      title: context.tr(offline ? 'no_internet' : 'something_went_wrong'),
      message: detail ?? context.tr(offline ? 'no_internet_msg' : 'something_went_wrong_msg'),
      actionLabel: onRetry != null ? context.tr('retry') : null,
      actionIcon: Icons.refresh_rounded,
      onAction: onRetry,
      scrollable: scrollable,
    );
  }
}
