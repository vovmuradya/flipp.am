import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/filters/search_filters.dart';
import '../models/listing.dart';

class SearchScreen extends StatefulWidget {
  final ApiClient api;

  const SearchScreen({super.key, required this.api});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _searchController = TextEditingController();
  SearchFilters _filters = const SearchFilters();
  List<Listing> _results = [];
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _search() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final filters = _filters.copyWith(query: _searchController.text.trim().isEmpty ? null : _searchController.text.trim());
      final results = await widget.api.searchListings(filters);
      setState(() {
        _results = results;
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

  void _openFilters() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => FilterScreen(
          initialFilters: _filters,
          onApply: (filters) {
            setState(() {
              _filters = filters;
            });
            _search();
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Search'),
        actions: [
          IconButton(
            icon: Icon(
              Icons.filter_list,
              color: _filters.hasActiveFilters ? Theme.of(context).colorScheme.primary : null,
            ),
            onPressed: _openFilters,
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search cars, parts...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _searchController.clear();
                    _search();
                  },
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onSubmitted: (_) => _search(),
            ),
          ),
          if (_filters.hasActiveFilters)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Wrap(
                spacing: 8,
                children: [
                  if (_filters.categoryId != null) _buildChip('Category', () => setState(() => _filters = _filters.copyWith(categoryId: null))),
                  if (_filters.regionId != null) _buildChip('Region', () => setState(() => _filters = _filters.copyWith(regionId: null))),
                  if (_filters.minPrice != null || _filters.maxPrice != null) _buildChip('Price', () => setState(() => _filters = _filters.copyWith(minPrice: null, maxPrice: null))),
                  if (_filters.brandId != null) _buildChip('Brand', () => setState(() => _filters = _filters.copyWith(brandId: null))),
                  if (_filters.isFromAuction != null) _buildChip('Auction', () => setState(() => _filters = _filters.copyWith(isFromAuction: null))),
                ],
              ),
            ),
          if (_loading)
            const Expanded(child: Center(child: CircularProgressIndicator()))
          else if (_error != null)
            Expanded(child: Center(child: Text(_error!, style: const TextStyle(color: Colors.red))))
          else if (_results.isEmpty)
            const Expanded(child: Center(child: Text('No results')))
          else
            Expanded(
              child: ListView.builder(
                itemCount: _results.length,
                padding: const EdgeInsets.all(16),
                itemBuilder: (context, i) => _buildListingCard(_results[i]),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildChip(String label, VoidCallback onDelete) {
    return Chip(
      label: Text(label),
      onDeleted: onDelete,
      deleteIconColor: Theme.of(context).colorScheme.error,
    );
  }

  Widget _buildListingCard(Listing listing) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
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
        title: Text(listing.title, maxLines: 1, overflow: TextOverflow.ellipsis),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(listing.priceDisplay, style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold)),
            if (listing.location != null) Text(listing.location!, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
          ],
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: () {
          // Navigate to details
        },
      ),
    );
  }
}

class FilterScreen extends StatefulWidget {
  final SearchFilters initialFilters;
  final Function(SearchFilters) onApply;

  const FilterScreen({super.key, required this.initialFilters, required this.onApply});

  @override
  State<FilterScreen> createState() => _FilterScreenState();
}

class _FilterScreenState extends State<FilterScreen> {
  late SearchFilters _filters;

  @override
  void initState() {
    super.initState();
    _filters = widget.initialFilters;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Filters'),
        actions: [
          TextButton(
            onPressed: () {
              setState(() {
                _filters = const SearchFilters();
              });
            },
            child: const Text('Clear All'),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSection('Price Range', [
            Row(
              children: [
                Expanded(
                  child: TextField(
                    decoration: const InputDecoration(labelText: 'Min Price', border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                    controller: TextEditingController(text: _filters.minPrice?.toString() ?? ''),
                    onChanged: (v) => _filters = _filters.copyWith(minPrice: double.tryParse(v)),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: TextField(
                    decoration: const InputDecoration(labelText: 'Max Price', border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                    controller: TextEditingController(text: _filters.maxPrice?.toString() ?? ''),
                    onChanged: (v) => _filters = _filters.copyWith(maxPrice: double.tryParse(v)),
                  ),
                ),
              ],
            ),
          ]),
          const SizedBox(height: 16),
          _buildSection('Year Range', [
            Row(
              children: [
                Expanded(
                  child: TextField(
                    decoration: const InputDecoration(labelText: 'Min Year', border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                    controller: TextEditingController(text: _filters.minYear?.toString() ?? ''),
                    onChanged: (v) => _filters = _filters.copyWith(minYear: int.tryParse(v)),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: TextField(
                    decoration: const InputDecoration(labelText: 'Max Year', border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                    controller: TextEditingController(text: _filters.maxYear?.toString() ?? ''),
                    onChanged: (v) => _filters = _filters.copyWith(maxYear: int.tryParse(v)),
                  ),
                ),
              ],
            ),
          ]),
          const SizedBox(height: 16),
          _buildSection('Condition', [
            SegmentedButton<String?>(
              segments: const [
                ButtonSegment(value: null, label: Text('All')),
                ButtonSegment(value: 'undamaged', label: Text('Clean')),
                ButtonSegment(value: 'damaged', label: Text('Damaged')),
              ],
              selected: {_filters.condition},
              onSelectionChanged: (set) {
                setState(() {
                  _filters = _filters.copyWith(condition: set.first);
                });
              },
            ),
          ]),
          const SizedBox(height: 16),
          CheckboxListTile(
            title: const Text('Copart Only'),
            value: _filters.isFromAuction ?? false,
            onChanged: (v) {
              setState(() {
                _filters = _filters.copyWith(isFromAuction: v);
              });
            },
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: ElevatedButton(
            onPressed: () {
              widget.onApply(_filters);
              Navigator.of(context).pop();
            },
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Theme.of(context).colorScheme.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Apply Filters'),
          ),
        ),
      ),
    );
  }

  Widget _buildSection(String title, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        ...children,
      ],
    );
  }
}
