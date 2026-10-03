import 'json_utils.dart';

class PageContent {
  final String title;
  final String slug;
  final String content;

  PageContent({required this.title, required this.slug, required this.content});

  factory PageContent.fromJson(Map<String, dynamic> json) {
    return PageContent(
      title: asString(json['title']),
      slug: asString(json['slug']),
      content: asString(json['content']),
    );
  }
}
