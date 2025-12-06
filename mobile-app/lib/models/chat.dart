class ChatSummary {
  final int id;
  final int? listingId;
  final String title;
  final String lastMessage;
  final String time;
  final int unread;
  final String? imageUrl;

  const ChatSummary({
    required this.id,
    this.listingId,
    required this.title,
    required this.lastMessage,
    required this.time,
    this.unread = 0,
    this.imageUrl,
  });

  factory ChatSummary.fromJson(Map<String, dynamic> json) {
    final listing = json['listing'];
    final idValue = json['id'];
    return ChatSummary(
      id: idValue is int ? idValue : int.tryParse('$idValue') ?? 0,
      listingId: listing is Map && listing['id'] != null
          ? (listing['id'] is int
              ? listing['id'] as int
              : int.tryParse('${listing['id']}'))
          : (json['listing_id'] is int
              ? json['listing_id'] as int
              : int.tryParse('${json['listing_id']}')),
      title: (listing is Map && listing['title'] != null)
          ? listing['title'].toString()
          : json['title']?.toString() ?? 'Chat',
      lastMessage: json['last_message']?.toString() ??
          json['message']?.toString() ??
          'No messages yet',
      time: json['last_message_time']?.toString() ??
          json['created_at']?.toString() ??
          '',
      unread: json['unread'] is int ? json['unread'] as int : 0,
      imageUrl: listing is Map ? listing['preview_image_url']?.toString() : null,
    );
  }
}
