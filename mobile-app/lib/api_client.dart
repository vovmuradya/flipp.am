import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import 'models/car_brand.dart';
import 'models/category.dart';
import 'models/chat.dart';
import 'models/filters/search_filters.dart';
import 'models/listing.dart';
import 'models/message.dart';
import 'models/notification_settings.dart';
import 'models/profile.dart';
import 'models/review.dart';

/// Default base URL.
/// For local development on Linux/WSL use 'http://localhost:8000'
/// For Android Emulator use 'http://10.0.2.2:8000'
const String kDefaultApiBaseUrl =
    String.fromEnvironment('API_BASE_URL', defaultValue: 'http://localhost:8000');

/// Optional bearer token.
/// You can hardcode your token here for testing if you don't want to pass it via CLI args.
const String kDefaultApiToken =
    String.fromEnvironment('API_TOKEN', defaultValue: '');

class ApiClient {
  ApiClient({
    String? baseUrl,
    String? token,
  }) : _token = token ?? kDefaultApiToken {
     // If no specific URL provided, try to detect platform or use default
     if (baseUrl != null) {
       this.baseUrl = baseUrl;
     } else if (kDefaultApiBaseUrl != 'http://localhost:8000') {
       // If it was overridden by compile-time flag, use it
       this.baseUrl = kDefaultApiBaseUrl;
     } else {
       // Smart default based on platform
       try {
         if (Platform.isAndroid) {
           this.baseUrl = 'http://10.0.2.2:8000';
         } else {
           this.baseUrl = 'http://localhost:8000';
         }
       } catch (e) {
         // Fallback for web or other
         this.baseUrl = 'http://localhost:8000';
       }
     }
  }

  late final String baseUrl;
  String _token;

  String get token => _token;

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

  Future<void> createListing({
    required Map<String, String> fields,
    List<String>? filePaths,
  }) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final uri = _uri('/api/mobile/listings');
    final request = http.MultipartRequest('POST', uri);
    request.headers.addAll(_headers(withAuth: true));

    // Add text fields
    request.fields.addAll(fields);

    // Add files
    if (filePaths != null) {
      for (final path in filePaths) {
        if (path.isEmpty) continue;
        final file = await http.MultipartFile.fromPath('images[]', path);
        request.files.add(file);
      }
    }

    final streamResp = await request.send();
    final resp = await http.Response.fromStream(streamResp);

    if (resp.statusCode != 200 && resp.statusCode != 201) {
      final body = jsonDecode(resp.body);
      final msg = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Create listing failed (${resp.statusCode})';
      throw Exception(msg);
    }
  }

  Future<Map<String, dynamic>> fetchFromAuctionUrl(String url) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.post(
      _uri('/api/v1/dealer/listings/fetch-from-url'),
      headers: _headers(withAuth: true),
      body: {'url': url},
    );
    final body = jsonDecode(resp.body);
    if (resp.statusCode != 200) {
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Fetch failed (${resp.statusCode})';
      throw Exception(message);
    }
    if (body is! Map) throw Exception('Unexpected auction response');
    return body as Map<String, dynamic>;
  }

  Future<List<Category>> fetchRootCategories() async {
    final resp = await http.get(_uri('/api/categories/root'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Categories failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Category.fromJson(e)).toList();
  }

  Future<List<Category>> fetchCategoryChildren(int categoryId) async {
    final resp = await http.get(_uri('/api/categories/$categoryId/children'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Category children failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Category.fromJson(e)).toList();
  }

  Future<List<CategoryField>> fetchCategoryFields(int categoryId) async {
    final resp = await http.get(_uri('/api/categories/$categoryId/fields'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Category fields failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => CategoryField.fromJson(e)).toList();
  }

  Future<List<CarBrand>> fetchCarBrands() async {
    final resp = await http.get(_uri('/api/brands'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Brands failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => CarBrand.fromJson(e)).toList();
  }

  Future<List<CarModel>> fetchCarModels(int brandId) async {
    final resp = await http.get(_uri('/api/brands/$brandId/models'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Models failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => CarModel.fromJson(e)).toList();
  }

  Future<List<CarGeneration>> fetchCarGenerations(int modelId) async {
    final resp = await http.get(_uri('/api/models/$modelId/generations'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Generations failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => CarGeneration.fromJson(e)).toList();
  }

  Future<List<Listing>> searchListings(SearchFilters filters, {int page = 1}) async {
    final params = filters.toQueryParams();
    params['page'] = '$page';
    final resp = await http.get(_uri('/api/mobile/listings', params), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Search failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Listing.fromJson(e, baseUrl: baseUrl)).toList();
  }

  Future<List<Listing>> fetchMyListings() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(_uri('/api/mobile/my/listings'), headers: _headers(withAuth: true));
    if (resp.statusCode != 200) throw Exception('My listings failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Listing.fromJson(e, baseUrl: baseUrl)).toList();
  }

  Future<List<Listing>> fetchMyAuctions() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(_uri('/api/mobile/my/auctions'), headers: _headers(withAuth: true));
    if (resp.statusCode != 200) throw Exception('My auctions failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Listing.fromJson(e, baseUrl: baseUrl)).toList();
  }

  Future<void> updateListing({required int id, required Map<String, String> fields, List<String>? filePaths}) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final uri = _uri('/api/mobile/listings/$id');
    final request = http.MultipartRequest('POST', uri);
    request.headers.addAll(_headers(withAuth: true));
    fields['_method'] = 'PUT';
    request.fields.addAll(fields);
    if (filePaths != null) {
      for (final path in filePaths) {
        if (path.isEmpty) continue;
        final file = await http.MultipartFile.fromPath('images[]', path);
        request.files.add(file);
      }
    }
    final streamResp = await request.send();
    final resp = await http.Response.fromStream(streamResp);
    if (resp.statusCode != 200) {
      final body = jsonDecode(resp.body);
      final msg = body is Map && body['message'] != null ? body['message'].toString() : 'Update failed';
      throw Exception(msg);
    }
  }

  Future<void> deleteListing(int id) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.delete(_uri('/api/mobile/listings/$id'), headers: _headers(withAuth: true));
    if (resp.statusCode != 200 && resp.statusCode != 204) throw Exception('Delete failed');
  }

  Future<void> bumpListing(int id) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.post(_uri('/api/mobile/listings/$id/bump'), headers: _headers(withAuth: true));
    if (resp.statusCode != 200) {
      final body = jsonDecode(resp.body);
      final msg = body is Map && body['message'] != null ? body['message'].toString() : 'Bump failed';
      throw Exception(msg);
    }
  }

  Future<List<Review>> fetchReviews(int sellerId) async {
    final resp = await http.get(_uri('/api/sellers/$sellerId/reviews'), headers: _headers());
    if (resp.statusCode != 200) throw Exception('Reviews failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is List ? body['data'] as List : [];
    return data.whereType<Map<String, dynamic>>().map((e) => Review.fromJson(e)).toList();
  }

  Future<void> createReview({required int sellerId, required int rating, String? comment}) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.post(
      _uri('/api/sellers/$sellerId/reviews'),
      headers: _headers(withAuth: true),
      body: {'rating': rating.toString(), if (comment != null) 'comment': comment},
    );
    if (resp.statusCode != 200 && resp.statusCode != 201) throw Exception('Create review failed');
  }

  Future<NotificationSettings> fetchNotificationSettings() async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.get(_uri('/api/mobile/notification-settings'), headers: _headers(withAuth: true));
    if (resp.statusCode != 200) throw Exception('Notification settings failed');
    final body = jsonDecode(resp.body);
    final data = body is Map && body['data'] is Map ? body['data'] as Map : body;
    if (data is! Map<String, dynamic>) throw Exception('Unexpected response');
    return NotificationSettings.fromJson(data);
  }

  Future<void> updateNotificationSettings(NotificationSettings settings) async {
    if (_token.isEmpty) throw Exception('No auth token set');
    final resp = await http.put(
      _uri('/api/mobile/notification-settings'),
      headers: {..._headers(withAuth: true), 'Content-Type': 'application/json'},
      body: jsonEncode(settings.toJson()),
    );
    if (resp.statusCode != 200) throw Exception('Update settings failed');
  }

  Future<Map<String, dynamic>> calculateImport(Map<String, String> params) async {
    final resp = await http.post(
      _uri('/api/copart-calculator/calculate'),
      headers: _headers(),
      body: params,
    );
    if (resp.statusCode != 200) {
      final body = jsonDecode(resp.body);
      final msg = body is Map && body['message'] != null ? body['message'].toString() : 'Calculation failed';
      throw Exception(msg);
    }
    final body = jsonDecode(resp.body);
    return body is Map<String, dynamic> ? body : {};
  }

  Future<Profile> loginWithGoogle(String idToken) async {
    final resp = await http.post(
      _uri('/api/mobile/auth/google'),
      headers: {..._headers(), 'Content-Type': 'application/json'},
      body: jsonEncode({'id_token': idToken}),
    );
    if (resp.statusCode != 200 && resp.statusCode != 201) {
      final body = jsonDecode(resp.body);
      final msg = body is Map && body['message'] != null ? body['message'].toString() : 'Google login failed';
      throw Exception(msg);
    }
    final body = jsonDecode(resp.body);
    if (body is! Map<String, dynamic>) throw Exception('Unexpected response');
    
    final token = body['token']?.toString();
    if (token != null && token.isNotEmpty) {
      setToken(token);
    }
    
    final userData = body['user'] ?? body;
    return Profile.fromJson(userData as Map<String, dynamic>);
  }
}
