import 'package:flutter/material.dart';
import '../api_client.dart';
import '../models/notification_settings.dart';

class SettingsScreen extends StatefulWidget {
  final ApiClient api;

  const SettingsScreen({super.key, required this.api});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  NotificationSettings? _settings;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final settings = await widget.api.fetchNotificationSettings();
      setState(() {
        _settings = settings;
      });
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst(RegExp('^Exception: '), '');
      });
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  Future<void> _saveSettings() async {
    if (_settings == null) return;

    setState(() => _loading = true);

    try {
      await widget.api.updateNotificationSettings(_settings!);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Settings saved')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed: $e')),
        );
      }
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notification Settings'),
      ),
      body: _loading && _settings == null
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Text('Email Notifications', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      title: const Text('Messages'),
                      value: _settings!.emailMessages,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(emailMessages: v)),
                    ),
                    SwitchListTile(
                      title: const Text('New Listings'),
                      value: _settings!.emailNewListings,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(emailNewListings: v)),
                    ),
                    SwitchListTile(
                      title: const Text('Price Drops'),
                      value: _settings!.emailPriceDrops,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(emailPriceDrops: v)),
                    ),
                    const Divider(height: 32),
                    const Text('Push Notifications', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      title: const Text('Messages'),
                      value: _settings!.pushMessages,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(pushMessages: v)),
                    ),
                    SwitchListTile(
                      title: const Text('New Listings'),
                      value: _settings!.pushNewListings,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(pushNewListings: v)),
                    ),
                    SwitchListTile(
                      title: const Text('Price Drops'),
                      value: _settings!.pushPriceDrops,
                      onChanged: (v) => setState(() => _settings = _settings!.copyWith(pushPriceDrops: v)),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _loading ? null : _saveSettings,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        backgroundColor: Theme.of(context).colorScheme.primary,
                        foregroundColor: Colors.white,
                      ),
                      child: _loading
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Text('Save Settings'),
                    ),
                  ],
                ),
    );
  }
}
