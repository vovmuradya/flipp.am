class Listing {
  final int id;
  final String title;
  final String priceDisplay;
  final String? priceCurrency;
  final double? priceAmount;
  final String imageUrl;
  final bool isFavorite;
  final String? location;
  final String? mileage;
  final String? year;
  final String? transmission;
  final String? engine;
  final String? exteriorColor;
  final String? interiorColor;
  final String? primaryDamage;
  final String? description;

  const Listing({
    required this.id,
    required this.title,
    required this.priceDisplay,
    required this.imageUrl,
    this.priceCurrency,
    this.priceAmount,
    this.isFavorite = false,
    this.location,
    this.mileage,
    this.year,
    this.transmission,
    this.engine,
    this.exteriorColor,
    this.interiorColor,
    this.primaryDamage,
    this.description,
  });

  Listing copyWith({bool? isFavorite}) {
    return Listing(
      id: id,
      title: title,
      priceDisplay: priceDisplay,
      priceCurrency: priceCurrency,
      priceAmount: priceAmount,
      imageUrl: imageUrl,
      isFavorite: isFavorite ?? this.isFavorite,
      location: location,
      mileage: mileage,
      year: year,
      transmission: transmission,
      engine: engine,
      exteriorColor: exteriorColor,
      interiorColor: interiorColor,
      primaryDamage: primaryDamage,
      description: description,
    );
  }

  static String? _firstString(dynamic value) {
    if (value is String && value.isNotEmpty) return value;
    if (value is List && value.isNotEmpty) {
      final first = value.first;
      if (first is String && first.isNotEmpty) return first;
    }
    return null;
  }

  static String? _formatMoney(dynamic amount, dynamic currency) {
    if (amount == null) return null;
    final num? parsed = amount is num ? amount : num.tryParse('$amount');
    if (parsed == null) return null;
    final formatted = parsed.toStringAsFixed(parsed.truncateToDouble() == parsed ? 0 : 2);
    final code = currency?.toString().toUpperCase() ?? '';
    return code.isEmpty ? formatted : '$formatted $code';
  }

  static String? _formatMileage(dynamic raw) {
    if (raw == null) return null;
    final num? parsed = raw is num ? raw : num.tryParse('$raw');
    if (parsed == null) return raw.toString();
    return '${parsed.toStringAsFixed(0)} km';
  }

  static String _absolute(String url, String? baseUrl) {
    if (url.isEmpty) return '';
    if (url.startsWith('http')) return url;
    if (baseUrl == null || baseUrl.isEmpty) return url;
    final normalizedBase = baseUrl.endsWith('/')
        ? baseUrl.substring(0, baseUrl.length - 1)
        : baseUrl;
    return '$normalizedBase$url';
  }

  factory Listing.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final photos = json['photos'];
    final vehicle = (json['vehicle_details'] as Map?) ?? <String, dynamic>{};
    final price = json['price'] as Map?;
    final auctionPhotoUrls = json['auction_photo_urls'];

    final photoUrlRaw = _firstString(
          photos is Map ? photos['primary'] : null,
        ) ??
        _firstString(photos is Map ? photos['main_image_url'] : null) ??
        _firstString(photos is Map ? photos['all'] : null) ??
        _firstString(auctionPhotoUrls) ??
        _firstString(json['preview_image_url']) ??
        _firstString(json['main_image_url']) ??
        '';

    final priceDisplay =
        _formatMoney(price?['amount'], price?['currency']) ?? '—';

    return Listing(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      title: json['title']?.toString() ?? '—',
      priceDisplay: priceDisplay,
      priceCurrency: price?['currency']?.toString(),
      priceAmount:
          price?['amount'] is num ? (price?['amount'] as num).toDouble() : double.tryParse('${price?['amount']}'),
      imageUrl: _absolute(photoUrlRaw, baseUrl),
      isFavorite: json['is_favorite'] == true,
      location: (json['region'] is Map ? json['region']['name'] : null)?.toString(),
      mileage: _formatMileage(vehicle['mileage']),
      year: vehicle['year']?.toString(),
      transmission: vehicle['transmission']?.toString(),
      engine: vehicle['engine_displacement_cc']?.toString(),
      exteriorColor: vehicle['exterior_color']?.toString(),
      interiorColor: vehicle['interior_color']?.toString(),
      primaryDamage: vehicle['primary_damage']?.toString(),
      description: json['description']?.toString(),
    );
  }
}
