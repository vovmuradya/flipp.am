class NotificationSettings {
  final bool emailMessages;
  final bool emailNewListings;
  final bool emailPriceDrops;
  final bool pushMessages;
  final bool pushNewListings;
  final bool pushPriceDrops;

  const NotificationSettings({
    this.emailMessages = true,
    this.emailNewListings = false,
    this.emailPriceDrops = false,
    this.pushMessages = true,
    this.pushNewListings = false,
    this.pushPriceDrops = false,
  });

  factory NotificationSettings.fromJson(Map<String, dynamic> json) {
    return NotificationSettings(
      emailMessages: json['email_messages'] == true,
      emailNewListings: json['email_new_listings'] == true,
      emailPriceDrops: json['email_price_drops'] == true,
      pushMessages: json['push_messages'] == true,
      pushNewListings: json['push_new_listings'] == true,
      pushPriceDrops: json['push_price_drops'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'email_messages': emailMessages,
      'email_new_listings': emailNewListings,
      'email_price_drops': emailPriceDrops,
      'push_messages': pushMessages,
      'push_new_listings': pushNewListings,
      'push_price_drops': pushPriceDrops,
    };
  }

  NotificationSettings copyWith({
    bool? emailMessages,
    bool? emailNewListings,
    bool? emailPriceDrops,
    bool? pushMessages,
    bool? pushNewListings,
    bool? pushPriceDrops,
  }) {
    return NotificationSettings(
      emailMessages: emailMessages ?? this.emailMessages,
      emailNewListings: emailNewListings ?? this.emailNewListings,
      emailPriceDrops: emailPriceDrops ?? this.emailPriceDrops,
      pushMessages: pushMessages ?? this.pushMessages,
      pushNewListings: pushNewListings ?? this.pushNewListings,
      pushPriceDrops: pushPriceDrops ?? this.pushPriceDrops,
    );
  }
}
