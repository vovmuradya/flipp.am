class Review {
  final int id;
  final int sellerId;
  final int reviewerId;
  final String reviewerName;
  final int rating;
  final String comment;
  final String createdAt;

  const Review({
    required this.id,
    required this.sellerId,
    required this.reviewerId,
    required this.reviewerName,
    required this.rating,
    required this.comment,
    required this.createdAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      sellerId: json['seller_id'] is int ? json['seller_id'] as int : int.tryParse('${json['seller_id']}') ?? 0,
      reviewerId: json['reviewer_id'] is int ? json['reviewer_id'] as int : int.tryParse('${json['reviewer_id']}') ?? 0,
      reviewerName: json['reviewer_name']?.toString() ?? json['user']?['name']?.toString() ?? 'Anonymous',
      rating: json['rating'] is int ? json['rating'] as int : int.tryParse('${json['rating']}') ?? 0,
      comment: json['comment']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? '',
    );
  }
}