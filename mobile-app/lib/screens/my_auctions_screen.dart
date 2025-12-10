import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/listing.dart';

class MyAuctionsScreen extends StatefulWidget {
  final ApiClient api;

  const MyAuctionsScreen({super.key, required this.api});

  @override
  State<MyAuctionsScreen> createState() => _MyAuctionsScreenState();
}

class _MyAuctionsScreenState extends State<MyAuctionsScreen> {
  List<Listing> _auctions = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAuctions();
  }

  Future<void> _loadAuctions() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final auctions = await widget.api.fetchMyAuctions();
      setState(() {
        _auctions = auctions;
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

  Future<void> _deleteListing(int id) async {
    try {
      await widget.api.deleteListing(id);
      _loadAuctions();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listing deleted')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Delete failed: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Auctions'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAuctions,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : _auctions.isEmpty
                  ? const Center(child: Text('No auction listings yet'))
                  : RefreshIndicator(
                      onRefresh: _loadAuctions,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _auctions.length,
                        itemBuilder: (context, i) => _buildCard(_auctions[i]),
                      ),
                    ),
    );
  }

  Widget _buildCard(Listing listing) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          ListTile(
            leading: listing.imageUrl.isNotEmpty
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      listing.imageUrl,
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        width: 60,
                        height: 60,
                        color: Colors.grey[300],
                        child: const Icon(Icons.image_not_supported),
                      ),
                    ),
                  )
                : null,
            title: Text(listing.title, maxLines: 2, overflow: TextOverflow.ellipsis),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(listing.priceDisplay, style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold)),
                if (listing.primaryDamage != null) Text('Damage: ${listing.primaryDamage}', style: const TextStyle(fontSize: 12, color: Colors.orange)),
              ],
            ),
          ),
          ButtonBar(
            children: [
              TextButton.icon(
                icon: const Icon(Icons.delete, size: 18),
                label: const Text('Delete'),
                onPressed: () => _deleteListing(listing.id),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
