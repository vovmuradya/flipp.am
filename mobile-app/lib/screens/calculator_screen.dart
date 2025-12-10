import 'package:flutter/material.dart';
import '../api_client.dart';

class CalculatorScreen extends StatefulWidget {
  final ApiClient api;

  const CalculatorScreen({super.key, required this.api});

  @override
  State<CalculatorScreen> createState() => _CalculatorScreenState();
}

class _CalculatorScreenState extends State<CalculatorScreen> {
  final _priceController = TextEditingController();
  final _yearController = TextEditingController();
  bool _loading = false;
  String? _error;
  Map<String, dynamic>? _result;

  @override
  void dispose() {
    _priceController.dispose();
    _yearController.dispose();
    super.dispose();
  }

  Future<void> _calculate() async {
    if (_priceController.text.isEmpty) {
      setState(() => _error = 'Please enter price');
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
      _result = null;
    });

    try {
      final params = <String, String>{
        'auction_price': _priceController.text,
        if (_yearController.text.isNotEmpty) 'year': _yearController.text,
      };

      final result = await widget.api.calculateImport(params);
      setState(() {
        _result = result;
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Import Calculator'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text(
            'Calculate import cost for vehicles from Copart',
            style: TextStyle(fontSize: 14, color: Colors.grey),
          ),
          const SizedBox(height: 24),

          TextField(
            controller: _priceController,
            decoration: const InputDecoration(
              labelText: 'Auction Price (USD)',
              border: OutlineInputBorder(),
              prefixText: '\$ ',
            ),
            keyboardType: TextInputType.number,
          ),
          const SizedBox(height: 16),

          TextField(
            controller: _yearController,
            decoration: const InputDecoration(
              labelText: 'Year (optional)',
              border: OutlineInputBorder(),
            ),
            keyboardType: TextInputType.number,
          ),
          const SizedBox(height: 24),

          ElevatedButton(
            onPressed: _loading ? null : _calculate,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Theme.of(context).colorScheme.primary,
              foregroundColor: Colors.white,
            ),
            child: _loading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Calculate'),
          ),

          if (_error != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_error!, style: const TextStyle(color: Colors.red)),
            ),
          ],

          if (_result != null) ...[
            const SizedBox(height: 24),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Results', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const Divider(),
                    _buildRow('Auction Price', _result!['auction_price']?.toString()),
                    _buildRow('Copart Fee', _result!['copart_fee']?.toString()),
                    _buildRow('Delivery', _result!['delivery']?.toString()),
                    _buildRow('Customs Duty', _result!['customs_duty']?.toString()),
                    _buildRow('VAT', _result!['vat']?.toString()),
                    _buildRow('Recycling Fee', _result!['recycling_fee']?.toString()),
                    _buildRow('Clearance', _result!['clearance']?.toString()),
                    const Divider(),
                    _buildRow(
                      'Total Cost',
                      _result!['total_cost']?.toString(),
                      isBold: true,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildRow(String label, String? value, {bool isBold = false, Color? color}) {
    if (value == null) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontWeight: isBold ? FontWeight.bold : null)),
          Text(
            '\$ $value',
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : null,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}
