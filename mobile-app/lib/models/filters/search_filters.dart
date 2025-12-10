class SearchFilters {
  final String? query;
  final int? categoryId;
  final int? regionId;
  final double? minPrice;
  final double? maxPrice;
  final int? minYear;
  final int? maxYear;
  final int? minMileage;
  final int? maxMileage;
  final String? bodyType;
  final String? transmission;
  final String? fuelType;
  final String? condition;
  final bool? isFromAuction;
  final String? sortBy;
  final String? sortOrder;
  final int? brandId;
  final int? modelId;

  const SearchFilters({
    this.query,
    this.categoryId,
    this.regionId,
    this.minPrice,
    this.maxPrice,
    this.minYear,
    this.maxYear,
    this.minMileage,
    this.maxMileage,
    this.bodyType,
    this.transmission,
    this.fuelType,
    this.condition,
    this.isFromAuction,
    this.sortBy,
    this.sortOrder,
    this.brandId,
    this.modelId,
  });

  Map<String, String> toQueryParams() {
    final params = <String, String>{};
    if (query != null && query!.isNotEmpty) params['q'] = query!;
    if (categoryId != null) params['category_id'] = categoryId.toString();
    if (regionId != null) params['region_id'] = regionId.toString();
    if (minPrice != null) params['min_price'] = minPrice.toString();
    if (maxPrice != null) params['max_price'] = maxPrice.toString();
    if (minYear != null) params['min_year'] = minYear.toString();
    if (maxYear != null) params['max_year'] = maxYear.toString();
    if (minMileage != null) params['min_mileage'] = minMileage.toString();
    if (maxMileage != null) params['max_mileage'] = maxMileage.toString();
    if (bodyType != null) params['body_type'] = bodyType!;
    if (transmission != null) params['transmission'] = transmission!;
    if (fuelType != null) params['fuel_type'] = fuelType!;
    if (condition != null) params['condition'] = condition!;
    if (isFromAuction != null) {
      params['is_from_auction'] = isFromAuction! ? '1' : '0';
    }
    if (sortBy != null) params['sort_by'] = sortBy!;
    if (sortOrder != null) params['sort_order'] = sortOrder!;
    if (brandId != null) params['brand_id'] = brandId.toString();
    if (modelId != null) params['model_id'] = modelId.toString();
    return params;
  }

  SearchFilters copyWith({
    String? query,
    int? categoryId,
    int? regionId,
    double? minPrice,
    double? maxPrice,
    int? minYear,
    int? maxYear,
    int? minMileage,
    int? maxMileage,
    String? bodyType,
    String? transmission,
    String? fuelType,
    String? condition,
    bool? isFromAuction,
    String? sortBy,
    String? sortOrder,
    int? brandId,
    int? modelId,
  }) {
    return SearchFilters(
      query: query ?? this.query,
      categoryId: categoryId ?? this.categoryId,
      regionId: regionId ?? this.regionId,
      minPrice: minPrice ?? this.minPrice,
      maxPrice: maxPrice ?? this.maxPrice,
      minYear: minYear ?? this.minYear,
      maxYear: maxYear ?? this.maxYear,
      minMileage: minMileage ?? this.minMileage,
      maxMileage: maxMileage ?? this.maxMileage,
      bodyType: bodyType ?? this.bodyType,
      transmission: transmission ?? this.transmission,
      fuelType: fuelType ?? this.fuelType,
      condition: condition ?? this.condition,
      isFromAuction: isFromAuction ?? this.isFromAuction,
      sortBy: sortBy ?? this.sortBy,
      sortOrder: sortOrder ?? this.sortOrder,
      brandId: brandId ?? this.brandId,
      modelId: modelId ?? this.modelId,
    );
  }

  bool get hasActiveFilters =>
      query != null ||
      categoryId != null ||
      regionId != null ||
      minPrice != null ||
      maxPrice != null ||
      minYear != null ||
      maxYear != null ||
      minMileage != null ||
      maxMileage != null ||
      bodyType != null ||
      transmission != null ||
      fuelType != null ||
      condition != null ||
      isFromAuction != null ||
      brandId != null ||
      modelId != null;
}
