import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/listing.dart';

class MyListingsScreen extends StatefulWidget {
  final ApiClient api;

  const MyListingsScreen({super.key, required this.api});

  @override
  State<MyListingsScreen> createState() => _MyListingsScreenState();
}

class _MyListingsScreenState extends State<MyListingsScreen> {
  List<Listing> _listings = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadListings();
  }

  Future<void> _loadListings() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final listings = await widget.api.fetchMyListings();
      setState(() {
        _listings = listings;
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
      _loadListings();
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

  Future<void> _bumpListing(int id) async {
    try {
      await widget.api.bumpListing(id);
      _loadListings();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listing bumped to top!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Bump failed: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Listings'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadListings,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : _listings.isEmpty
                  ? const Center(child: Text('No listings yet'))
                  : RefreshIndicator(
                      onRefresh: _loadListings,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _listings.length,
                        itemBuilder: (context, i) => _buildCard(_listings[i]),
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
            subtitle: Text(listing.priceDisplay, style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold)),
          ),
          ButtonBar(
            children: [
              TextButton.icon(
                icon: const Icon(Icons.arrow_upward, size: 18),
                label: const Text('Bump'),
                onPressed: () => _bumpListing(listing.id),
              ),
              TextButton.icon(
                icon: const Icon(Icons.edit, size: 18),
                label: const Text('Edit'),
                onPressed: () {
                  // TODO: navigate to edit screen
                },
              ),
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
