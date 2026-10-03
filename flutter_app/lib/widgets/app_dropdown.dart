import 'package:flutter/material.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';

/// One option of an [AppDropdownField] / [AppSelectChip].
class AppDropdownItem<T> {
  final T value;
  final String label;
  final IconData? icon;

  const AppDropdownItem({required this.value, required this.label, this.icon});
}

/// Row used inside every themed menu (dropdowns and popup menus): icon, label
/// and a check mark + tinted pill for the selected entry.
class AppMenuRow extends StatelessWidget {
  final String label;
  final IconData? icon;
  final bool selected;
  final bool destructive;

  const AppMenuRow({
    super.key,
    required this.label,
    this.icon,
    this.selected = false,
    this.destructive = false,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final Color textColor = destructive ? scheme.error : (selected ? scheme.primary : scheme.onSurface);
    final Color iconColor = destructive ? scheme.error : (selected ? scheme.primary : scheme.onSurfaceVariant);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
      decoration: BoxDecoration(
        color: selected ? scheme.primaryContainer.fade(context.isDark ? 0.35 : 0.55) : Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.xs + 2),
      ),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, size: 20, color: iconColor),
            const SizedBox(width: AppSpacing.md),
          ],
          Expanded(
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: context.textStyles.bodyLarge?.copyWith(
                color: textColor,
                fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
              ),
            ),
          ),
          if (selected) ...[
            const SizedBox(width: AppSpacing.sm),
            Icon(Icons.check_rounded, size: 20, color: scheme.primary),
          ],
        ],
      ),
    );
  }
}

/// Themed [PopupMenuItem] (rounded highlight, check on the selected entry).
PopupMenuItem<T> appMenuItem<T>({
  required T value,
  required String label,
  IconData? icon,
  bool selected = false,
  bool destructive = false,
  bool enabled = true,
}) {
  return PopupMenuItem<T>(
    value: value,
    enabled: enabled,
    height: 44,
    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xs + 2),
    child: AppMenuRow(label: label, icon: icon, selected: selected, destructive: destructive),
  );
}

/// Form select with the app's input decoration, a rounded elevated menu and the
/// selected entry highlighted with a check mark. Controlled: pass [value] and
/// update it in [onChanged].
class AppDropdownField<T> extends StatelessWidget {
  final T? value;
  final List<AppDropdownItem<T>> items;
  final ValueChanged<T>? onChanged;
  final String? label;
  final String? hint;
  final IconData? prefixIcon;
  final FormFieldValidator<T>? validator;

  const AppDropdownField({
    super.key,
    required this.value,
    required this.items,
    required this.onChanged,
    this.label,
    this.hint,
    this.prefixIcon,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final T? current = items.any((i) => i.value == value) ? value : null;
    final handler = onChanged;

    return DropdownButtonFormField<T>(
      value: current,
      isExpanded: true,
      menuMaxHeight: 360,
      elevation: 6,
      borderRadius: BorderRadius.circular(AppRadius.md),
      dropdownColor: context.isDark ? scheme.surfaceContainerHigh : Colors.white,
      icon: const Icon(Icons.keyboard_arrow_down_rounded),
      iconEnabledColor: scheme.onSurfaceVariant,
      iconDisabledColor: scheme.onSurface.fade(0.38),
      style: context.textStyles.bodyLarge?.copyWith(fontWeight: FontWeight.w600, color: scheme.onSurface),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint ?? context.tr('select_option'),
        prefixIcon: prefixIcon != null ? Icon(prefixIcon) : null,
      ),
      validator: validator,
      onChanged: handler == null
          ? null
          : (T? v) {
              if (v != null) handler(v);
            },
      selectedItemBuilder: (ctx) => items
          .map<Widget>(
            (i) => Align(
              alignment: AlignmentDirectional.centerStart,
              child: Row(
                children: [
                  if (i.icon != null && prefixIcon == null) ...[
                    Icon(i.icon, size: 20, color: scheme.primary),
                    const SizedBox(width: AppSpacing.md),
                  ],
                  Flexible(
                    child: Text(i.label, maxLines: 1, overflow: TextOverflow.ellipsis),
                  ),
                ],
              ),
            ),
          )
          .toList(),
      items: items
          .map<DropdownMenuItem<T>>(
            (i) => DropdownMenuItem<T>(
              value: i.value,
              child: AppMenuRow(label: i.label, icon: i.icon, selected: i.value == current),
            ),
          )
          .toList(),
    );
  }
}

/// Compact pill select (e.g. sort in a toolbar) that opens a themed popup menu.
class AppSelectChip<T> extends StatelessWidget {
  final T value;
  final List<AppDropdownItem<T>> items;
  final ValueChanged<T> onChanged;
  final IconData? leadingIcon;
  final String? tooltip;

  const AppSelectChip({
    super.key,
    required this.value,
    required this.items,
    required this.onChanged,
    this.leadingIcon,
    this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    AppDropdownItem<T>? current;
    for (final i in items) {
      if (i.value == value) current = i;
    }
    final IconData? icon = leadingIcon ?? current?.icon;

    return PopupMenuButton<T>(
      tooltip: tooltip,
      initialValue: value,
      position: PopupMenuPosition.under,
      onSelected: onChanged,
      itemBuilder: (ctx) => items
          .map<PopupMenuEntry<T>>(
            (i) => appMenuItem<T>(value: i.value, label: i.label, icon: i.icon, selected: i.value == value),
          )
          .toList(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
        decoration: BoxDecoration(
          color: scheme.surface,
          borderRadius: BorderRadius.circular(AppRadius.pill),
          border: Border.all(color: scheme.outlineVariant),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 18, color: scheme.primary),
              const SizedBox(width: 6),
            ],
            ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 180),
              child: Text(
                current?.label ?? context.tr('select_option'),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: context.textStyles.labelLarge?.copyWith(fontWeight: FontWeight.w700),
              ),
            ),
            const SizedBox(width: 2),
            Icon(Icons.keyboard_arrow_down_rounded, size: 18, color: scheme.onSurfaceVariant),
          ],
        ),
      ),
    );
  }
}
