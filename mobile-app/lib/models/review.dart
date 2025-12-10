class Review {
  final int id;
  final int rating;
  final String? comment;
  final String reviewerName;
  final DateTime createdAt;

  const Review({
    required this.id,
    required this.rating,
    this.comment,
    required this.reviewerName,
    required this.createdAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      rating:
          json['rating'] is int ? json['rating'] : int.tryParse('${json['rating']}') ?? 0,
      comment: json['comment']?.toString(),
      reviewerName: json['reviewer_name']?.toString() ??
          json['reviewer']?['name']?.toString() ??
          'Anonymous',
      createdAt: json['created_at'] is String
          ? DateTime.tryParse(json['created_at']) ?? DateTime.now()
          : DateTime.now(),
    );
  }
}
