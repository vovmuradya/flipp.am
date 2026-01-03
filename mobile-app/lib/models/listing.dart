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
  final String? bodyType;
  final String? fuelType;
  final String? operationalStatus;
  final String? auctionUrl;
  final String? auctionEndsAt;
  final double? buyNowPrice;
  final String? buyNowCurrency;
  final double? currentBidPrice;
  final String? currentBidCurrency;
  final bool isFromAuction;
  final String? make;
  final String? model;
  final String? engineDisplacementCc;
  final List<String> auctionPhotoUrls;
  final int? viewsCount;
  final String? status;
  final String? listingType;
  final int? categoryId;
  final int? regionId;
  final String? slug;
  final String? language;
  final DateTime? promotedUntil;
  final DateTime? lastBumpedAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;

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
    this.bodyType,
    this.fuelType,
    this.operationalStatus,
    this.auctionUrl,
    this.auctionEndsAt,
    this.buyNowPrice,
    this.buyNowCurrency,
    this.currentBidPrice,
    this.currentBidCurrency,
    this.isFromAuction = false,
    this.make,
    this.model,
    this.engineDisplacementCc,
    this.auctionPhotoUrls = const [],
    this.viewsCount,
    this.status,
    this.listingType,
    this.categoryId,
    this.regionId,
    this.slug,
    this.language,
    this.promotedUntil,
    this.lastBumpedAt,
    this.createdAt,
    this.updatedAt,
  });

  Listing copyWith({
    bool? isFavorite,
    String? title,
    String? priceDisplay,
    String? imageUrl,
    String? priceCurrency,
    double? priceAmount,
    String? location,
    String? mileage,
    String? year,
    String? transmission,
    String? engine,
    String? exteriorColor,
    String? interiorColor,
    String? primaryDamage,
    String? description,
    String? bodyType,
    String? fuelType,
    String? operationalStatus,
    String? auctionUrl,
    String? auctionEndsAt,
    double? buyNowPrice,
    String? buyNowCurrency,
    double? currentBidPrice,
    String? currentBidCurrency,
    bool? isFromAuction,
    String? make,
    String? model,
    String? engineDisplacementCc,
    List<String>? auctionPhotoUrls,
    int? viewsCount,
    String? status,
    String? listingType,
    int? categoryId,
    int? regionId,
    String? slug,
    String? language,
    DateTime? promotedUntil,
    DateTime? lastBumpedAt,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Listing(
      id: id,
      title: title ?? this.title,
      priceDisplay: priceDisplay ?? this.priceDisplay,
      priceCurrency: priceCurrency ?? this.priceCurrency,
      priceAmount: priceAmount ?? this.priceAmount,
      imageUrl: imageUrl ?? this.imageUrl,
      isFavorite: isFavorite ?? this.isFavorite,
      location: location ?? this.location,
      mileage: mileage ?? this.mileage,
      year: year ?? this.year,
      transmission: transmission ?? this.transmission,
      engine: engine ?? this.engine,
      exteriorColor: exteriorColor ?? this.exteriorColor,
      interiorColor: interiorColor ?? this.interiorColor,
      primaryDamage: primaryDamage ?? this.primaryDamage,
      description: description ?? this.description,
      bodyType: bodyType ?? this.bodyType,
      fuelType: fuelType ?? this.fuelType,
      operationalStatus: operationalStatus ?? this.operationalStatus,
      auctionUrl: auctionUrl ?? this.auctionUrl,
      auctionEndsAt: auctionEndsAt ?? this.auctionEndsAt,
      buyNowPrice: buyNowPrice ?? this.buyNowPrice,
      buyNowCurrency: buyNowCurrency ?? this.buyNowCurrency,
      currentBidPrice: currentBidPrice ?? this.currentBidPrice,
      currentBidCurrency: currentBidCurrency ?? this.currentBidCurrency,
      isFromAuction: isFromAuction ?? this.isFromAuction,
      make: make ?? this.make,
      model: model ?? this.model,
      engineDisplacementCc: engineDisplacementCc ?? this.engineDisplacementCc,
      auctionPhotoUrls: auctionPhotoUrls ?? this.auctionPhotoUrls,
      viewsCount: viewsCount ?? this.viewsCount,
      status: status ?? this.status,
      listingType: listingType ?? this.listingType,
      categoryId: categoryId ?? this.categoryId,
      regionId: regionId ?? this.regionId,
      slug: slug ?? this.slug,
      language: language ?? this.language,
      promotedUntil: promotedUntil ?? this.promotedUntil,
      lastBumpedAt: lastBumpedAt ?? this.lastBumpedAt,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
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
    final auctionPhotoUrls = json['auction_photo_urls'] as List? ?? [];

    final photoUrlRaw = _firstString(
          photos is Map ? photos['primary'] : null,
        ) ??
        _firstString(photos is Map ? photos['main_image_url'] : null) ??
        _firstString(photos is Map ? photos['all'] : null) ??
        _firstString(auctionPhotoUrls) ??
        _firstString(json['preview_image_url']) ??
        _firstString(json['main_image_url']) ??
        '';

    final priceDisplay = _formatMoney(price?['amount'], price?['currency']) ?? '—';

    // Extract auction-specific data
    final buyNowPrice = (json['buy_now_price'] is num ? (json['buy_now_price'] as num).toDouble() : double.tryParse('${json['buy_now_price']}'));
    final currentBidPrice = (json['current_bid_price'] is num ? (json['current_bid_price'] as num).toDouble() : double.tryParse('${json['current_bid_price']}'));

    // Parse auction photo URLs
    final List<String> parsedAuctionPhotoUrls = auctionPhotoUrls
        .whereType<String>()
        .map((url) => _absolute(url, baseUrl))
        .toList();

    return Listing(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      title: json['title']?.toString() ?? '—',
      priceDisplay: priceDisplay,
      priceCurrency: price?['currency']?.toString(),
      priceAmount: price?['amount'] is num ? (price?['amount'] as num).toDouble() : double.tryParse('${price?['amount']}'),
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
      // Additional fields from vehicle_details
      bodyType: vehicle['body_type']?.toString(),
      fuelType: vehicle['fuel_type']?.toString(),
      operationalStatus: vehicle['operational_status']?.toString(),
      auctionUrl: vehicle['source_auction_url']?.toString(),
      auctionEndsAt: vehicle['auction_ends_at']?.toString(),
      buyNowPrice: buyNowPrice,
      buyNowCurrency: json['buy_now_currency']?.toString(),
      currentBidPrice: currentBidPrice,
      currentBidCurrency: json['current_bid_currency']?.toString(),
      isFromAuction: json['is_from_auction'] == true,
      make: vehicle['make']?.toString(),
      model: vehicle['model']?.toString(),
      engineDisplacementCc: vehicle['engine_displacement_cc']?.toString(),
      auctionPhotoUrls: parsedAuctionPhotoUrls,
      viewsCount: json['views_count'] is int ? json['views_count'] as int : int.tryParse('${json['views_count']}'),
      status: json['status']?.toString(),
      listingType: json['listing_type']?.toString(),
      categoryId: json['category_id'] is int ? json['category_id'] as int : int.tryParse('${json['category_id']}'),
      regionId: json['region_id'] is int ? json['region_id'] as int : int.tryParse('${json['region_id']}'),
      slug: json['slug']?.toString(),
      language: json['language']?.toString(),
      promotedUntil: json['promoted_until'] != null ? DateTime.tryParse('${json['promoted_until']}') : null,
      lastBumpedAt: json['last_bumped_at'] != null ? DateTime.tryParse('${json['last_bumped_at']}') : null,
      createdAt: json['created_at'] != null ? DateTime.tryParse('${json['created_at']}') : null,
      updatedAt: json['updated_at'] != null ? DateTime.tryParse('${json['updated_at']}') : null,
    );
  }
}
