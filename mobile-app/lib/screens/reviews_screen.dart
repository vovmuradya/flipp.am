import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/review.dart';

class ReviewsScreen extends StatefulWidget {
  final int sellerId;
  final String sellerName;
  final ApiClient api;

  const ReviewsScreen({
    super.key,
    required this.sellerId,
    required this.sellerName,
    required this.api,
  });

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {
  List<Review> _reviews = [];
  bool _loading = true;
  String? _error;
  bool _showAddReview = false;
  int _rating = 0;
  String _comment = '';

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

  Future<void> _submitReview() async {
    if (_rating == 0) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please select a rating')),
        );
      }
      return;
    }

    setState(() => _loading = true);

    try {
      await widget.api.createReview(
        sellerId: widget.sellerId,
        rating: _rating,
        comment: _comment.isEmpty ? null : _comment,
      );

      // Refresh reviews
      await _loadReviews();
      setState(() {
        _showAddReview = false;
        _rating = 0;
        _comment = '';
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Review submitted successfully')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to submit review: $e')),
        );
      }
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Reviews for ${widget.sellerName}'),
      ),
      body: _loading && _reviews.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _reviews.isEmpty
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : Column(
                  children: [
                    if (_showAddReview) _buildAddReviewForm(),
                    Expanded(
                      child: RefreshIndicator(
                        onRefresh: _loadReviews,
                        child: _reviews.isEmpty
                            ? const Center(child: Text('No reviews yet'))
                            : ListView.builder(
                                padding: const EdgeInsets.all(16),
                                itemCount: _reviews.length,
                                itemBuilder: (context, index) => _buildReviewCard(_reviews[index]),
                              ),
                      ),
                    ),
                  ],
                ),
      floatingActionButton: !_showAddReview
          ? FloatingActionButton(
              onPressed: () => setState(() => _showAddReview = true),
              child: const Icon(Icons.add_comment),
            )
          : null,
    );
  }

  Widget _buildAddReviewForm() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Write a Review',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Row(
              children: List.generate(
                5,
                (index) => IconButton(
                  onPressed: () => setState(() => _rating = index + 1),
                  icon: Icon(
                    index < _rating ? Icons.star : Icons.star_border,
                    color: Colors.amber,
                    size: 32,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              decoration: const InputDecoration(
                labelText: 'Your review',
                border: OutlineInputBorder(),
                hintText: 'Share your experience with this seller...',
              ),
              maxLines: 4,
              onChanged: (value) => _comment = value,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                TextButton(
                  onPressed: () => setState(() => _showAddReview = false),
                  child: const Text('Cancel'),
                ),
                const Spacer(),
                ElevatedButton(
                  onPressed: _loading ? null : _submitReview,
                  child: _loading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Submit'),
                ),
              ],
            ),
          ],
        ),
      ),
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
                CircleAvatar(
                  child: Text(review.reviewerName.isNotEmpty 
                      ? review.reviewerName.substring(0, 1).toUpperCase() 
                      : '?'),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        review.reviewerName,
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      Row(
                        children: List.generate(
                          5,
                          (index) => Icon(
                            index < review.rating ? Icons.star : Icons.star_border,
                            color: Colors.amber,
                            size: 16,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  review.createdAt,
                  style: const TextStyle(color: Colors.grey, fontSize: 12),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              review.comment,
              style: const TextStyle(height: 1.4),
            ),
          ],
        ),
      ),
    );
  }
}