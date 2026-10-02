import 'address.dart';
import 'product.dart';

class Order {
  final int id;
  final String orderNumber;
  final int userId;
  final int? addressId;
  final double subtotal;
  final double discountAmount;
  final double deliveryCharge;
  final double totalAmount;
  final String? couponCode;
  final String paymentMethod;
  final String paymentStatus;
  final String orderStatus;
  final String deliverySlot; // '2_hours' or 'next_day'
  final String? deliverySlotTime;
  final String? deliveryNote;
  final String createdAt;
  final Address? address;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.orderNumber,
    required this.userId,
    this.addressId,
    required this.subtotal,
    required this.discountAmount,
    required this.deliveryCharge,
    required this.totalAmount,
    this.couponCode,
    required this.paymentMethod,
    required this.paymentStatus,
    required this.orderStatus,
    required this.deliverySlot,
    this.deliverySlotTime,
    this.deliveryNote,
    required this.createdAt,
    this.address,
    this.items = const [],
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    var itemsJson = json['items'] as List? ?? [];
    List<OrderItem> itemsList = itemsJson.map((i) => OrderItem.fromJson(i)).toList();

    return Order(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      orderNumber: json['order_number'] ?? '',
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      addressId: json['address_id'] != null ? (json['address_id'] is int ? json['address_id'] : int.tryParse(json['address_id'].toString())) : null,
      subtotal: json['subtotal'] != null ? double.tryParse(json['subtotal'].toString()) ?? 0.0 : 0.0,
      discountAmount: json['discount_amount'] != null ? double.tryParse(json['discount_amount'].toString()) ?? 0.0 : 0.0,
      deliveryCharge: json['delivery_charge'] != null ? double.tryParse(json['delivery_charge'].toString()) ?? 0.0 : 0.0,
      totalAmount: json['total_amount'] != null ? double.tryParse(json['total_amount'].toString()) ?? 0.0 : 0.0,
      couponCode: json['coupon_code'],
      paymentMethod: json['payment_method'] ?? 'cod',
      paymentStatus: json['payment_status'] ?? 'pending',
      orderStatus: json['order_status'] ?? 'pending',
      deliverySlot: json['delivery_slot'] ?? '2_hours',
      deliverySlotTime: json['delivery_slot_time'],
      deliveryNote: json['delivery_note'],
      createdAt: json['created_at'] ?? '',
      address: json['address'] != null ? Address.fromJson(json['address']) : null,
      items: itemsList,
    );
  }
}

class OrderItem {
  final int id;
  final int orderId;
  final int productId;
  final String productName;
  final String productNameGu;
  final double unitPrice;
  final int quantity;
  final double totalPrice;
  final Product? product;

  OrderItem({
    required this.id,
    required this.orderId,
    required this.productId,
    required this.productName,
    required this.productNameGu,
    required this.unitPrice,
    required this.quantity,
    required this.totalPrice,
    this.product,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      orderId: json['order_id'] is int ? json['order_id'] : int.tryParse(json['order_id'].toString()) ?? 0,
      productId: json['product_id'] is int ? json['product_id'] : int.tryParse(json['product_id'].toString()) ?? 0,
      productName: json['product_name'] ?? '',
      productNameGu: json['product_name_gu'] ?? json['product_name'] ?? '',
      unitPrice: json['unit_price'] != null ? double.tryParse(json['unit_price'].toString()) ?? 0.0 : 0.0,
      quantity: json['quantity'] is int ? json['quantity'] : int.tryParse(json['quantity'].toString()) ?? 1,
      totalPrice: json['total_price'] != null ? double.tryParse(json['total_price'].toString()) ?? 0.0 : 0.0,
      product: json['product'] != null ? Product.fromJson(json['product']) : null,
    );
  }
}
