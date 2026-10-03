import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';

enum AuthMode { login, register }

/// Login | Register segmented switch shown at the top of both auth screens.
class AuthModeSwitch extends StatelessWidget {
  final AuthMode mode;
  final ValueChanged<AuthMode> onChanged;

  const AuthModeSwitch({super.key, required this.mode, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: SegmentedButton<AuthMode>(
        segments: [
          ButtonSegment<AuthMode>(
            value: AuthMode.login,
            icon: const Icon(Icons.login_rounded),
            label: Text(context.tr('login')),
          ),
          ButtonSegment<AuthMode>(
            value: AuthMode.register,
            icon: const Icon(Icons.person_add_alt_1_rounded),
            label: Text(context.tr('register')),
          ),
        ],
        selected: {mode},
        showSelectedIcon: false,
        onSelectionChanged: (s) {
          if (s.isNotEmpty && s.first != mode) onChanged(s.first);
        },
      ),
    );
  }
}

/// 10-digit Indian mobile number field with +91 prefix and a valid tick.
class PhoneField extends StatelessWidget {
  static final RegExp pattern = RegExp(r'^[0-9]{10}$');

  final TextEditingController controller;
  final bool autofocus;
  final TextInputAction textInputAction;
  final ValueChanged<String>? onSubmitted;

  const PhoneField({
    super.key,
    required this.controller,
    this.autofocus = false,
    this.textInputAction = TextInputAction.done,
    this.onSubmitted,
  });

  static bool isValid(String value) => pattern.hasMatch(value.trim());

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return ValueListenableBuilder<TextEditingValue>(
      valueListenable: controller,
      builder: (context, value, _) {
        final valid = isValid(value.text);
        return TextFormField(
          controller: controller,
          autofocus: autofocus,
          keyboardType: TextInputType.phone,
          textInputAction: textInputAction,
          autofillHints: const [AutofillHints.telephoneNumberNational],
          style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w700, letterSpacing: 1.5),
          inputFormatters: [
            FilteringTextInputFormatter.digitsOnly,
            LengthLimitingTextInputFormatter(10),
          ],
          decoration: InputDecoration(
            labelText: context.tr('mobile_number'),
            hintText: '98765 43210',
            prefixIcon: Padding(
              padding: const EdgeInsets.only(left: AppSpacing.lg, right: AppSpacing.sm),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('🇮🇳', style: TextStyle(fontSize: 18)),
                  const SizedBox(width: 6),
                  Text('+91', style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                  const SizedBox(width: AppSpacing.sm),
                  Container(width: 1, height: 24, color: scheme.outlineVariant),
                ],
              ),
            ),
            suffixIcon: valid ? Icon(Icons.check_circle_rounded, color: scheme.primary) : null,
          ),
          validator: (v) => isValid(v ?? '') ? null : context.tr('invalid_phone'),
          onFieldSubmitted: onSubmitted,
        );
      },
    );
  }
}

/// Tinted info banner (e.g. "This number is not registered yet").
class AuthNotice extends StatelessWidget {
  final String message;
  final IconData icon;

  const AuthNotice({super.key, required this.message, this.icon = Icons.info_outline_rounded});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: scheme.tertiaryContainer.fade(0.6),
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: Row(
        children: [
          Icon(icon, size: 20, color: scheme.onTertiaryContainer),
          const SizedBox(width: AppSpacing.sm),
          Expanded(
            child: Text(
              message,
              style: context.textStyles.bodyMedium?.copyWith(color: scheme.onTertiaryContainer, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
