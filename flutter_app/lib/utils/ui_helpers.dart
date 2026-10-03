import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../providers/auth_provider.dart';
import '../screens/auth/phone_login_screen.dart';
import '../services/api_service.dart';

enum SnackType { info, success, error }

/// Human readable message for any error thrown by the API layer.
String errorMessage(Object error) {
  if (error is ApiException) return error.message;
  return error.toString().replaceFirst('Exception: ', '');
}

bool isNetworkError(Object? error) => error is ApiException && error.isNetworkError;

void showAppSnack(
  BuildContext context,
  String message, {
  SnackType type = SnackType.info,
  SnackBarAction? action,
  Duration? duration,
}) {
  final messenger = ScaffoldMessenger.maybeOf(context);
  if (messenger == null) return;
  IconData icon = Icons.info_rounded;
  Color iconColor = const Color(0xFF93C5FD);
  switch (type) {
    case SnackType.success:
      icon = Icons.check_circle_rounded;
      iconColor = AppColors.primaryOnDark;
      break;
    case SnackType.error:
      icon = Icons.error_rounded;
      iconColor = const Color(0xFFFCA5A5);
      break;
    case SnackType.info:
      icon = Icons.info_rounded;
      iconColor = const Color(0xFF93C5FD);
      break;
  }
  messenger
    ..hideCurrentSnackBar()
    ..showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(icon, color: iconColor, size: 20),
            const SizedBox(width: 12),
            Expanded(child: Text(message)),
          ],
        ),
        action: action,
        duration: duration ?? Duration(milliseconds: type == SnackType.error ? 3500 : 2200),
      ),
    );
}

/// Ensures the user is logged in, opening the login flow if needed.
/// Returns true when the user is (now) authenticated.
Future<bool> ensureLoggedIn(BuildContext context) async {
  final auth = context.read<AuthProvider>();
  if (auth.isAuthenticated) return true;
  final ok = await Navigator.of(context).push<bool>(
    MaterialPageRoute(builder: (_) => const PhoneLoginScreen()),
  );
  if (!context.mounted) return false;
  return ok == true && context.read<AuthProvider>().isAuthenticated;
}

/// Material 3 confirmation dialog. Returns true when confirmed.
Future<bool> confirmDialog(
  BuildContext context, {
  required String title,
  required String message,
  String? confirmLabel,
  bool destructive = false,
  IconData? icon,
}) async {
  final result = await showDialog<bool>(
    context: context,
    builder: (ctx) {
      final scheme = Theme.of(ctx).colorScheme;
      return AlertDialog(
        icon: icon != null ? Icon(icon, color: destructive ? scheme.error : scheme.primary) : null,
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: Text(ctx.tr('cancel')),
          ),
          FilledButton(
            style: destructive
                ? FilledButton.styleFrom(backgroundColor: scheme.error, foregroundColor: scheme.onError, minimumSize: const Size(88, 44))
                : FilledButton.styleFrom(minimumSize: const Size(88, 44)),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: Text(confirmLabel ?? ctx.tr('confirm')),
          ),
        ],
      );
    },
  );
  return result == true;
}
