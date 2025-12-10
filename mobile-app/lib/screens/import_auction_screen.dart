import 'package:flutter/material.dart';
import '../api_client.dart';

class ImportAuctionScreen extends StatefulWidget {
  final ApiClient api;

  const ImportAuctionScreen({super.key, required this.api});

  @override
  State<ImportAuctionScreen> createState() => _ImportAuctionScreenState();
}

class _ImportAuctionScreenState extends State<ImportAuctionScreen> {
  final _urlController = TextEditingController();
  bool _loading = false;
  String? _error;
  Map<String, dynamic>? _auctionData;

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _fetchAuction() async {
    if (_urlController.text.trim().isEmpty) {
      setState(() => _error = 'Please enter URL');
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
      _auctionData = null;
    });

    try {
      final data = await widget.api.fetchFromAuctionUrl(_urlController.text.trim());
      setState(() {
        _auctionData = data;
      });
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

  Future<void> _createListing() async {
    if (_auctionData == null) return;

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final fields = <String, String>{
        'listing_type': 'vehicle',
        'is_from_auction': '1',
        if (_auctionData!['title'] != null) 'title': _auctionData!['title'].toString(),
        if (_auctionData!['buy_now_price'] != null) 'buy_now_price': _auctionData!['buy_now_price'].toString(),
        if (_auctionData!['current_bid'] != null) 'current_bid': _auctionData!['current_bid'].toString(),
        if (_auctionData!['operational_status'] != null) 'operational_status': _auctionData!['operational_status'].toString(),
        if (_auctionData!['primary_damage'] != null) 'primary_damage': _auctionData!['primary_damage'].toString(),
        if (_auctionData!['source_auction_url'] != null) 'source_auction_url': _auctionData!['source_auction_url'].toString(),
        if (_auctionData!['auction_ends_at'] != null) 'auction_ends_at': _auctionData!['auction_ends_at'].toString(),
        if (_auctionData!['year'] != null) 'year': _auctionData!['year'].toString(),
        if (_auctionData!['make'] != null) 'make': _auctionData!['make'].toString(),
        if (_auctionData!['model'] != null) 'model': _auctionData!['model'].toString(),
        if (_auctionData!['mileage'] != null) 'mileage': _auctionData!['mileage'].toString(),
        if (_auctionData!['exterior_color'] != null) 'exterior_color': _auctionData!['exterior_color'].toString(),
        if (_auctionData!['description'] != null) 'description': _auctionData!['description'].toString(),
      };

      await widget.api.createListing(fields: fields);

      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Auction listing imported successfully!')),
        );
      }
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
        title: const Text('Import from Copart'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text(
            'Paste Copart auction URL to import vehicle details',
            style: TextStyle(fontSize: 14, color: Colors.grey),
          ),
          const SizedBox(height: 16),
          
          TextField(
            controller: _urlController,
            decoration: const InputDecoration(
              labelText: 'Copart URL',
              hintText: 'https://www.copart.com/lot/...',
              border: OutlineInputBorder(),
            ),
            maxLines: 2,
          ),
          const SizedBox(height: 16),

          ElevatedButton(
            onPressed: _loading ? null : _fetchAuction,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Theme.of(context).colorScheme.primary,
              foregroundColor: Colors.white,
            ),
            child: _loading && _auctionData == null
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Fetch Details'),
          ),

          if (_error != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_error!, style: const TextStyle(color: Colors.red)),
            ),
          ],

          if (_auctionData != null) ...[
            const SizedBox(height: 24),
            const Text('Preview', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildRow('Title', _auctionData!['title']?.toString()),
                    _buildRow('Make', _auctionData!['make']?.toString()),
                    _buildRow('Model', _auctionData!['model']?.toString()),
                    _buildRow('Year', _auctionData!['year']?.toString()),
                    _buildRow('Mileage', _auctionData!['mileage']?.toString()),
                    _buildRow('Color', _auctionData!['exterior_color']?.toString()),
                    _buildRow('Damage', _auctionData!['primary_damage']?.toString()),
                    _buildRow('Status', _auctionData!['operational_status']?.toString()),
                    _buildRow('Buy Now', _auctionData!['buy_now_price']?.toString()),
                    _buildRow('Current Bid', _auctionData!['current_bid']?.toString()),
                    if (_auctionData!['photos'] != null && _auctionData!['photos'] is List)
                      Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: Text('${(_auctionData!['photos'] as List).length} photos found'),
                      ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loading ? null : _createListing,
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
              ),
              child: _loading && _auctionData != null
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Create Listing'),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildRow(String label, String? value) {
    if (value == null || value.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
