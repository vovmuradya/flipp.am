import 'dart:convert';

import 'package:http/http.dart' as http;

import 'models/chat.dart';
import 'models/listing.dart';
import 'models/message.dart';
import 'models/profile.dart';

/// Default base URL; override with --dart-define=API_BASE_URL=... when needed.
const String kDefaultApiBaseUrl =
    String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.0.2.2:8000');

/// Optional bearer token; override with --dart-define=API_TOKEN=... or set via [setToken].
const String kDefaultApiToken =
    String.fromEnvironment('API_TOKEN', defaultValue: '');

class ApiClient {
  ApiClient({
    this.baseUrl = kDefaultApiBaseUrl,
    String? token,
  }) : _token = token ?? kDefaultApiToken;

  final String baseUrl;
  String _token;

  void setToken(String? token) {
    _token = token ?? '';
  }

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    return Uri.parse('$baseUrl$path').replace(queryParameters: query);
  }

  Map<String, String> _headers({bool withAuth = false}) {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (withAuth && _token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  Future<Profile> login({
    required String login,
    required String password,
    String? deviceName,
  }) async {
    final resp = await http.post(
      _uri('/api/mobile/auth/login'),
      headers: _headers(),
      body: {
        'login': login,
        'password': password,
        if (deviceName != null) 'device_name': deviceName,
      },
    );
    final body = jsonDecode(resp.body);
    if (resp.statusCode != 200) {
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Login failed (${resp.statusCode})';
      throw Exception(message);
    }
    if (body is! Map) throw Exception('Unexpected login response');
    final token = body['token']?.toString();
    if (token == null || token.isEmpty) {
      throw Exception('Token missing in response');
    }
    _token = token;
    final user = body['user'] is Map<String, dynamic>
        ? body['user'] as Map<String, dynamic>
        : <String, dynamic>{};
    return Profile.fromJson(user);
  }

  bool get hasToken => _token.isNotEmpty;

  Future<List<Listing>> fetchListings({int page = 1}) async {
    final resp = await http.get(
      _uri('/api/mobile/listings', {'page': '$page'}),
      headers: _headers(),
    );
    if (resp.statusCode != 200) {
      throw Exception('Listings request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data
        .whereType<Map<String, dynamic>>()
        .map((e) => Listing.fromJson(e, baseUrl: baseUrl))
        .toList();
  }

  Future<Listing> fetchListing(int id) async {
    final resp = await http.get(
      _uri('/api/mobile/listings/$id'),
      headers: _headers(),
    );
    if (resp.statusCode != 200) {
      throw Exception('Listing $id request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data =
        body is Map && body['data'] is Map ? body['data'] as Map : body;
    if (data is! Map<String, dynamic>) {
      throw Exception('Unexpected listing payload');
    }
    return Listing.fromJson(data, baseUrl: baseUrl);
  }

  Future<bool> toggleFavorite(int id, {required bool favorite}) async {
    if (_token.isEmpty) {
      throw Exception('No auth token set for favorite toggle');
    }
    final uri = _uri('/api/mobile/listings/$id/favorite');
    final resp = favorite
        ? await http.post(uri, headers: _headers(withAuth: true))
        : await http.delete(uri, headers: _headers(withAuth: true));
    return resp.statusCode == 200 ||
        resp.statusCode == 201 ||
        resp.statusCode == 204;
  }

  Future<List<Listing>> fetchFavorites() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(
      _uri('/api/mobile/favorites'),
      headers: _headers(withAuth: true),
    );
    if (resp.statusCode != 200) {
      throw Exception('Favorites request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data
        .whereType<Map<String, dynamic>>()
        .map((e) => Listing.fromJson(e, baseUrl: baseUrl))
        .toList();
  }

  Future<Profile> fetchProfile() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(
      _uri('/api/mobile/profile'),
      headers: _headers(withAuth: true),
    );
    if (resp.statusCode != 200) {
      throw Exception('Profile request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data =
        body is Map && body['data'] is Map ? body['data'] as Map : body;
    if (data is! Map<String, dynamic>) throw Exception('Unexpected profile');
    return Profile.fromJson(data);
  }

  Future<List<ChatSummary>> fetchChats() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(
      _uri('/api/mobile/chats'),
      headers: _headers(withAuth: true),
    );
    if (resp.statusCode != 200) {
      throw Exception('Chats request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data
        .whereType<Map<String, dynamic>>()
        .map(ChatSummary.fromJson)
        .toList();
  }

  Future<List<ChatMessage>> fetchListingMessages({
    required int listingId,
    String? myUserId,
  }) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(
      _uri('/api/mobile/listings/$listingId/messages'),
      headers: _headers(withAuth: true),
    );
    if (resp.statusCode != 200) {
      throw Exception('Messages request failed (${resp.statusCode})');
    }
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data
        .whereType<Map<String, dynamic>>()
        .map((e) => ChatMessage.fromJson(e, myId: myUserId))
        .toList();
  }

  Future<void> sendListingMessage({
    required int listingId,
    required String body,
  }) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.post(
      _uri('/api/mobile/listings/$listingId/messages'),
      headers: _headers(withAuth: true),
      body: {
        'body': body,
      },
    );
    if (resp.statusCode != 200 && resp.statusCode != 201) {
      throw Exception('Send message failed (${resp.statusCode})');
    }
  }
}
