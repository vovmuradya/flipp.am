import 'package:flutter/material.dart';

class CreateListingChoiceScreen extends StatelessWidget {
  const CreateListingChoiceScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Создать объявление'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Какое объявление вы хотите разместить?',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            const Text(
              'Выберите подходящий формат и заполните форму',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
            const SizedBox(height: 32),
            _buildChoiceCard(
              context,
              icon: Icons.description,
              title: 'Обычное объявление',
              description: 'Подходит для частных продавцов и автосалонов. Добавьте автомобиль с подробным описанием и фото.',
              onTap: () {
                Navigator.pushNamed(context, '/listings/create');
              },
            ),
            const SizedBox(height: 16),
            _buildChoiceCard(
              context,
              icon: Icons.public,
              title: 'Объявление с другого сайта',
              description: 'Вставьте ссылку с List.am — подтянем фото и параметры, а вы сразу отредактируете под себя.',
              onTap: () {
                Navigator.pushNamed(context, '/listings/create-from-external');
              },
            ),
            const SizedBox(height: 16),
            _buildChoiceCard(
              context,
              icon: Icons.gavel,
              title: 'Объявление из аукциона',
              description: 'Импортируйте данные по лоту Copart и быстро создайте объявление с уже заполненными характеристиками.',
              color: Colors.orange,
              onTap: () {
                Navigator.pushNamed(context, '/listings/create-from-auction');
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChoiceCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String description,
    required VoidCallback onTap,
    Color? color,
  }) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: (color ?? Colors.blue).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, size: 32, color: color ?? Colors.blue),
              ),
              const SizedBox(height: 16),
              Text(
                title,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                description,
                style: const TextStyle(fontSize: 14, color: Colors.grey),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Text(
                    'Перейти к форме',
                    style: TextStyle(
                      color: color ?? Colors.blue,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(Icons.arrow_forward, size: 16, color: color ?? Colors.blue),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
