import 'json_utils.dart';
import 'product.dart';

class CartItem {
  final int id;
  final int productId;
  int quantity;
  final double unitPrice;
  final double mrp;
  final String name;
  final String unit;
  final int maxStock;
  final String? image;
  final Product? product;

  CartItem({
    required this.id,
    required this.productId,
    required this.quantity,
    required this.unitPrice,
    double? mrp,
    this.name = '',
    this.unit = '',
    this.maxStock = 999,
    this.image,
    this.product,
  }) : mrp = mrp ?? unitPrice;

  double get totalPrice => unitPrice * quantity;

  double get savings => mrp > unitPrice ? (mrp - unitPrice) * quantity : 0;

  bool get canIncrement => quantity < maxStock;

  /// Light-weight product used to open the product page or re-add the item.
  Product toProduct() {
    return product ??
        Product(
          id: productId,
          name: name,
          price: unitPrice,
          strikePrice: mrp > unitPrice ? mrp : null,
          unit: unit,
          stock: maxStock,
          isInStock: maxStock > 0,
          mainImage: image,
        );
  }

  /// Parses `GET /cart` rows (`cart_item_id`, `price`, `mrp`, `max_stock` ...).
  factory CartItem.fromJson(Map<String, dynamic> json) {
    final product = json['product'] is Map ? Product.fromJson(asMap(json['product'])) : null;
    final unitPrice = asDouble(json['price'] ?? json['unit_price'], product?.price ?? 0);
    return CartItem(
      id: asInt(json['cart_item_id'] ?? json['id']),
      productId: asInt(json['product_id'], product?.id ?? 0),
      quantity: asInt(json['quantity'], 1),
      unitPrice: unitPrice,
      mrp: asDoubleOrNull(json['mrp']) ?? product?.strikePrice ?? unitPrice,
      name: asString(json['name'], product?.name ?? ''),
      unit: asString(json['unit'], product?.unit ?? ''),
      maxStock: asInt(json['max_stock'], product?.stock ?? 999),
      image: asStringOrNull(json['thumbnail_url'] ?? json['image']) ?? product?.mainImage,
      product: product,
    );
  }
}
