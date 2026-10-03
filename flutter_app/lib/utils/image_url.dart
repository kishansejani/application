import '../constants/api_constants.dart';

/// Turns whatever the API returns (absolute URL, `storage/..` or a bare
/// storage path) into a loadable URL. Also rewrites `localhost` URLs generated
/// by Laravel's `asset()` to the host the app talks to (e.g. 10.0.2.2 / LAN IP).
class ImageUrl {
  ImageUrl._();

  static String? resolve(String? path) {
    if (path == null) return null;
    var p = path.trim();
    if (p.isEmpty) return null;
    if (p.startsWith('//')) p = 'https:$p';

    if (p.startsWith('http://') || p.startsWith('https://')) {
      final uri = Uri.tryParse(p);
      final base = Uri.tryParse(ApiConstants.webBaseUrl);
      if (uri != null && base != null && _isLoopback(uri.host) && !_isLoopback(base.host)) {
        return uri.replace(scheme: base.scheme, host: base.host, port: base.port).toString();
      }
      return p;
    }

    if (p.startsWith('/')) p = p.substring(1);
    if (p.startsWith('storage/') || p.startsWith('images/')) {
      return '${ApiConstants.webBaseUrl}/$p';
    }
    return '${ApiConstants.imageBaseUrl}/$p';
  }

  static bool _isLoopback(String host) => host == 'localhost' || host == '127.0.0.1';
}
