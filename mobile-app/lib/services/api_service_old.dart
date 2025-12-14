import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:8000';

  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  Future<void> _deleteToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  // Auth
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: json.encode({'email': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      if (data['token'] != null) {
        await _saveToken(data['token']);
        return data;
      }
      throw Exception('Token missing in response');
    } else if (response.statusCode == 401 || response.statusCode == 422) {
      throw Exception('Неверные учётные данные.');
    } else {
      throw Exception('Ошибка входа');
    }
  }

  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/register'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: json.encode({'name': name, 'email': email, 'password': password, 'password_confirmation': password}),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      final data = json.decode(response.body);
      if (data['token'] != null) {
        await _saveToken(data['token']);
      }
      return data;
    } else {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Ошибка регистрации');
    }
  }

  Future<void> logout() async {
    final token = await _getToken();
    if (token != null) {
      await http.post(
        Uri.parse('$baseUrl/api/logout'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
    }
    await _deleteToken();
  }

  Future<Map<String, dynamic>> getProfile() async {
    final token = await _getToken();
    if (token == null) throw Exception('Not authenticated');

    final response = await http.get(
      Uri.parse('$baseUrl/api/user'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to get profile');
    }
  }

  // Listings
  Future<List<dynamic>> getListings() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/listings'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'] ?? [];
    }
    return [];
  }

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
    } else {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Failed to create listing');
    }
  }

  // Import from external site (List.am) - Async Job
  Future<Map<String, dynamic>> importFromExternal(String url) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');

    // Step 1: Start async parsing job
    final response = await http.post(
      Uri.parse('$baseUrl/listings/import-external'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode({'url': url}),
    );

    if (response.statusCode != 200) {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Failed to start import');
    }

    final jobData = json.decode(response.body);
    if (jobData['success'] != true || jobData['job_id'] == null) {
      throw Exception('Invalid job response');
    }

    final jobId = jobData['job_id'];
    debugPrint('✅ Parse job started: $jobId');

    // Step 2: Poll for completion
    for (int attempt = 0; attempt < 30; attempt++) {
      await Future.delayed(const Duration(seconds: 2));

      final statusResp = await http.post(
        Uri.parse('$baseUrl/api/v1/dealer/listings/check-parse-status'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({'job_id': jobId}),
      );

      if (statusResp.statusCode == 200) {
        final statusData = json.decode(statusResp.body);
        final status = statusData['status'];

        debugPrint('⏳ Job status: $status (attempt ${attempt + 1}/30)');

        if (status == 'completed' && statusData['data'] != null) {
          debugPrint('✅ Parse completed!');
          return statusData['data'];
        }

        if (status == 'failed') {
          throw Exception(statusData['error'] ?? 'Parsing failed');
        }
      }
    }

    throw Exception('Import timeout - please try again');
  }

  // Import from auction (Copart) - Async Job
  Future<Map<String, dynamic>> importFromAuction(String url) async {
    final token = await _getToken();
    if (token == null) throw Exception('Please login first');

    // Step 1: Start async parsing job  
    final response = await http.post(
      Uri.parse('$baseUrl/listings/import-auction'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode({'auction_url': url}),
    );

    if (response.statusCode == 401) {
      throw Exception('Please login to import from auctions');
    }

    if (response.statusCode != 200) {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Failed to start import');
    }

    final jobData = json.decode(response.body);
    if (jobData['success'] != true || jobData['job_id'] == null) {
      throw Exception('Invalid job response');
    }

    final jobId = jobData['job_id'];
    debugPrint('✅ Copart parse job started: $jobId');

    // Step 2: Poll for completion
    for (int attempt = 0; attempt < 30; attempt++) {
      await Future.delayed(const Duration(seconds: 2));

      final statusResp = await http.post(
        Uri.parse('$baseUrl/api/v1/dealer/listings/check-parse-status'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({'job_id': jobId}),
      );

      if (statusResp.statusCode == 200) {
        final statusData = json.decode(statusResp.body);
        final status = statusData['status'];

        debugPrint('⏳ Copart job status: $status (attempt ${attempt + 1}/30)');

        if (status == 'completed' && statusData['data'] != null) {
          debugPrint('✅ Copart parse completed!');
          return statusData['data'];
        }

        if (status == 'failed') {
          throw Exception(statusData['error'] ?? 'Parsing failed');
        }
      }
    }

    throw Exception('Import timeout - please try again');
  }
}
