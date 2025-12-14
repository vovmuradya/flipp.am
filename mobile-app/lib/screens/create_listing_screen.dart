import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../api_client.dart';
import '../models/category.dart';
import '../models/car_brand.dart';

class CreateListingScreen extends StatefulWidget {
  final ApiClient api;
  final String type;
  final Map<String, dynamic>? initialData;

  const CreateListingScreen({
    super.key, 
    required this.api, 
    required this.type,
    this.initialData,
  });

  @override
  State<CreateListingScreen> createState() => _CreateListingScreenState();
}

class _CreateListingScreenState extends State<CreateListingScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _priceController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _yearController = TextEditingController();
  final _mileageController = TextEditingController();
  final _colorController = TextEditingController();
  final _engineController = TextEditingController();
  final _vinController = TextEditingController();
  
  Category? _selectedCategory;
  CarBrand? _selectedBrand;
  CarModel? _selectedModel;
  String? _selectedTransmission;
  String? _selectedFuelType;
  String? _selectedBodyType;
  List<Category> _categories = [];
  List<CarBrand> _brands = [];
  List<CarModel> _models = [];
  List<XFile> _images = [];
  List<String> _importedImageUrls = [];
  bool _loading = false;
  String? _error;
  int _currentImageIndex = 0;
  String? _importedMake;
  String? _importedModel;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  @override
  void dispose() {
    _titleController.dispose();
    _priceController.dispose();
    _descriptionController.dispose();
    _yearController.dispose();
    _mileageController.dispose();
    _colorController.dispose();
    _engineController.dispose();
    _vinController.dispose();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    setState(() => _loading = true);
    try {
      final categories = await widget.api.fetchRootCategories();
      final brands = await widget.api.fetchCarBrands();
      setState(() {
        _categories = categories;
        _brands = brands;
        // Auto-select first category for all types
        if (categories.isNotEmpty) {
          _selectedCategory = categories.first;
        }
      });
      
      // Pre-fill form if initialData provided
      if (widget.initialData != null) {
        _prefillFromImport(widget.initialData!);
      }
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }
  
  void _prefillFromImport(Map<String, dynamic> data) {
    // Category is already selected in _loadInitialData
    
    // Set basic fields
    if (data['year'] != null) {
      _yearController.text = data['year'].toString();
    }
    if (data['mileage'] != null) {
      _mileageController.text = data['mileage'].toString();
    }
    if (data['exterior_color'] != null) {
      _colorController.text = data['exterior_color'].toString();
    }
    if (data['engine_displacement_cc'] != null) {
      _engineController.text = '${data['engine_displacement_cc']} cc';
    }
    
    // Set make/model from parsed data as text (not dropdown)
    if (data['make'] != null) {
      _importedMake = data['make'].toString();
    }
    if (data['model'] != null) {
      _importedModel = data['model'].toString();
    }
    
    // Set transmission
    if (data['transmission'] != null) {
      final trans = data['transmission'].toString().toLowerCase();
      if (trans.contains('auto')) {
        _selectedTransmission = 'Автоматическая';
      } else if (trans.contains('manual')) {
        _selectedTransmission = 'Механическая';
      }
    }
    
    // Set fuel type
    if (data['fuel_type'] != null) {
      final fuel = data['fuel_type'].toString().toLowerCase();
      if (fuel.contains('gas') || fuel.contains('бензин')) {
        _selectedFuelType = 'Бензин';
      } else if (fuel.contains('diesel') || fuel.contains('дизель')) {
        _selectedFuelType = 'Дизель';
      } else if (fuel.contains('electric') || fuel.contains('электро')) {
        _selectedFuelType = 'Электро';
      } else if (fuel.contains('hybrid') || fuel.contains('гибрид')) {
        _selectedFuelType = 'Гибрид';
      }
    }
    
    // Set body type
    if (data['body_type'] != null) {
      _selectedBodyType = data['body_type'].toString();
    }
    
    // Set price (buy_now_price or current_bid_price)
    if (data['buy_now_price'] != null) {
      _priceController.text = data['buy_now_price'].toString();
    } else if (data['current_bid_price'] != null) {
      _priceController.text = data['current_bid_price'].toString();
    }
    
    // Build description from available data
    final descParts = <String>[];
    
    // Add car info
    final carMake = data['make'] ?? '';
    final carModel = data['model'] ?? '';
    final carYear = data['year'] ?? '';
    if (carMake.isNotEmpty || carModel.isNotEmpty) {
      descParts.add('$carYear $carMake $carModel imported from auction'.trim());
    }
    
    if (data['operational_status'] != null) {
      descParts.add('Status: ${data['operational_status']}');
    }
    if (data['primary_damage'] != null) {
      descParts.add('Damage: ${data['primary_damage']}');
    }
    if (data['auction_ends_at'] != null) {
      descParts.add('Auction ends: ${data['auction_ends_at']}');
    }
    if (data['source_auction_url'] != null) {
      descParts.add('\nSource: ${data['source_auction_url']}');
    }
    
    // Ensure description is at least 20 characters
    if (descParts.isEmpty) {
      descParts.add('Vehicle imported from auction. Contact for more details.');
    }
    
    _descriptionController.text = descParts.join('\n');
    
    // Import photos and remove duplicates
    if (data['photos'] != null && data['photos'] is List) {
      final allPhotos = List<String>.from(data['photos']);
      
      // Remove duplicates based on filename (keep _ful.jpg, skip _hrs.jpg)
      final seen = <String>{};
      _importedImageUrls = allPhotos.where((url) {
        // Extract filename without size suffix
        final baseUrl = url.replaceAll('_hrs.jpg', '.jpg')
                           .replaceAll('_ful.jpg', '.jpg')
                           .replaceAll('_thb.jpg', '.jpg')
                           .replaceAll('_vthb.jpg', '.jpg');
        
        if (seen.contains(baseUrl)) {
          return false; // Skip duplicate
        }
        seen.add(baseUrl);
        return true;
      }).toList();
      
      debugPrint('📸 Imported ${_importedImageUrls.length} photos (${allPhotos.length} total, removed duplicates)');
    }
    
    // Build title from make/model/year (reuse variables)
    if (carMake.isNotEmpty || carModel.isNotEmpty || carYear.isNotEmpty) {
      _titleController.text = '$carYear $carMake $carModel'.trim();
    }
    
    debugPrint('📝 Pre-filled form with imported data');
  }

  Future<void> _loadModels(int brandId) async {
    try {
      final models = await widget.api.fetchCarModels(brandId);
      setState(() {
        _models = models;
        _selectedModel = null;
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load models: $e')),
      );
    }
  }

  Future<void> _pickImages() async {
    final picker = ImagePicker();
    final images = await picker.pickMultiImage();
    setState(() {
      _images.addAll(images);
    });
  }

  Future<void> _submit() async {
    // Validate required fields manually
    if (_titleController.text.trim().isEmpty) {
      setState(() => _error = 'Title is required (min 3 characters)');
      return;
    }
    if (_titleController.text.trim().length < 3) {
      setState(() => _error = 'Title must be at least 3 characters');
      return;
    }
    if (_priceController.text.trim().isEmpty) {
      setState(() => _error = 'Price is required');
      return;
    }
    if (_descriptionController.text.trim().isEmpty) {
      setState(() => _error = 'Description is required (min 20 characters)');
      return;
    }
    if (_descriptionController.text.trim().length < 20) {
      setState(() => _error = 'Description must be at least 20 characters');
      return;
    }
    
    final isFromAuction = widget.type == 'auction';
    
    // Auto-select category if not selected - DO NOT show error
    if (_selectedCategory == null && _categories.isNotEmpty) {
      _selectedCategory = _categories.first;
    }
    
    // For non-auction vehicles, require vehicle details
    if (!isFromAuction && widget.type == 'vehicle') {
      if (_selectedBrand == null) {
        setState(() => _error = 'Brand is required for vehicles');
        return;
      }
      if (_selectedModel == null) {
        setState(() => _error = 'Model is required for vehicles');
        return;
      }
      if (_yearController.text.trim().isEmpty) {
        setState(() => _error = 'Year is required for vehicles');
        return;
      }
    }
    
    // For auction imports, require make/model from import
    if (isFromAuction) {
      if (_importedMake == null || _importedMake?.isEmpty == true) {
        setState(() => _error = 'Vehicle make is required');
        return;
      }
    }
    
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      // Build fields according to Laravel API expectations
      final fields = <String, String>{
        'title': _titleController.text.trim(),
        'price': _priceController.text.trim(),
        'description': _descriptionController.text.trim(),
        'currency': 'USD',
        'listing_type': widget.type == 'auction' ? 'vehicle' : widget.type,
        'from_auction': isFromAuction ? '1' : '0',
      };
      
      // Category is REQUIRED - always add it
      if (_selectedCategory != null) {
        fields['category_id'] = _selectedCategory!.id.toString();
      } else if (_categories.isNotEmpty) {
        fields['category_id'] = _categories.first.id.toString();
      } else {
        // Fallback - use category 1 (usually Vehicles)
        fields['category_id'] = '1';
      }
      
      // Vehicle details in nested structure
      if (widget.type == 'vehicle' || widget.type == 'auction') {
        // ALWAYS set is_from_auction for auction type
        if (isFromAuction) {
          fields['vehicle[is_from_auction]'] = '1';
        }
        
        // For auction imports, use parsed make/model
        if (isFromAuction && _importedMake != null && _importedMake!.isNotEmpty) {
          fields['vehicle[make]'] = _importedMake!;
        } else if (_selectedBrand != null) {
          fields['vehicle[make]'] = _selectedBrand!.name;
        }
        
        if (isFromAuction && _importedModel != null && _importedModel!.isNotEmpty) {
          fields['vehicle[model]'] = _importedModel!;
        } else if (_selectedModel != null) {
          fields['vehicle[model]'] = _selectedModel!.name;
        }
        if (_yearController.text.isNotEmpty) {
          fields['vehicle[year]'] = _yearController.text.trim();
        }
        if (_mileageController.text.isNotEmpty) {
          fields['vehicle[mileage]'] = _mileageController.text.trim();
        }
        if (_colorController.text.isNotEmpty) {
          fields['vehicle[exterior_color]'] = _colorController.text.trim();
        }
        if (_engineController.text.isNotEmpty) {
          // Extract number from engine string
          final engineNum = _engineController.text.replaceAll(RegExp(r'[^0-9]'), '');
          if (engineNum.isNotEmpty) {
            fields['vehicle[engine_displacement_cc]'] = engineNum;
          }
        }
        if (_selectedTransmission != null) {
          // Map Russian to English
          final transmissionMap = {
            'Автоматическая': 'automatic',
            'Механическая': 'manual',
            'Вариатор': 'cvt',
          };
          fields['vehicle[transmission]'] = transmissionMap[_selectedTransmission] ?? 'automatic';
        }
        if (_selectedFuelType != null) {
          // Map Russian to English
          final fuelMap = {
            'Бензин': 'gasoline',
            'Дизель': 'diesel',
            'Электро': 'electric',
            'Гибрид': 'hybrid',
          };
          fields['vehicle[fuel_type]'] = fuelMap[_selectedFuelType] ?? 'gasoline';
        }
        if (_selectedBodyType != null && _selectedBodyType!.isNotEmpty) {
          fields['vehicle[body_type]'] = _selectedBodyType!;
        }
        
        // Add auction-specific data
        if (isFromAuction && widget.initialData != null) {
          final initData = widget.initialData!;
          if (initData['source_auction_url'] != null) {
            fields['vehicle[source_auction_url]'] = initData['source_auction_url'].toString();
          }
          if (initData['operational_status'] != null) {
            fields['vehicle[operational_status]'] = initData['operational_status'].toString();
          }
          if (initData['primary_damage'] != null) {
            fields['vehicle[primary_damage]'] = initData['primary_damage'].toString();
          }
        }
      }
      
      // Add imported image URLs as auction_photos
      final imagePaths = _images.map((e) => e.path).toList();
      
      // Add auction photos URLs
      for (var i = 0; i < _importedImageUrls.length; i++) {
        fields['auction_photos[$i]'] = _importedImageUrls[i];
      }

      await widget.api.createListing(fields: fields, filePaths: imagePaths);

      if (mounted) {
        // Close TWO screens: CreateListingScreen AND ImportAuctionScreen
        Navigator.of(context).pop(); // Close CreateListingScreen
        Navigator.of(context).pop(); // Close ImportAuctionScreen
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listing created successfully!')),
        );
      }
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst(RegExp('^Exception: '), '');
      });
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Create ${widget.type == 'vehicle' ? 'Vehicle' : 'Parts'} Listing'),
      ),
      body: _loading && _categories.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_error != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.red.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(_error!, style: const TextStyle(color: Colors.red)),
                    ),
                  
                  TextFormField(
                    controller: _titleController,
                    decoration: const InputDecoration(
                      labelText: 'Title *',
                      hintText: 'e.g. Toyota Camry 2015',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),

                  if (widget.type == 'vehicle' || widget.type == 'auction') ...[
                    // For auction imports, show parsed make/model as read-only text
                    if (widget.type == 'auction' && _importedMake != null) ...[
                      TextFormField(
                        initialValue: _importedMake,
                        decoration: const InputDecoration(
                          labelText: 'Brand (from auction)',
                          border: OutlineInputBorder(),
                          enabled: false,
                        ),
                        enabled: false,
                      ),
                      const SizedBox(height: 16),
                      
                      if (_importedModel != null) ...[
                        TextFormField(
                          initialValue: _importedModel,
                          decoration: const InputDecoration(
                            labelText: 'Model (from auction)',
                            border: OutlineInputBorder(),
                            enabled: false,
                          ),
                          enabled: false,
                        ),
                        const SizedBox(height: 16),
                      ],
                    ],
                    
                    // For regular vehicles (not auction), show dropdowns
                    if (widget.type == 'vehicle') ...[
                      DropdownButtonFormField<CarBrand>(
                        value: _selectedBrand,
                        decoration: const InputDecoration(
                          labelText: 'Brand *',
                          border: OutlineInputBorder(),
                        ),
                        items: _brands.map((b) => DropdownMenuItem(value: b, child: Text(b.name))).toList(),
                        onChanged: (v) {
                          setState(() => _selectedBrand = v);
                          if (v != null) _loadModels(v.id);
                        },
                      ),
                      const SizedBox(height: 16),

                      DropdownButtonFormField<CarModel>(
                        value: _selectedModel,
                        decoration: const InputDecoration(
                          labelText: 'Model *',
                          border: OutlineInputBorder(),
                        ),
                        items: _models.map((m) => DropdownMenuItem(value: m, child: Text(m.name))).toList(),
                        onChanged: (v) => setState(() => _selectedModel = v),
                      ),
                      const SizedBox(height: 16),
                    ],

                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _yearController,
                            decoration: const InputDecoration(
                              labelText: 'Year',
                              border: OutlineInputBorder(),
                            ),
                            keyboardType: TextInputType.number,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: TextFormField(
                            controller: _mileageController,
                            decoration: const InputDecoration(
                              labelText: 'Mileage (km)',
                              border: OutlineInputBorder(),
                            ),
                            keyboardType: TextInputType.number,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    
                    // Additional fields for imported data
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _colorController,
                            decoration: const InputDecoration(
                              labelText: 'Color',
                              border: OutlineInputBorder(),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: TextFormField(
                            controller: _engineController,
                            decoration: const InputDecoration(
                              labelText: 'Engine',
                              border: OutlineInputBorder(),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: _selectedTransmission,
                            decoration: const InputDecoration(
                              labelText: 'Transmission',
                              border: OutlineInputBorder(),
                            ),
                            items: ['Автоматическая', 'Механическая', 'Вариатор']
                                .map((t) => DropdownMenuItem(value: t, child: Text(t)))
                                .toList(),
                            onChanged: (v) => setState(() => _selectedTransmission = v),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: _selectedFuelType,
                            decoration: const InputDecoration(
                              labelText: 'Fuel Type',
                              border: OutlineInputBorder(),
                            ),
                            items: ['Бензин', 'Дизель', 'Электро', 'Гибрид']
                                .map((f) => DropdownMenuItem(value: f, child: Text(f)))
                                .toList(),
                            onChanged: (v) => setState(() => _selectedFuelType = v),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                  ],

                  TextFormField(
                    controller: _priceController,
                    decoration: const InputDecoration(
                      labelText: 'Price *',
                      border: OutlineInputBorder(),
                      prefixText: '\$ ',
                    ),
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 16),

                  TextFormField(
                    controller: _descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'Description * (min 20 characters)',
                      hintText: 'Describe the vehicle condition, features, etc.',
                      border: OutlineInputBorder(),
                    ),
                    maxLines: 5,
                  ),
                  const SizedBox(height: 16),

                  Text('Photos (${_images.length + _importedImageUrls.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  
                  // Main image slider with swipe
                  if (_images.isNotEmpty || _importedImageUrls.isNotEmpty) ...[
                    Container(
                      height: 250,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.1),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Stack(
                        children: [
                          PageView.builder(
                            itemCount: _importedImageUrls.length + _images.length,
                            onPageChanged: (index) => setState(() => _currentImageIndex = index),
                            itemBuilder: (context, index) {
                              if (index < _importedImageUrls.length) {
                                // Show imported image
                                final url = _importedImageUrls[index]
                                    .replaceAll('http://localhost/proxy', 'http://localhost:8000/proxy');
                                return ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.network(
                                    url,
                                    fit: BoxFit.cover,
                                    loadingBuilder: (context, child, loadingProgress) {
                                      if (loadingProgress == null) return child;
                                      return Container(
                                        color: Colors.grey.shade200,
                                        child: Center(
                                          child: CircularProgressIndicator(
                                            value: loadingProgress.expectedTotalBytes != null
                                                ? loadingProgress.cumulativeBytesLoaded / loadingProgress.expectedTotalBytes!
                                                : null,
                                          ),
                                        ),
                                      );
                                    },
                                    errorBuilder: (_, __, ___) => Container(
                                      color: Colors.grey.shade300,
                                      child: const Icon(Icons.broken_image, size: 64),
                                    ),
                                  ),
                                );
                              } else {
                                // Show local image
                                final img = _images[index - _importedImageUrls.length];
                                return ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.file(File(img.path), fit: BoxFit.cover),
                                );
                              }
                            },
                          ),
                          // Delete button
                          Positioned(
                            top: 8,
                            right: 8,
                            child: GestureDetector(
                              onTap: () {
                                setState(() {
                                  if (_currentImageIndex < _importedImageUrls.length) {
                                    _importedImageUrls.removeAt(_currentImageIndex);
                                  } else {
                                    _images.removeAt(_currentImageIndex - _importedImageUrls.length);
                                  }
                                  if (_currentImageIndex > 0) _currentImageIndex--;
                                });
                              },
                              child: Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: Colors.red,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.3),
                                      blurRadius: 4,
                                    ),
                                  ],
                                ),
                                child: const Icon(Icons.delete, size: 20, color: Colors.white),
                              ),
                            ),
                          ),
                          // Page indicator
                          Positioned(
                            bottom: 12,
                            left: 0,
                            right: 0,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: List.generate(
                                _importedImageUrls.length + _images.length,
                                (index) => Container(
                                  margin: const EdgeInsets.symmetric(horizontal: 4),
                                  width: 8,
                                  height: 8,
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    color: _currentImageIndex == index
                                        ? Colors.white
                                        : Colors.white.withOpacity(0.4),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  
                  // Add photos button
                  GestureDetector(
                    onTap: _pickImages,
                    child: Container(
                      height: 60,
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade300, width: 2),
                        borderRadius: BorderRadius.circular(8),
                        color: Colors.grey.shade50,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.add_photo_alternate, size: 28, color: Colors.grey),
                          SizedBox(width: 8),
                          Text('Add more photos', style: TextStyle(color: Colors.grey, fontSize: 16)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  ElevatedButton(
                    onPressed: _loading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: Theme.of(context).colorScheme.primary,
                      foregroundColor: Colors.white,
                    ),
                    child: _loading
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Create Listing'),
                  ),
                ],
              ),
            ),
    );
  }
}
