import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ImportFromExternalScreen extends StatefulWidget {
  const ImportFromExternalScreen({Key? key}) : super(key: key);

  @override
  State<ImportFromExternalScreen> createState() => _ImportFromExternalScreenState();
}

class _ImportFromExternalScreenState extends State<ImportFromExternalScreen> {
  final _urlController = TextEditingController(text: 'https://www.list.am/ru/item/');
  final _apiService = ApiService();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _importListing() async {
    final url = _urlController.text.trim();
    if (url.isEmpty) {
      setState(() => _errorMessage = 'Введите URL');
      return;
    }

    if (!url.contains('list.am')) {
      setState(() => _errorMessage = 'Поддерживаются только ссылки List.am');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiService.importFromExternal(url);
      
      if (!mounted) return;
      
      // Перейти на экран создания/редактирования с предзаполненными данными
      Navigator.pushReplacementNamed(
        context,
        '/listings/create',
        arguments: result,
      );
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('✅ Данные импортированы успешно!')),
      );
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceAll('Exception: ', '');
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Импорт с List.am'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Вставьте ссылку List.am для импорта объявления',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _urlController,
              decoration: InputDecoration(
                labelText: 'URL с List.am',
                hintText: 'https://www.list.am/ru/item/...',
                border: const OutlineInputBorder(),
                prefixIcon: const Icon(Icons.link),
                errorText: _errorMessage,
              ),
              keyboardType: TextInputType.url,
              enabled: !_isLoading,
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _importListing,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue,
                  foregroundColor: Colors.white,
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Text('Импортировать объявление', style: TextStyle(fontSize: 16)),
              ),
            ),
            const SizedBox(height: 32),
            Card(
              color: Colors.blue.shade50,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: const [
                        Icon(Icons.info_outline, color: Colors.blue),
                        SizedBox(width: 8),
                        Text(
                          'Как это работает:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _buildStep('1', 'Скопируйте ссылку с List.am'),
                    _buildStep('2', 'Вставьте её сюда'),
                    _buildStep('3', 'Мы автоматически импортируем все данные'),
                    _buildStep('4', 'Проверьте и опубликуйте'),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStep(String number, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Container(
            width: 24,
            height: 24,
            decoration: BoxDecoration(
              color: Colors.blue,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Text(
                number,
                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}
