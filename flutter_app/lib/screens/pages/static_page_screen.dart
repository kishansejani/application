import 'package:flutter/material.dart';
import '../../constants/api_constants.dart';
import '../../constants/app_colors.dart';
import '../../models/page_content.dart';
import '../../services/api_service.dart';

class StaticPageScreen extends StatefulWidget {
  final String slug;
  final String title;

  const StaticPageScreen({Key? key, required this.slug, required this.title}) : super(key: key);

  @override
  State<StaticPageScreen> createState() => _StaticPageScreenState();
}

class _StaticPageScreenState extends State<StaticPageScreen> {
  PageContent? _page;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadPage();
  }

  Future<void> _loadPage() async {
    try {
      final res = await ApiService.get('${ApiConstants.pages}/${widget.slug}');
      if (res['page'] != null) {
        setState(() {
          _page = PageContent.fromJson(res['page']);
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          _page?.title ?? widget.title,
          style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _page == null
              ? const Center(child: Text('Content not available.'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _page!.title,
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        const SizedBox(height: 16),
                        const Divider(),
                        const SizedBox(height: 16),
                        Text(
                          _page!.content,
                          style: const TextStyle(fontSize: 14, color: AppColors.textSecondary, height: 1.6),
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }
}
