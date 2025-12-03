import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'IDrom Client',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blueAccent),
        useMaterial3: true,
      ),
      home: const MainNavigation(),
    );
  }
}

class MainNavigation extends StatefulWidget {
  const MainNavigation({super.key});
  @override
  State<MainNavigation> createState() => _MainNavigationState();
}

class _MainNavigationState extends State<MainNavigation> {
  int _currentIndex = 0;

  // Список всех основных "страниц"-заглушек (виджетов)
  final List<Widget> _pages = const [
    ListingsPageStub(),
    SearchPageStub(),
    DashboardStub(),
    DealersPageStub(),
    ProfilePageStub(),
  ];

  final List<String> _titles = const [
    'Главная',
    'Поиск',
    'Дашборд',
    'Дилеры',
    'Профиль',
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_currentIndex]),
      ),
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.list), label: 'Листинги'),
          BottomNavigationBarItem(icon: Icon(Icons.search), label: 'Поиск'),
          BottomNavigationBarItem(icon: Icon(Icons.dashboard), label: 'Дашборд'),
          BottomNavigationBarItem(icon: Icon(Icons.store), label: 'Дилеры'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Профиль'),
        ],
      ),
    );
  }
}

// ================= Заглушки для страниц/экранов ===================

class ListingsPageStub extends StatelessWidget {
  const ListingsPageStub({super.key});

  Future<List<dynamic>> fetchListings() async {
    final response = await http.get(
      Uri.parse('http://10.0.2.2:8000/api/mobile/listings?per_page=10'),
    );
    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      return jsonData['data'] ?? [];
    } else {
      throw Exception('Ошибка загрузки данных');
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<dynamic>>(
      future: fetchListings(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          return Center(child: Text('Ошибка: ${snapshot.error}'));
        }
        final list = snapshot.data ?? [];
        if (list.isEmpty) {
          return const Center(child: Text('Нет объявлений'));
        }
        return ListView.builder(
          itemCount: list.length,
          itemBuilder: (context, i) {
            final item = list[i];
            final photos = item['photos'] ?? {};
            final photoUrl = photos['primary'] ?? '';
            final title = item['title'] ?? 'Без названия';
            final price = item['price']?['amount'];
            final currency = item['price']?['currency'] ?? '';
            return Card(
              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              child: ListTile(
                leading: photoUrl != ''
                  ? Image.network('http://10.0.2.2:8000/$photoUrl', width: 64, height: 64, fit: BoxFit.cover)
                  : const Icon(Icons.directions_car_outlined, size: 48),
                title: Text(title, maxLines: 2, overflow: TextOverflow.ellipsis),
                subtitle: Text(price != null ? '$price $currency' : 'Без цены'),
              ),
            );
          },
        );
      },
    );
  }
}

class SearchPageStub extends StatelessWidget {
  const SearchPageStub({super.key});
  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: const [
      Icon(Icons.search, size: 64),
      SizedBox(height: 12),
      Text('Поиск'),
    ]));
  }
}

class DashboardStub extends StatelessWidget {
  const DashboardStub({super.key});
  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: const [
      Icon(Icons.dashboard_customize, size: 64),
      SizedBox(height: 12),
      Text('Дашборд'),
      SizedBox(height: 8),
      Text('• Мои объявления\n• Аукционы\n• Избранное', textAlign: TextAlign.center),
    ]));
  }
}

class DealersPageStub extends StatelessWidget {
  const DealersPageStub({super.key});
  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: const [
      Icon(Icons.storefront, size: 64),
      SizedBox(height: 12),
      Text('Дилеры'),
    ]));
  }
}

class ProfilePageStub extends StatelessWidget {
  const ProfilePageStub({super.key});
  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: const [
      Icon(Icons.person_outline, size: 64),
      SizedBox(height: 12),
      Text('Профиль'),
    ]));
  }
}
