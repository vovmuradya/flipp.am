class Category {
  final int id;
  final String name;
  final String? slug;
  final int? parentId;
  final String? listingType; // 'vehicle', 'parts', or null for both
  final List<Category> children;
  final List<CategoryField> fields;

  const Category({
    required this.id,
    required this.name,
    this.slug,
    this.parentId,
    this.listingType,
    this.children = const [],
    this.fields = const [],
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    final childrenList = json['children'] as List?;
    final fieldsList = json['fields'] as List?;

    return Category(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
      parentId: json['parent_id'] is int
          ? json['parent_id']
          : int.tryParse('${json['parent_id']}'),
      listingType: json['listing_type']?.toString(),
      children: childrenList
              ?.whereType<Map<String, dynamic>>()
              .map((e) => Category.fromJson(e))
              .toList() ??
          [],
      fields: fieldsList
              ?.whereType<Map<String, dynamic>>()
              .map((e) => CategoryField.fromJson(e))
              .toList() ??
          [],
    );
  }
}

class CategoryField {
  final int id;
  final String name;
  final String type;
  final bool required;
  final Map<String, dynamic>? options;

  const CategoryField({
    required this.id,
    required this.name,
    required this.type,
    this.required = false,
    this.options,
  });

  factory CategoryField.fromJson(Map<String, dynamic> json) {
    return CategoryField(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'text',
      required: json['required'] == true,
      options: json['options'] is Map<String, dynamic>
          ? json['options'] as Map<String, dynamic>
          : null,
    );
  }
}
