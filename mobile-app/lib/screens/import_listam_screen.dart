import 'package:flutter/material.dart';
import '../api_client.dart';

class ImportListAmScreen extends StatefulWidget {
  final ApiClient api;

  const ImportListAmScreen({super.key, required this.api});

  @override
  State<ImportListAmScreen> createState() => _ImportListAmScreenState();
}

class _ImportListAmScreenState extends State<ImportListAmScreen> {
  final _urlController = TextEditingController();
  bool _loading = false;
  String? _error;
  Map<String, dynamic>? _listingData;

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _fetchListing() async {
    if (_urlController.text.trim().isEmpty) {
      setState(() => _error = 'Please enter URL');
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
      _listingData = null;
    });

    try {
      // TODO: Implement List.am parser API endpoint
      // For now, show coming soon message
      await Future.delayed(const Duration(seconds: 1));
      throw Exception('List.am import coming soon! Please use manual listing creation.');
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst(RegExp('^Exception: '), '');
      });
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Import from List.am'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text(
            'Paste List.am URL to import listing details',
            style: TextStyle(fontSize: 14, color: Colors.grey),
          ),
          const SizedBox(height: 16),
          
          TextField(
            controller: _urlController,
            decoration: const InputDecoration(
              labelText: 'List.am URL',
              hintText: 'https://www.list.am/item/...',
              border: OutlineInputBorder(),
            ),
            maxLines: 2,
          ),
          const SizedBox(height: 16),

          ElevatedButton(
            onPressed: _loading ? null : _fetchListing,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Theme.of(context).colorScheme.primary,
              foregroundColor: Colors.white,
            ),
            child: _loading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Import Listing'),
          ),

          if (_error != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.orange.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.orange),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.info_outline, color: Colors.orange[700]),
                      const SizedBox(width: 8),
                      const Text(
                        'Coming Soon',
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(_error!, style: TextStyle(color: Colors.orange[900])),
                  const SizedBox(height: 8),
                  const Text(
                    'For now, please use "Vehicle Listing" option to create listing manually.',
                    style: TextStyle(fontSize: 12),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 16),

          const Text(
            'How it works:',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          _buildStep('1', 'Copy listing URL from List.am'),
          _buildStep('2', 'Paste it here'),
          _buildStep('3', 'We\'ll import all details automatically'),
          _buildStep('4', 'Review and publish'),
        ],
      ),
    );
  }

  Widget _buildStep(String number, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 24,
            height: 24,
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primary.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                number,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Theme.of(context).colorScheme.primary,
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}
