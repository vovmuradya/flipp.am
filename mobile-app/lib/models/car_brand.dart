class CarBrand {
  final int id;
  final String name;
  final String? slug;

  const CarBrand({
    required this.id,
    required this.name,
    this.slug,
  });

  factory CarBrand.fromJson(Map<String, dynamic> json) {
    return CarBrand(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
    );
  }
}

class CarModel {
  final int id;
  final String name;
  final int brandId;
  final String? slug;

  const CarModel({
    required this.id,
    required this.name,
    required this.brandId,
    this.slug,
  });

  factory CarModel.fromJson(Map<String, dynamic> json) {
    return CarModel(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      brandId: json['brand_id'] is int
          ? json['brand_id']
          : int.tryParse('${json['brand_id']}') ?? 0,
      slug: json['slug']?.toString(),
    );
  }
}

class CarGeneration {
  final int id;
  final String name;
  final int modelId;
  final int? yearStart;
  final int? yearEnd;

  const CarGeneration({
    required this.id,
    required this.name,
    required this.modelId,
    this.yearStart,
    this.yearEnd,
  });

  factory CarGeneration.fromJson(Map<String, dynamic> json) {
    return CarGeneration(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      modelId: json['model_id'] is int
          ? json['model_id']
          : int.tryParse('${json['model_id']}') ?? 0,
      yearStart: json['year_start'] is int
          ? json['year_start']
          : int.tryParse('${json['year_start']}'),
      yearEnd: json['year_end'] is int
          ? json['year_end']
          : int.tryParse('${json['year_end']}'),
    );
  }
}
