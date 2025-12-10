import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/review.dart';

class ReviewsScreen extends StatefulWidget {
  final ApiClient api;
  final int sellerId;

  const ReviewsScreen({super.key, required this.api, required this.sellerId});

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {
  List<Review> _reviews = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadReviews();
  }

  Future<void> _loadReviews() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final reviews = await widget.api.fetchReviews(widget.sellerId);
      setState(() {
        _reviews = reviews;
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

  void _openAddReview() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => AddReviewSheet(
        api: widget.api,
        sellerId: widget.sellerId,
        onSuccess: () {
          _loadReviews();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final avgRating = _reviews.isEmpty
        ? 0.0
        : _reviews.map((r) => r.rating).reduce((a, b) => a + b) / _reviews.length;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Reviews'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      color: Theme.of(context).colorScheme.primaryContainer,
                      child: Row(
                        children: [
                          Column(
                            children: [
                              Text(
                                avgRating.toStringAsFixed(1),
                                style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold),
                              ),
                              _buildStars(avgRating),
                              Text('${_reviews.length} reviews'),
                            ],
                          ),
                          const Spacer(),
                          ElevatedButton.icon(
                            icon: const Icon(Icons.add),
                            label: const Text('Add Review'),
                            onPressed: _openAddReview,
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: _reviews.isEmpty
                          ? const Center(child: Text('No reviews yet'))
                          : ListView.builder(
                              padding: const EdgeInsets.all(16),
                              itemCount: _reviews.length,
                              itemBuilder: (context, i) => _buildReviewCard(_reviews[i]),
                            ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildStars(double rating) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (i) {
        return Icon(
          i < rating.floor() ? Icons.star : Icons.star_border,
          color: Colors.amber,
          size: 16,
        );
      }),
    );
  }

  Widget _buildReviewCard(Review review) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(review.reviewerName, style: const TextStyle(fontWeight: FontWeight.bold)),
                const Spacer(),
                _buildStars(review.rating.toDouble()),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              '${review.createdAt.day}/${review.createdAt.month}/${review.createdAt.year}',
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
            if (review.comment != null && review.comment!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(review.comment!),
            ],
          ],
        ),
      ),
    );
  }
}

class AddReviewSheet extends StatefulWidget {
  final ApiClient api;
  final int sellerId;
  final VoidCallback onSuccess;

  const AddReviewSheet({super.key, required this.api, required this.sellerId, required this.onSuccess});

  @override
  State<AddReviewSheet> createState() => _AddReviewSheetState();
}

class _AddReviewSheetState extends State<AddReviewSheet> {
  final _commentController = TextEditingController();
  int _rating = 5;
  bool _loading = false;

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _loading = true);

    try {
      await widget.api.createReview(
        sellerId: widget.sellerId,
        rating: _rating,
        comment: _commentController.text.trim().isEmpty ? null : _commentController.text.trim(),
      );

      if (mounted) {
        Navigator.of(context).pop();
        widget.onSuccess();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Review submitted!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 16,
        bottom: MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('Add Review', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (i) {
              return IconButton(
                icon: Icon(
                  i < _rating ? Icons.star : Icons.star_border,
                  color: Colors.amber,
                  size: 32,
                ),
                onPressed: () => setState(() => _rating = i + 1),
              );
            }),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _commentController,
            decoration: const InputDecoration(
              labelText: 'Comment (optional)',
              border: OutlineInputBorder(),
            ),
            maxLines: 4,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loading ? null : _submit,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Theme.of(context).colorScheme.primary,
              foregroundColor: Colors.white,
            ),
            child: _loading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Submit Review'),
          ),
        ],
      ),
    );
  }
}
