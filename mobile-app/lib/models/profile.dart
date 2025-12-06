class Profile {
  final int id;
  final String name;
  final String? email;
  final String? phone;
  final String? role;

  const Profile({
    required this.id,
    required this.name,
    this.email,
    this.phone,
    this.role,
  });

  factory Profile.fromJson(Map<String, dynamic> json) {
    final idValue = json['id'];
    return Profile(
      id: idValue is int ? idValue : int.tryParse('$idValue') ?? 0,
      name: json['name']?.toString() ?? 'User',
      email: json['email']?.toString(),
      phone: json['phone']?.toString(),
      role: json['role']?.toString(),
    );
  }
}
