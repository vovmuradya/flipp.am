import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../api_client.dart';
import 'create_listing_screen.dart';

class ImportFromAuctionScreen extends StatefulWidget {
  final ApiClient? api;
  
  const ImportFromAuctionScreen({Key? key, this.api}) : super(key: key);

  @override
  State<ImportFromAuctionScreen> createState() => _ImportFromAuctionScreenState();
}

class _ImportFromAuctionScreenState extends State<ImportFromAuctionScreen> {
  final _urlController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _importAuction() async {
    var url = _urlController.text.trim();
    if (url.isEmpty) {
      setState(() => _errorMessage = 'Введите URL аукциона');
      return;
    }

    // Ensure proper URL format
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      url = 'https://www.copart.com/$url';
    }
    
    if (!url.contains('copart.com')) {
      setState(() => _errorMessage = 'Поддерживаются только аукционы Copart');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiService.importFromAuction(url);
      
      if (!mounted) return;
      
      // Check if we got valid data
      if (result['make'] == null && result['model'] == null) {
        setState(() {
          _errorMessage = 'Не удалось найти данные по этой ссылке. Проверьте URL или попробуйте позже.';
          _isLoading = false;
        });
        return;
      }
      
      // Go to create listing screen WITHOUT replacing (keep this screen in stack)
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => CreateListingScreen(
            api: widget.api ?? ApiClient(),
            type: 'auction',
            initialData: result,
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
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
        title: const Text('Импорт из Copart'),
        backgroundColor: Colors.orange,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Вставьте ссылку на аукцион Copart',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _urlController,
              decoration: InputDecoration(
                labelText: 'URL аукциона Copart',
                hintText: 'https://www.copart.com/lot/...',
                border: const OutlineInputBorder(),
                prefixIcon: const Icon(Icons.gavel),
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
                onPressed: _isLoading ? null : _importAuction,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.orange,
                  foregroundColor: Colors.white,
                ),
                child: _isLoading
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          ),
                          SizedBox(width: 12),
                          Text('Загрузка данных...', style: TextStyle(fontSize: 16)),
                        ],
                      )
                    : const Text('Импортировать с аукциона', style: TextStyle(fontSize: 16)),
              ),
            ),
            const SizedBox(height: 32),
            Card(
              color: Colors.orange.shade50,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: const [
                        Icon(Icons.info_outline, color: Colors.orange),
                        SizedBox(width: 8),
                        Text(
                          'Как это работает:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _buildStep('1', 'Найдите лот на Copart.com'),
                    _buildStep('2', 'Скопируйте ссылку на лот'),
                    _buildStep('3', 'Вставьте её сюда'),
                    _buildStep('4', 'Мы автоматически загрузим все данные, фото и параметры'),
                    _buildStep('5', 'Проверьте и опубликуйте объявление'),
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
              color: Colors.orange,
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
