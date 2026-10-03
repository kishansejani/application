import 'address.dart';
import 'json_utils.dart';

class Order {
  static const List<String> trackingSteps = [
    'pending',
    'confirmed',
    'processing',
    'out_for_delivery',
    'delivered',
  ];

  final int id;
  final String orderNumber;
  final String? invoiceNumber;
  final double subtotal;
  final double discountAmount;
  final double deliveryCharge;
  final double totalAmount;
  final String? couponCode;
  final String paymentMethod;
  final String paymentStatus;
  final String orderStatus;
  final String? statusLocalized;

  /// `two_hours` or `next_day`.
  final String deliveryType;

  /// Human readable slot text (already localised by the API).
  final String? deliverySlot;
  final String? estimatedDeliveryAt;
  final String? deliveryAddress;
  final String? customerName;
  final String? customerPhone;
  final String createdAt;
  final int itemsCount;
  final String? firstItemName;
  final String? firstItemImage;
  final String? notes;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.orderNumber,
    this.invoiceNumber,
    this.subtotal = 0,
    this.discountAmount = 0,
    this.deliveryCharge = 0,
    required this.totalAmount,
    this.couponCode,
    this.paymentMethod = 'cod',
    this.paymentStatus = 'pending',
    this.orderStatus = 'pending',
    this.statusLocalized,
    this.deliveryType = 'two_hours',
    this.deliverySlot,
    this.estimatedDeliveryAt,
    this.deliveryAddress,
    this.customerName,
    this.customerPhone,
    this.createdAt = '',
    this.itemsCount = 0,
    this.firstItemName,
    this.firstItemImage,
    this.notes,
    this.items = const [],
  });

  bool get isExpress => deliveryType == 'two_hours' || deliveryType == '2_hours';
  bool get isCancelled => orderStatus == 'cancelled';
  bool get isDelivered => orderStatus == 'delivered';
  bool get isActive => !isCancelled && !isDelivered;

  /// Index into [trackingSteps]; -1 for cancelled/unknown.
  int get progressIndex => trackingSteps.indexOf(orderStatus);

  factory Order.fromJson(Map<String, dynamic> json) {
    final items = mapList(json['items'], (e) => OrderItem.fromJson(e));
    String? address = asStringOrNull(json['delivery_address']);
    if (address == null && json['address'] is Map) {
      address = Address.fromJson(asMap(json['address'])).fullAddress;
    }
    final legacySlot = asStringOrNull(json['delivery_slot']);
    return Order(
      id: asInt(json['id'] ?? json['order_id']),
      orderNumber: asString(json['order_number']),
      invoiceNumber: asStringOrNull(json['invoice_number']),
      subtotal: asDouble(json['subtotal']),
      discountAmount: asDouble(json['discount_amount']),
      deliveryCharge: asDouble(json['delivery_charge']),
      totalAmount: asDouble(json['total_amount']),
      couponCode: asStringOrNull(json['coupon_code']),
      paymentMethod: asString(json['payment_method'], 'cod'),
      paymentStatus: asString(json['payment_status'], 'pending'),
      orderStatus: asString(json['order_status'], 'pending'),
      statusLocalized: asStringOrNull(json['status_localized']),
      deliveryType: asStringOrNull(json['delivery_type']) ??
          (legacySlot == 'next_day' ? 'next_day' : 'two_hours'),
      deliverySlot: legacySlot ?? asStringOrNull(json['delivery_slot_time']),
      estimatedDeliveryAt: asStringOrNull(json['estimated_delivery_at']),
      deliveryAddress: address,
      customerName: asStringOrNull(json['customer_name']),
      customerPhone: asStringOrNull(json['customer_phone']),
      createdAt: asString(json['created_at']),
      itemsCount: asInt(json['items_count'], items.length),
      firstItemName: asStringOrNull(json['first_item_name']) ?? (items.isNotEmpty ? items.first.name : null),
      firstItemImage: asStringOrNull(json['first_item_image']) ?? (items.isNotEmpty ? items.first.image : null),
      notes: asStringOrNull(json['notes'] ?? json['delivery_note']),
      items: items,
    );
  }
}

class OrderItem {
  final int id;
  final int productId;
  final String name;
  final String unit;
  final double unitPrice;
  final int quantity;
  final double totalPrice;
  final String? image;

  OrderItem({
    required this.id,
    this.productId = 0,
    required this.name,
    this.unit = '',
    required this.unitPrice,
    required this.quantity,
    required this.totalPrice,
    this.image,
  });

  String get productName => name;

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    final qty = asInt(json['quantity'], 1);
    final price = asDouble(json['price'] ?? json['unit_price']);
    return OrderItem(
      id: asInt(json['id']),
      productId: asInt(json['product_id']),
      name: asString(json['name'] ?? json['product_name'] ?? json['product_name_en']),
      unit: asString(json['unit'] ?? json['product_unit']),
      unitPrice: price,
      quantity: qty,
      totalPrice: asDouble(json['total'] ?? json['total_price'], price * qty),
      image: asStringOrNull(json['image'] ?? json['product_image']),
    );
  }
}
