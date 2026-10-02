import 'product.dart';

class CartItem {
  final int id;
  final int productId;
  int quantity;
  final double unitPrice;
  final Product? product;

  CartItem({
    required this.id,
    required this.productId,
    required this.quantity,
    required this.unitPrice,
    this.product,
  });

  double get totalPrice => unitPrice * quantity;

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      productId: json['product_id'] is int ? json['product_id'] : int.tryParse(json['product_id'].toString()) ?? 0,
      quantity: json['quantity'] is int ? json['quantity'] : int.tryParse(json['quantity'].toString()) ?? 1,
      unitPrice: json['unit_price'] != null ? double.tryParse(json['unit_price'].toString()) ?? 0.0 : 0.0,
      product: json['product'] != null ? Product.fromJson(json['product']) : null,
    );
  }
}
