class Region {
  final int id;
  final String name;
  final String? nameRu;
  final String? nameAm;
  final String? nameEn;

  const Region({
    required this.id,
    required this.name,
    this.nameRu,
    this.nameAm,
    this.nameEn,
  });

  factory Region.fromJson(Map<String, dynamic> json) {
    return Region(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      nameRu: json['name_ru']?.toString(),
      nameAm: json['name_am']?.toString(),
      nameEn: json['name_en']?.toString(),
    );
  }
}
