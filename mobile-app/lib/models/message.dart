class ChatMessage {
  final int id;
  final int? listingId;
  final String body;
  final bool isMine;
  final String? createdAt;
  final String? imageUrl;

  const ChatMessage({
    required this.id,
    this.listingId,
    required this.body,
    required this.isMine,
    this.createdAt,
    this.imageUrl,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json, {String? myId}) {
    final idValue = json['id'];
    final senderId = json['sender_id']?.toString();
    return ChatMessage(
      id: idValue is int ? idValue : int.tryParse('$idValue') ?? 0,
      listingId: json['listing_id'] is int
          ? json['listing_id'] as int
          : int.tryParse('${json['listing_id']}'),
      body: json['body']?.toString() ??
          json['message']?.toString() ??
          '[empty]',
      isMine: myId != null && senderId == myId,
      createdAt: json['created_at']?.toString(),
      imageUrl: json['image_url']?.toString(),
    );
  }
}
