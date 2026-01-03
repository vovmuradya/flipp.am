import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

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

  Future<void> _loadToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedToken = prefs.getString('auth_token');
      if (savedToken != null && savedToken.isNotEmpty) {
        _token = savedToken;
        print('🔑 Loaded saved token');
      }
    } catch (e) {
      print('⚠️ Could not load token: $e');
    }
  }

  /// Public method to load saved token from SharedPreferences
  Future<void> loadSavedToken() async {
    await _loadToken();
  }

  Future<void> _saveToken(String token) async {
    _token = token;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);
      print('💾 Token saved');
    } catch (e) {
      print('⚠️ Could not save token: $e');
    }
  }

  Future<void> setToken(String? token) async {
    if (token == null || token.isEmpty) {
      _token = '';
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('auth_token');
        print('🗑️ Token cleared');
      } catch (e) {
        print('⚠️ Could not clear token: $e');
      }
    } else {
      await _saveToken(token);
    }
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
    
    // Handle response format: { status, message, data: { token, user } }
    final data = body['data'] as Map?;
    final token = (data?['token'] ?? body['token'])?.toString();
    
    if (token == null || token.isEmpty) {
      print('❌ Response body: $body');
      throw Exception('Token missing in response');
    }
    
    await _saveToken(token);
    
    final userData = data?['user'] ?? body['user'];
    final user = userData is Map<String, dynamic>
        ? userData
        : <String, dynamic>{};
    
    print('✅ Login successful: ${user['name']}');
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

  // Fetch auction data from Copart URL (step 1)
  Future<Map<String, dynamic>> fetchFromAuctionUrl(String url) async {
    print('🔍 Fetching auction data from: $url');
    print('🔑 Using token: ${_token.isEmpty ? "NO TOKEN" : "EXISTS"}');
    
    if (_token.isEmpty) {
      throw Exception('Please login to import from auctions');
    }
    
    // Step 1: Start async parsing job
    final resp = await http.post(
      _uri('/api/v1/dealer/listings/fetch-from-url'),
      headers: {..._headers(withAuth: true), 'Content-Type': 'application/json'},
      body: jsonEncode({'url': url}),
    );
    
    print('📡 Response status: ${resp.statusCode}');
    print('📄 Response body: ${resp.body}');
    
    if (resp.statusCode == 401 || resp.statusCode == 403) {
      throw Exception('Please login to import from auctions');
    }
    
    final body = jsonDecode(resp.body);
    
    if (resp.statusCode != 200 && resp.statusCode != 201) {
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Fetch failed (${resp.statusCode})';
      throw Exception(message);
    }
    
    if (body is! Map || body['job_id'] == null) {
      throw Exception('Invalid response from server');
    }
    
    final jobId = body['job_id'] as String;
    print('✅ Parse job started: $jobId');
    
    // Step 2: Poll for completion (max 30 seconds)
    for (var i = 0; i < 30; i++) {
      await Future.delayed(Duration(seconds: 1));
      
      final statusResp = await http.post(
        _uri('/api/v1/dealer/listings/check-parse-status'),
        headers: _headers(withAuth: true),
        body: {'job_id': jobId},
      );
      
      if (statusResp.statusCode != 200) continue;
      
      final statusBody = jsonDecode(statusResp.body);
      final status = statusBody['status'];
      
      print('⏳ Job status: $status (${i+1}s)');
      
      if (status == 'completed') {
        print('✅ Parse completed!');
        return statusBody['data'] as Map<String, dynamic>;
      }
      
      if (status == 'failed') {
        throw Exception(statusBody['error'] ?? 'Parsing failed');
      }
    }
    
    throw Exception('Parsing timeout (30s)');
  }

  // Import from external URL (list.am, auto.am)
  Future<Map<String, dynamic>> importFromUrl(String url, String source) async {
    print('🔗 Importing from: $source ($url)');
    
    if (_token.isEmpty) {
      throw Exception('Please login to import listings');
    }
    
    // Use API endpoint with auth:sanctum
    final resp = await http.post(
      _uri('/api/v1/dealer/listings/import-external'),
      headers: _headers(withAuth: true),
      body: {'url': url},
    );
    
    print('📡 Response status: ${resp.statusCode}');
    
    final body = jsonDecode(resp.body);
    
    if (resp.statusCode == 401 || resp.statusCode == 403) {
      throw Exception('Please login to import listings');
    }
    
    if (resp.statusCode != 200 && resp.statusCode != 201) {
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : body is Map && body['errors'] != null
            ? (body['errors'] as Map).values.first.toString()
            : 'Import failed (${resp.statusCode})';
      throw Exception(message);
    }
    
    if (body is! Map<String, dynamic>) throw Exception('Unexpected response');
    
    return body['data'] ?? body;
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

  Future<void> sendPhoneVerification(String phone) async {
    final resp = await http.post(
      _uri('/api/mobile/auth/phone/send-code'),
      headers: _headers(),
      body: {
        'phone': phone,
      },
    );

    if (resp.statusCode != 200) {
      final body = jsonDecode(resp.body);
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Failed to send verification code (${resp.statusCode})';
      throw Exception(message);
    }
  }

  Future<Profile> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String verificationCode,
  }) async {
    final resp = await http.post(
      _uri('/api/mobile/auth/register'),
      headers: _headers(),
      body: {
        'name': name,
        'email': email,
        'phone': phone,
        'password': password,
        'password_confirmation': password, // Laravel validation requires confirmation
        'verification_code': verificationCode,
      },
    );

    final body = jsonDecode(resp.body);
    if (resp.statusCode != 200 && resp.statusCode != 201) {
      final message = body is Map && body['message'] != null
          ? body['message'].toString()
          : 'Registration failed (${resp.statusCode})';
      throw Exception(message);
    }

    if (body is! Map) throw Exception('Invalid response format');

    // Handle response format: { status, message, data: { token, user } }
    final data = body['data'] as Map?;
    final token = (data?['token'] ?? body['token'])?.toString();

    if (token == null || token.isEmpty) {
      print('❌ Response body: $body');
      throw Exception('Token missing in response');
    }

    await _saveToken(token);

    final userData = data?['user'] ?? body['user'];
    final user = userData is Map<String, dynamic>
        ? userData
        : <String, dynamic>{};

    print('✅ Registration successful: ${user['name']}');
    return Profile.fromJson(user);
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
