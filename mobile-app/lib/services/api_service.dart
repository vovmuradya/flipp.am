import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:8000';
  
  String? _token;
  
  Future<void> _saveToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    debugPrint('💾 Token saved');
  }
  
  Future<String?> _getToken() async {
    if (_token != null) return _token;
    
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    return _token;
  }
  
  Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }
  
  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    debugPrint('🔐 Logging in: $email');
    
    final response = await http.post(
      Uri.parse('$baseUrl/api/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: json.encode({'email': email, 'password': password}),
    );
    
    debugPrint('📡 Login response: ${response.statusCode}');
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      if (data['token'] != null) {
        await _saveToken(data['token']);
        debugPrint('✅ Login successful');
        return data;
      }
      throw Exception('Token missing in response');
    }
    
    throw Exception('Login failed: ${response.body}');
  }
  
  // Register
  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/register'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: json.encode({'name': name, 'email': email, 'password': password}),
    );
    
    if (response.statusCode == 200 || response.statusCode == 201) {
      final data = json.decode(response.body);
      if (data['token'] != null) {
        await _saveToken(data['token']);
        return data;
      }
      throw Exception('Token missing in response');
    }
    
    throw Exception('Registration failed: ${response.body}');
  }
  
  // Get current user
  Future<Map<String, dynamic>> getCurrentUser() async {
    final token = await _getToken();
    if (token == null) throw Exception('Not authenticated');
    
    final response = await http.get(
      Uri.parse('$baseUrl/api/user'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    
    throw Exception('Failed to get user');
  }
  
  // Create listing (simple)
  Future<Map<String, dynamic>> createListing(Map<String, dynamic> listingData) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');
    
    final response = await http.post(
      Uri.parse('$baseUrl/api/listings'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode(listingData),
    );
    
    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    }
    
    final error = json.decode(response.body);
    throw Exception(error['message'] ?? 'Failed to create listing');
  }
  
  // Import from List.am (using Laravel web route)
  Future<Map<String, dynamic>> importFromListAm(String url) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');
    
    debugPrint('🔗 Importing from List.am: $url');
    
    final response = await http.post(
      Uri.parse('$baseUrl/api/listings/import-listam'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode({'url': url}),
    );
    
    debugPrint('📡 Response: ${response.statusCode}');
    debugPrint('📄 Body: ${response.body}');
    
    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    }
    
    if (response.statusCode == 422) {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Validation failed');
    }
    
    throw Exception('Failed to import from List.am');
  }
  
  // Import from Copart (using API route with async polling)
  Future<Map<String, dynamic>> importFromCopart(String url) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');
    
    debugPrint('🚗 Importing from Copart: $url');
    
    // Step 1: Start the parsing job
    final response = await http.post(
      Uri.parse('$baseUrl/api/listings/import-copart'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode({'url': url}),
    );
    
    debugPrint('📡 Response: ${response.statusCode}');
    debugPrint('📄 Body: ${response.body}');
    
    if (response.statusCode == 422) {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Не удалось распознать ссылку Copart');
    }
    
    if (response.statusCode != 200 && response.statusCode != 201) {
      throw Exception('Ошибка при импорте с Copart');
    }
    
    final data = json.decode(response.body);
    
    // Check if it's async response with job_id
    if (data['job_id'] != null) {
      debugPrint('⏳ Async job started: ${data['job_id']}');
      return await _pollJobStatus(data['job_id']);
    }
    
    // Synchronous response
    return data;
  }
  
  // Poll job status until completion
  Future<Map<String, dynamic>> _pollJobStatus(String jobId) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');
    
    const maxAttempts = 60; // 60 seconds max (increased from 30)
    const pollInterval = Duration(seconds: 1);
    
    for (var attempt = 0; attempt < maxAttempts; attempt++) {
      debugPrint('🔄 Checking status (attempt ${attempt + 1}/$maxAttempts)...');
      
      final response = await http.post(
        Uri.parse('$baseUrl/api/listings/check-status/$jobId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({'job_id': jobId}),
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final status = data['status'];
        
        debugPrint('📊 Job status: $status');
        
        if (status == 'completed' && data['data'] != null) {
          debugPrint('✅ Parsing completed!');
          return data['data'];
        }
        
        if (status == 'failed') {
          throw Exception(data['error'] ?? 'Не удалось загрузить данные с аукциона');
        }
        
        // Still processing, wait and retry
        await Future.delayed(pollInterval);
      } else {
        throw Exception('Ошибка проверки статуса парсинга');
      }
    }
    
    throw Exception('Превышено время ожидания парсинга');
  }
  
  // Get all listings
  Future<List<dynamic>> getListings() async {
    final token = await _getToken();
    if (token == null) return [];
    
    final response = await http.get(
      Uri.parse('$baseUrl/api/listings'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'] ?? [];
    }
    
    return [];
  }

  // Import from auction (Copart)
  Future<Map<String, dynamic>> importFromAuction(String url) async {
    return await importFromCopart(url);
  }

  // Import from external (List.am)
  Future<Map<String, dynamic>> importFromExternal(String url) async {
    return await importFromListAm(url);
  }
}
