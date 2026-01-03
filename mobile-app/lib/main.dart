import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_sign_in/google_sign_in.dart';

import 'api_client.dart';
import 'models/chat.dart';
import 'models/listing.dart';
import 'models/message.dart';
import 'models/profile.dart';
import 'models/review.dart';
import 'screens/calculator_screen.dart';
import 'screens/create_listing_screen.dart';
import 'screens/import_from_auction_screen.dart';
import 'screens/import_from_external_screen.dart';
import 'screens/my_auctions_screen.dart';
import 'screens/my_listings_screen.dart';
import 'screens/register_screen.dart';
import 'screens/reviews_screen.dart';
import 'screens/search_screen.dart';
import 'screens/settings_screen.dart';
import 'test_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    // Основные цвета для новой темы
    const primary = Color(0xFFEF4444);
    const secondary = Color(0xFFFF6B6B);
    const accent = Color(0xFF4ECDC4);
    const backgroundLight = Color(0xFFFAFAFA);
    const backgroundDark = Color(0xFF0D0D0D);
    const cardLight = Color(0xFFFFFFFF);
    const cardDark = Color(0xFF1A1A1A);

    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'idrom.am',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: primary,
          brightness: Brightness.light,
          background: backgroundLight,
        ),
        scaffoldBackgroundColor: backgroundLight,
        cardColor: cardLight,
        textTheme: GoogleFonts.poppinsTextTheme()
            .copyWith(
              headlineLarge: GoogleFonts.poppins(
                fontSize: 32,
                fontWeight: FontWeight.w800,
                color: const Color(0xFF1A1A1A),
              ),
              headlineMedium: GoogleFonts.poppins(
                fontSize: 24,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF1A1A1A),
              ),
              titleLarge: GoogleFonts.poppins(
                fontSize: 20,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF1A1A1A),
              ),
              bodyLarge: GoogleFonts.poppins(
                fontSize: 16,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF1A1A1A),
              ),
              bodyMedium: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight: FontWeight.w400,
                color: const Color(0xFF4A4A4A),
              ),
            ),
        useMaterial3: true,
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: primary,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            elevation: 4,
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: primary, width: 2),
            foregroundColor: primary,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.grey.shade100,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: primary, width: 2),
          ),
        ),
      ),
      darkTheme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: primary,
          brightness: Brightness.dark,
          background: backgroundDark,
        ),
        scaffoldBackgroundColor: backgroundDark,
        cardColor: cardDark,
        textTheme: GoogleFonts.poppinsTextTheme(ThemeData.dark().textTheme)
            .copyWith(
              headlineLarge: GoogleFonts.poppins(
                fontSize: 32,
                fontWeight: FontWeight.w800,
                color: const Color(0xFFE0E0E0),
              ),
              headlineMedium: GoogleFonts.poppins(
                fontSize: 24,
                fontWeight: FontWeight.w700,
                color: const Color(0xFFE0E0E0),
              ),
              titleLarge: GoogleFonts.poppins(
                fontSize: 20,
                fontWeight: FontWeight.w600,
                color: const Color(0xFFE0E0E0),
              ),
              bodyLarge: GoogleFonts.poppins(
                fontSize: 16,
                fontWeight: FontWeight.w500,
                color: const Color(0xFFCCCCCC),
              ),
              bodyMedium: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight: FontWeight.w400,
                color: const Color(0xFFAAAAAA),
              ),
            ),
        useMaterial3: true,
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: primary,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            elevation: 4,
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: primary, width: 2),
            foregroundColor: primary,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.grey.shade900,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: primary, width: 2),
          ),
        ),
      ),
      home: const AppShell(),
      // home: const TestScreen(), // Test screen
    );
  }
}

class AppShell extends StatefulWidget {
  const AppShell({super.key});

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  final ApiClient _api = ApiClient();
  int _tabIndex = 0;
  Profile? _profile;
  bool _authLoading = false;
  String? _authError;

  bool get _isLoggedIn => _profile != null && _api.hasToken;

  @override
  void initState() {
    super.initState();
    _restoreSession();
  }

  Future<void> _restoreSession() async {
    setState(() => _authLoading = true);
    try {
      // Try to load saved token and profile
      await _api.loadSavedToken();
      if (_api.hasToken) {
        print('🔐 Found saved token, fetching profile...');
        await _fetchProfile();
      }
    } catch (e) {
      print('⚠️ Failed to restore session: $e');
    } finally {
      setState(() => _authLoading = false);
    }
  }

  void _openAddFlow() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => AddListingOptionsScreen(
        api: _api,
        isLoggedIn: _isLoggedIn,
      )),
    );
  }

  Future<void> _login(String login, String password) async {
    setState(() {
      _authLoading = true;
      _authError = null;
    });
    try {
      final profile = await _api.login(
        login: login,
        password: password,
        deviceName: 'flutter_app',
      );
      setState(() {
        _profile = profile;
      });
    } catch (e) {
      setState(() {
        final msg = e.toString().replaceFirst(RegExp('^Exception: '), '');
        _authError = msg;
      });
    } finally {
      setState(() {
        _authLoading = false;
      });
    }
  }

  void _openLogin() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => LoginScreen(
          api: _api,
          onSubmit: (login, password) async {
            setState(() {
              _authLoading = true;
              _authError = null;
            });

            try {
              final profile = await _api.login(
                login: login,
                password: password,
                deviceName: 'flutter_app',
              );
              setState(() {
                _profile = profile;
                _authLoading = false;
              });

              if (mounted) {
                Navigator.of(context).pop(); // Close login screen only on success
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Добро пожаловать, ${profile.name}!')),
                );
              }
            } catch (e) {
              setState(() {
                _authLoading = false;
                _authError = e.toString().replaceFirst(RegExp('^Exception: '), '');
              });
              // Don't close screen - show error in LoginScreen
              throw e; // Re-throw to show in LoginScreen
            }
          },
          onOpenRegister: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => RegisterScreen(api: _api),
              ),
            );
          },
          onGoogleSignIn: () async {
            // Google Sign-In will be handled inside LoginScreen
          },
        ),
      ),
    );
  }

  Future<void> _fetchProfile() async {
    if (!_api.hasToken) return;
    try {
      final profile = await _api.fetchProfile();
      setState(() {
        _profile = profile;
      });
    } catch (_) {}
  }

  void _logout() {
    _api.setToken(null);
    setState(() {
      _profile = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;
    final isDark = theme.brightness == Brightness.dark;

    final screens = [
      CarListingScreen(
        api: _api,
      ),
      SearchScreen(api: _api),
      MessagesListScreen(
        api: _api,
        profile: _profile,
        onOpenChat: (chat) {
          if (chat.listingId == null) return;
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ChatScreen(
                chat: chat,
                api: _api,
                myProfile: _profile,
              ),
            ),
          );
        },
      ),
      ProfileScreen(
        api: _api,
        profile: _profile,
        authLoading: _authLoading,
        authError: _authError,
        onLogin: _login,
        onLogout: _logout,
        onRefreshProfile: _fetchProfile,
        onOpenLogin: _openLogin,
      ),
    ];

    final textInactive =
        isDark ? Colors.grey.shade600 : Colors.grey.shade500;

    // Show loading screen while checking session
    if (_authLoading && _profile == null) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(color: primary),
              const SizedBox(height: 16),
              Text(
                'Загрузка...',
                style: theme.textTheme.bodyMedium?.copyWith(color: Colors.grey),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      body: SafeArea(
        top: false,
        child: IndexedStack(
          index: _tabIndex,
          children: screens,
        ),
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Theme.of(context).scaffoldBackgroundColor,
          border: Border(
            top: BorderSide(
              color: isDark ? Colors.grey.shade800 : Colors.grey.shade200,
            ),
          ),
        ),
        padding: const EdgeInsets.fromLTRB(24, 8, 24, 14),
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  onPressed: () => setState(() => _tabIndex = 0),
                  icon: Icon(
                    Icons.home,
                    color: _tabIndex == 0 ? primary : textInactive,
                  ),
                ),
                IconButton(
                  onPressed: () => setState(() => _tabIndex = 1),
                  icon: Icon(
                    Icons.search,
                    color: _tabIndex == 1 ? primary : textInactive,
                  ),
                ),
                const SizedBox(width: 64),
                IconButton(
                  onPressed: () => setState(() => _tabIndex = 2),
                  icon: Icon(
                    Icons.chat_bubble_outline,
                    color: _tabIndex == 2 ? primary : textInactive,
                  ),
                ),
                IconButton(
                  onPressed: () => setState(() => _tabIndex = 3),
                  icon: Icon(
                    Icons.person_outline,
                    color: _tabIndex == 3 ? primary : textInactive,
                  ),
                ),
              ],
            ),
            Positioned.fill(
              child: Center(
                child: Transform.translate(
                  offset: const Offset(0, -24),
                  child: Material(
                    color: primary,
                    shape: const CircleBorder(),
                    elevation: 6,
                    shadowColor: primary.withOpacity(0.4),
                    child: InkWell(
                      customBorder: const CircleBorder(),
                      onTap: _openAddFlow,
                      child: const SizedBox(
                        width: 64,
                        height: 64,
                        child: Icon(
                          Icons.add,
                          size: 32,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class CarListingScreen extends StatefulWidget {
  const CarListingScreen({super.key, required this.api});

  final ApiClient api;

  @override
  State<CarListingScreen> createState() => _CarListingScreenState();
}

class _CarListingScreenState extends State<CarListingScreen> {
  List<Listing> featured = [];
  List<Listing> listings = [];
  bool loading = true;
  String? error;
  int featuredIndex = 0;

  @override
  void initState() {
    super.initState();
    print('🚗 CarListingScreen: Initializing...');
    _load();
  }

  Future<void> _load() async {
    print('📡 CarListingScreen: Loading listings from API...');
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final data = await widget.api.fetchListings();
      print('✅ CarListingScreen: Loaded ${data.length} listings');
      setState(() {
        listings = data;
        featured = data.take(2).toList();
        featuredIndex = 0;
        loading = false;
      });
    } catch (e) {
      print('❌ CarListingScreen: Error loading listings: $e');
      setState(() {
        error = e.toString();
        loading = false;
      });
    }
  }

  void _updateListing(Listing updated) {
    setState(() {
      listings = listings
          .map((item) => item.id == updated.id ? updated : item)
          .toList();
      featured = featured
          .map((item) => item.id == updated.id ? updated : item)
          .toList();
    });
  }

  Future<void> _toggleFavorite(Listing car) async {
    final next = car.copyWith(isFavorite: !car.isFavorite);
    _updateListing(next);
    try {
      final ok =
          await widget.api.toggleFavorite(car.id, favorite: next.isFavorite);
      if (!ok) {
        throw Exception('Favorite toggle failed');
      }
    } catch (e) {
      _updateListing(car);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Не удалось обновить избранное: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;
    final isDark = theme.brightness == Brightness.dark;
    final background = theme.scaffoldBackgroundColor;

    return Container(
      color: background,
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 28, 16, 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const _Logo(),
                    const SizedBox(width: 8),
                    Text(
                      'drom.am',
                      style: theme.textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        fontSize: 20,
                        color: isDark ? Colors.white : Colors.grey[900],
                      ),
                    ),
                  ],
                ),
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    IconButton(
                      icon: Icon(
                        Icons.notifications,
                        color: isDark ? Colors.grey[400] : Colors.grey[700],
                        size: 24,
                      ),
                      onPressed: () {},
                    ),
                    Positioned(
                      top: 4,
                      right: 4,
                      child: _PingDot(color: primary),
                    ),
                  ],
                ),
              ],
            ),
          ),
        Expanded(
          child: RefreshIndicator(
            onRefresh: _load,
            child: Builder(
              builder: (context) {
                if (loading) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (error != null) {
                    return ListView(
                      children: [
                        const SizedBox(height: 120),
                        Center(
                          child: Column(
                            children: [
                              Text(
                                'Ошибка загрузки',
                                style: theme.textTheme.titleMedium,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                '$error',
                                textAlign: TextAlign.center,
                                style: theme.textTheme.bodyMedium
                                    ?.copyWith(color: Colors.grey),
                              ),
                              const SizedBox(height: 12),
                              Container(
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [primary, primary.withOpacity(0.8)],
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                  boxShadow: [
                                    BoxShadow(
                                      color: primary.withOpacity(0.3),
                                      blurRadius: 8,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: FilledButton(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  onPressed: _load,
                                  child: const Text('Повторить'),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    );
                  }
                  if (listings.isEmpty) {
                    return ListView(
                      children: [
                        const SizedBox(height: 120),
                        Center(
                          child: Column(
                            children: [
                              Text(
                                'Пока нет объявлений',
                                style: theme.textTheme.titleMedium,
                              ),
                              const SizedBox(height: 8),
                              TextButton(
                                onPressed: _load,
                                child: const Text('Обновить'),
                              ),
                            ],
                          ),
                        ),
                      ],
                    );
                  }
                  return CustomScrollView(
                    physics: const BouncingScrollPhysics(),
                    slivers: [
                      SliverToBoxAdapter(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FeaturedCarousel(
                              items: featured,
                              onChanged: (idx) {
                                setState(() {
                                  featuredIndex = idx;
                                });
                              },
                              activeIndex: featuredIndex,
                            ),
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 12,
                              ),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'Сортировка',
                                    style: theme.textTheme.bodyMedium?.copyWith(
                                      color: primary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 6,
                                      ),
                                      side: BorderSide(
                                        color: isDark
                                            ? Colors.grey.shade700
                                            : Colors.grey.shade300,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    onPressed: () {},
                                    icon: Icon(
                                      Icons.expand_more,
                                      size: 16,
                                      color: isDark
                                          ? Colors.grey.shade500
                                          : Colors.grey.shade600,
                                    ),
                                    label: Text(
                                      'Сначала новые',
                                      style:
                                          theme.textTheme.bodyMedium?.copyWith(
                                        fontSize: 13,
                                        color: isDark
                                            ? Colors.grey.shade400
                                            : Colors.grey.shade700,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      SliverPadding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 8,
                        ),
                        sliver: SliverList.builder(
                          itemCount: listings.length,
                          itemBuilder: (context, index) {
                            final car = listings[index];
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: _ListingTile(
                                car: car,
                                onFavorite: () => _toggleFavorite(car),
                                onTap: () => Navigator.of(context).push(
                                  MaterialPageRoute(
                                    builder: (_) => CarDetailsScreen(
                                      car: car,
                                      api: widget.api, // Pass the API client
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                      const SliverToBoxAdapter(child: SizedBox(height: 120)),
                    ],
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _FeaturedCarousel extends StatefulWidget {
  const _FeaturedCarousel({
    required this.items,
    required this.onChanged,
    required this.activeIndex,
  });

  final List<Listing> items;
  final ValueChanged<int> onChanged;
  final int activeIndex;

  @override
  State<_FeaturedCarousel> createState() => _FeaturedCarouselState();
}

class _FeaturedCarouselState extends State<_FeaturedCarousel> {
  final controller = PageController(viewportFraction: 0.85);

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primary = theme.colorScheme.primary;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8),
      child: Column(
        children: [
          Container(
            height: 240,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: isDark
                    ? Colors.black.withOpacity(0.5)
                    : Colors.grey.shade300.withOpacity(0.4),
                  blurRadius: 12,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: PageView.builder(
              controller: controller,
              itemCount: widget.items.length,
              onPageChanged: widget.onChanged,
              itemBuilder: (context, index) {
                final item = widget.items[index];
                final badge = item.primaryDamage;
                return AnimatedPadding(
                  duration: const Duration(milliseconds: 250),
                  padding: EdgeInsets.only(
                    right: 16,
                    left: index == 0 ? 16 : 8,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Image.network(
                          item.imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            color: isDark ? Colors.grey.shade800 : Colors.grey.shade300,
                            alignment: Alignment.center,
                            child: const Icon(Icons.image_not_supported),
                          ),
                        ),
                        Container(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: [
                                isDark
                                  ? Colors.black.withOpacity(0.8)
                                  : Colors.black.withOpacity(0.6),
                                Colors.transparent,
                              ],
                            ),
                          ),
                        ),
                        if (badge != null && badge.isNotEmpty)
                          Positioned(
                            top: 16,
                            left: 16,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 5,
                              ),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [Colors.red.shade600, Colors.red.shade500],
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                badge,
                                style: theme.textTheme.labelSmall?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ),
                        Positioned(
                          top: 16,
                          right: 16,
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(
                              Icons.favorite,
                              color: Colors.white.withOpacity(0.9),
                              size: 20,
                            ),
                          ),
                        ),
                        Positioned(
                          bottom: 16,
                          left: 16,
                          right: 16,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item.title,
                                style: theme.textTheme.titleMedium?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w800,
                                  shadows: [
                                    Shadow(
                                      color: Colors.black.withOpacity(0.7),
                                      blurRadius: 4,
                                      offset: const Offset(1, 1),
                                    ),
                                  ],
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                item.priceDisplay,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(widget.items.length, (index) {
              final isActive = index == widget.activeIndex;
              return Container(
                margin: const EdgeInsets.symmetric(horizontal: 5),
                width: 12,
                height: 12,
                decoration: BoxDecoration(
                  gradient: isActive
                    ? LinearGradient(
                      colors: [primary, primary.withOpacity(0.7)],
                    )
                    : LinearGradient(
                      colors: [
                        isDark ? Colors.grey.shade700 : Colors.grey.shade300,
                        isDark ? Colors.grey.shade600 : Colors.grey.shade200,
                      ],
                    ),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.2),
                      blurRadius: 2,
                      offset: const Offset(0, 1),
                    ),
                  ],
                ),
              );
            }),
          ),
        ],
      ),
    );
  }
}

class _ListingTile extends StatelessWidget {
  const _ListingTile({
    required this.car,
    required this.onFavorite,
    required this.onTap,
  });

  final Listing car;
  final VoidCallback onFavorite;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primary = theme.colorScheme.primary;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: BoxDecoration(
        color: isDark ? Colors.grey.shade800 : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: isDark
              ? Colors.black.withOpacity(0.3)
              : Colors.grey.shade300.withOpacity(0.4),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    width: 112,
                    height: 80,
                    decoration: BoxDecoration(
                      color: isDark ? Colors.grey.shade700 : Colors.grey.shade200,
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        car.imageUrl,
                        width: 112,
                        height: 80,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          width: 112,
                          height: 80,
                          color: isDark ? Colors.grey.shade700 : Colors.grey.shade200,
                          alignment: Alignment.center,
                          child: const Icon(Icons.image_not_supported),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        car.title,
                        style: theme.textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                          color: isDark ? Colors.white : Colors.grey.shade900,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        car.priceDisplay,
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: primary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      if (car.location != null) ...[
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(
                              Icons.place,
                              size: 12,
                              color: isDark
                                  ? Colors.grey.shade500
                                  : Colors.grey.shade600,
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                car.location!,
                                style: theme.textTheme.bodySmall?.copyWith(
                                  color: isDark
                                      ? Colors.grey.shade400
                                      : Colors.grey.shade700,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  onPressed: onFavorite,
                  icon: Icon(
                    car.isFavorite ? Icons.favorite : Icons.favorite_border,
                    color: car.isFavorite
                        ? primary
                        : theme.colorScheme.primary.withOpacity(0.7),
                    size: 20,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _PingDot extends StatelessWidget {
  const _PingDot({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 14,
      height: 14,
      child: Stack(
        alignment: Alignment.center,
        children: [
          Container(
            width: 14,
            height: 14,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
          ),
          Container(
            width: 14,
            height: 14,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: color.withOpacity(0.6),
                  blurRadius: 8,
                  spreadRadius: 2,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Logo extends StatelessWidget {
  const _Logo();

  @override
  Widget build(BuildContext context) {
    return ClipOval(
      child: Image.asset(
        'assets/logo.png',
        width: 36,
        height: 36,
        fit: BoxFit.cover,
      ),
    );
  }
}

class CarDetailsScreen extends StatefulWidget {
  const CarDetailsScreen({super.key, required this.car, required this.api});

  final Listing car;
  final ApiClient api;

  @override
  State<CarDetailsScreen> createState() => _CarDetailsScreenState();
}

class _CarDetailsScreenState extends State<CarDetailsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isFavorite = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _isFavorite = widget.car.isFavorite;
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    return Scaffold(
      backgroundColor: const Color(0xFF221310),
      body: SafeArea(
        child: Stack(
          children: [
            Positioned.fill(
              child: Column(
                children: [
                  // Header with image and title
                  Expanded(
                    flex: 2,
                    child: Stack(
                      children: [
                        Positioned.fill(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: widget.car.imageUrl.isNotEmpty
                                ? Image.network(
                                    widget.car.imageUrl,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => Container(
                                      color: Colors.grey.shade800,
                                      alignment: Alignment.center,
                                      child: const Icon(
                                        Icons.image_not_supported,
                                        color: Colors.white,
                                      ),
                                    ),
                                  )
                                : Container(
                                    color: Colors.grey.shade800,
                                    alignment: Alignment.center,
                                    child: const Icon(
                                      Icons.image_not_supported,
                                      color: Colors.white,
                                    ),
                                  ),
                          ),
                        ),
                        Positioned(
                          top: 12,
                          left: 16,
                          child: IconButton(
                            onPressed: () => Navigator.of(context).pop(),
                            icon: const Icon(
                              Icons.arrow_back_ios_new,
                              color: Colors.white,
                            ),
                          ),
                        ),
                        Positioned(
                          top: 12,
                          right: 16,
                          child: IconButton(
                            onPressed: () async {
                              final next = !widget.car.isFavorite;
                              setState(() => _isFavorite = next);
                              try {
                                final success = await widget.api.toggleFavorite(widget.car.id, favorite: next);
                                if (success) {
                                  // Update the state to reflect the new favorite status
                                  setState(() {
                                    _isFavorite = next;
                                  });
                                } else {
                                  setState(() => _isFavorite = !next);
                                }
                              } catch (e) {
                                setState(() => _isFavorite = !next);
                                if (mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text('Failed to update favorite: $e')),
                                  );
                                }
                              }
                            },
                            icon: Icon(
                              _isFavorite ? Icons.favorite : Icons.favorite_border,
                              color: _isFavorite ? Colors.red : Colors.white,
                            ),
                          ),
                        ),
                        Positioned(
                          bottom: 0,
                          left: 0,
                          right: 0,
                          child: Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: [
                                  Colors.transparent,
                                  const Color(0xFF221310).withOpacity(0.9),
                                ],
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.car.title,
                                  style: theme.textTheme.headlineSmall?.copyWith(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  widget.car.priceDisplay,
                                  style: theme.textTheme.headlineSmall?.copyWith(
                                    color: primary,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                // Auction-specific information
                                if (widget.car.isFromAuction) ...[
                                  if (widget.car.currentBidPrice != null || widget.car.buyNowPrice != null) ...[
                                    const SizedBox(height: 4),
                                    if (widget.car.currentBidPrice != null)
                                      Text(
                                        'Current Bid: ${widget.car.currentBidPrice?.toStringAsFixed(0)} ${widget.car.currentBidCurrency ?? 'USD'}',
                                        style: theme.textTheme.bodyMedium?.copyWith(
                                          color: Colors.orange,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    if (widget.car.buyNowPrice != null)
                                      Text(
                                        'Buy Now: ${widget.car.buyNowPrice?.toStringAsFixed(0)} ${widget.car.buyNowCurrency ?? 'USD'}',
                                        style: theme.textTheme.bodyMedium?.copyWith(
                                          color: Colors.green,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                  ],
                                ],
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Tab bar and content
                  Expanded(
                    flex: 3,
                    child: Column(
                      children: [
                        Container(
                          color: const Color(0xFF221310),
                          child: TabBar(
                            controller: _tabController,
                            labelColor: primary,
                            unselectedLabelColor: Colors.grey,
                            indicatorColor: primary,
                            tabs: const [
                              Tab(text: 'Details'),
                              Tab(text: 'Features'),
                              Tab(text: 'Seller'),
                            ],
                          ),
                        ),
                        Expanded(
                          child: TabBarView(
                            controller: _tabController,
                            children: [
                              // Details Tab
                              SingleChildScrollView(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // Basic vehicle info
                                    _DetailRow('Make', widget.car.make ?? '—'),
                                    _DetailRow('Model', widget.car.model ?? '—'),
                                    _DetailRow('Year', widget.car.year ?? '—'),
                                    _DetailRow('Mileage', widget.car.mileage ?? '—'),
                                    _DetailRow('Transmission', widget.car.transmission ?? '—'),
                                    _DetailRow('Engine', widget.car.engine ?? '—'),
                                    _DetailRow('Body Type', widget.car.bodyType ?? '—'),
                                    _DetailRow('Fuel Type', widget.car.fuelType ?? '—'),
                                    _DetailRow('Exterior Color', widget.car.exteriorColor ?? '—'),
                                    _DetailRow('Interior Color', widget.car.interiorColor ?? '—'),
                                    const SizedBox(height: 16),
                                    // Auction-specific details
                                    if (widget.car.isFromAuction) ...[
                                      Text(
                                        'Auction Information',
                                        style: theme.textTheme.titleMedium?.copyWith(
                                          color: primary,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      _DetailRow('Auction URL', widget.car.auctionUrl ?? '—'),
                                      _DetailRow('Operational Status', widget.car.operationalStatus ?? '—'),
                                      _DetailRow('Primary Damage', widget.car.primaryDamage ?? '—'),
                                      _DetailRow('Auction Ends', widget.car.auctionEndsAt ?? '—'),
                                    ],
                                    // Location
                                    if (widget.car.location != null) ...[
                                      const SizedBox(height: 16),
                                      Text(
                                        'Location',
                                        style: theme.textTheme.titleMedium?.copyWith(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(12),
                                        child: SizedBox(
                                          height: 180,
                                          width: double.infinity,
                                          child: Image.network(
                                            'https://maps.googleapis.com/maps/api/staticmap?center=${widget.car.location!}&zoom=13&size=600x300&maptype=roadmap&key=YOUR_API_KEY', // Replace with real map service
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) => Container(
                                              color: Colors.grey.shade800,
                                              alignment: Alignment.center,
                                              child: const Icon(Icons.map),
                                            ),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Flexible(
                                            child: Text(
                                              widget.car.location!,
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          ),
                                          TextButton(
                                            onPressed: () {},
                                            child: Text(
                                              'Get Directions',
                                              style: TextStyle(color: primary),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                              // Features Tab
                              SingleChildScrollView(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Description',
                                      style: theme.textTheme.titleMedium?.copyWith(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      widget.car.description ?? 'No description available',
                                      style: const TextStyle(
                                        color: Colors.white70,
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    if (widget.car.auctionPhotoUrls.isNotEmpty) ...[
                                      Text(
                                        'Auction Photos',
                                        style: theme.textTheme.titleMedium?.copyWith(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      SizedBox(
                                        height: 120,
                                        child: ListView.builder(
                                          scrollDirection: Axis.horizontal,
                                          itemCount: widget.car.auctionPhotoUrls.length,
                                          itemBuilder: (context, index) {
                                            return Container(
                                              margin: const EdgeInsets.only(right: 8),
                                              child: ClipRRect(
                                                borderRadius: BorderRadius.circular(8),
                                                child: Image.network(
                                                  widget.car.auctionPhotoUrls[index],
                                                  width: 120,
                                                  height: 120,
                                                  fit: BoxFit.cover,
                                                  errorBuilder: (_, __, ___) => Container(
                                                    width: 120,
                                                    height: 120,
                                                    color: Colors.grey.shade800,
                                                    alignment: Alignment.center,
                                                    child: const Icon(Icons.image_not_supported),
                                                  ),
                                                ),
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                              // Seller Info Tab
                              SingleChildScrollView(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Seller Contact',
                                      style: theme.textTheme.titleMedium?.copyWith(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    OutlinedButton.icon(
                                      onPressed: () {},
                                      icon: const Icon(Icons.phone),
                                      label: const Text('Show Phone Number'),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: primary,
                                        side: BorderSide(color: primary),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 16,
                                          vertical: 12,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    OutlinedButton.icon(
                                      onPressed: () {},
                                      icon: const Icon(Icons.chat),
                                      label: const Text('Send Message'),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: primary,
                                        side: BorderSide(color: primary),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 16,
                                          vertical: 12,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    ElevatedButton.icon(
                                      onPressed: () {},
                                      icon: const Icon(Icons.report),
                                      label: const Text('Report Item'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.red,
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 16,
                                          vertical: 12,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF221310).withOpacity(0.9),
                  border: Border(
                    top: BorderSide(color: Colors.white.withOpacity(0.1)),
                  ),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 16,
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: primary,
                          side: BorderSide(color: primary),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                        onPressed: () {},
                        child: const Text(
                          'Make an Offer',
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primary,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                        onPressed: () {},
                        child: const Text(
                          'Contact Seller',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: Colors.white70,
              fontWeight: FontWeight.w500,
            ),
          ),
          Flexible(
            child: Text(
              value,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.right,
            ),
          ),
        ],
      ),
    );
  }
}

class _DetailTab extends StatelessWidget {
  const _DetailTab({
    required this.label,
    required this.isActive,
    required this.primary,
  });

  final String label;
  final bool isActive;
  final Color primary;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: isActive ? primary : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
      ),
      padding: const EdgeInsets.symmetric(vertical: 12),
      alignment: Alignment.center,
      child: Text(
        label,
        style: TextStyle(
          color: isActive ? Colors.white : Colors.white70,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _SpecTile extends StatelessWidget {
  const _SpecTile({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Divider(color: Colors.white12),
        const SizedBox(height: 8),
        Text(
          label,
          style: const TextStyle(
            color: Colors.white60,
            fontSize: 13,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}

class AddListingOptionsScreen extends StatelessWidget {
  final ApiClient api;
  final bool isLoggedIn;

  const AddListingOptionsScreen({
    super.key, 
    required this.api,
    this.isLoggedIn = false,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primary = theme.colorScheme.primary;

    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: [
                  const SizedBox(width: 48),
                  Expanded(
                    child: Text(
                      'Choose Listing Type',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _AddTypeCard(
                    icon: Icons.directions_car,
                    title: 'Объявление автомобиля',
                    description:
                        'Подходит для частных продавцов и автосалонов',
                    primary: primary,
                    isDark: isDark,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => CreateListingScreen(api: api, type: 'vehicle'),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 12),
                  _AddTypeCard(
                    icon: Icons.build,
                    title: 'Объявление запчастей',
                    description:
                        'Для продажи автомобильных запчастей и аксессуаров',
                    primary: primary,
                    isDark: isDark,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => CreateListingScreen(api: api, type: 'parts'),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 12),
                  const SizedBox(height: 12),
                  _AddTypeCard(
                    icon: Icons.public,
                    title: 'Объявление с другого сайта',
                    description:
                        'Импорт с List.am — подтянем фото и параметры',
                    primary: primary,
                    isDark: isDark,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ImportFromExternalScreen(),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 12),
                  _AddTypeCard(
                    icon: Icons.gavel,
                    title: 'Объявление из аукциона',
                    description:
                        'Импорт с Copart с готовыми характеристиками',
                    primary: primary,
                    isDark: isDark,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ImportFromAuctionScreen(api: api),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AddTypeCard extends StatelessWidget {
  const _AddTypeCard({
    required this.icon,
    required this.title,
    required this.description,
    required this.primary,
    required this.isDark,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String description;
  final Color primary;
  final bool isDark;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF2C1F1C) : Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: primary.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color: primary,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w700,
                          ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      description,
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: isDark
                                ? const Color(0xFFC99B92)
                                : Colors.grey.shade600,
                          ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios, size: 18),
            ],
          ),
        ),
      ),
    );
  }
}

class QuickSellBasicInfoScreen extends StatelessWidget {
  const QuickSellBasicInfoScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;
    final surface = const Color(0xFF221310);

    return Scaffold(
      backgroundColor: surface,
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white70),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  Expanded(
                    child: Text(
                      'Basic Vehicle Info',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.titleMedium?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Step 1 of 3',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: Colors.white70,
                    ),
                  ),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(999),
                    child: LinearProgressIndicator(
                      value: 0.33,
                      backgroundColor: Colors.white24,
                      color: primary,
                      minHeight: 8,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: [
                  const SizedBox(height: 12),
                  _DarkField(label: 'Make', hint: 'Select Make'),
                  const SizedBox(height: 16),
                  _DarkField(label: 'Model', hint: 'Select Model'),
                  const SizedBox(height: 16),
                  _DarkField(label: 'Year', hint: 'Select Year'),
                  const SizedBox(height: 16),
                  _DarkTextArea(
                    label: 'Description',
                    hint: "Briefly describe your vehicle's condition...",
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primary,
                  minimumSize: const Size.fromHeight(52),
                ),
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const QuickSellPhotosScreen(),
                    ),
                  );
                },
                child: const Text(
                  'Next',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DarkField extends StatelessWidget {
  const _DarkField({required this.label, required this.hint});

  final String label;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(color: Colors.white54),
            filled: true,
            fillColor: const Color(0xFF2B1A17),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF673B32)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF673B32)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFEF4444)),
            ),
            suffixIcon: const Icon(Icons.unfold_more, color: Colors.white54),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 16,
            ),
          ),
        ),
      ],
    );
  }
}

class _DarkTextArea extends StatelessWidget {
  const _DarkTextArea({required this.label, required this.hint});

  final String label;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          maxLines: 4,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(color: Colors.white54),
            filled: true,
            fillColor: const Color(0xFF2B1A17),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF673B32)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF673B32)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFEF4444)),
            ),
            contentPadding: const EdgeInsets.all(16),
          ),
        ),
        const SizedBox(height: 4),
        const Align(
          alignment: Alignment.centerRight,
          child: Text(
            '0/250',
            style: TextStyle(color: Colors.white54, fontSize: 12),
          ),
        ),
      ],
    );
  }
}

class QuickSellPhotosScreen extends StatelessWidget {
  const QuickSellPhotosScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    return Scaffold(
      backgroundColor: const Color(0xFF221310),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(Icons.arrow_back, color: Colors.white),
                  ),
                  Expanded(
                    child: Text(
                      'Basic Photos',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.titleMedium?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: 1,
                        backgroundColor: primary.withOpacity(0.2),
                        color: primary,
                        minHeight: 6,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: 1,
                        backgroundColor: primary.withOpacity(0.2),
                        color: primary,
                        minHeight: 6,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: 0,
                        backgroundColor: primary.withOpacity(0.2),
                        color: primary,
                        minHeight: 6,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: 0,
                        backgroundColor: primary.withOpacity(0.2),
                        color: primary,
                        minHeight: 6,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Text(
                    "Upload Your Car's Photos",
                    style: theme.textTheme.headlineSmall?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Add at least 3 photos to get started. Front, back, and interior views work best.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: Colors.white70,
                    ),
                  ),
                  const SizedBox(height: 20),
                  GridView.count(
                    crossAxisCount: 2,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      _PhotoTile(
                        url:
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuCfphYzMhEUqmG3Mo_CFMPMkG7rXhdiWBGExpkjoXamltTpoeTofVA-LYRymtYXclxsoE58ZmznPzumcqw9BJetGwC07CMdQImkija_tS-NGU8_4VaX8F4Qw6T1_guessw5VlrDngoQK4m6cGYBj5y2hcBXYacy71D_u5vYjyngTrvYtLONWRPfvM_uNSHoO_DorMtnY7TQO1xtX9p2pw9jGSO5t8q8T23INxQcynwSZ5A2vuMLfE6W6NvdOZ5wOPfEQTpdohklLg',
                        badge: 'Cover',
                      ),
                      _PhotoTile(
                        url:
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuAwoBcrM3dGrzFQd3fFErIlC1zpEQ4MCECJ2NwSFJLWQzmtGYJ8q5R31i0Ldm1j1JKsvnEdnIeiL92MGwRqa-H0vrfw2hG05mMEQFX-wonF0EYhPnffUXvlnq-I1aVr-KTaC-Loh-H04sx39__kRlml3Yw--Rb5romAAF-Wz9W4EKWoTTwGo_hNcOvJDNVHhnt7WDe7PuBdOGyo4O6kDky8QcOkmG0BzTm-zLsiPqJ7WrchEjHP4f_Xy-A7UWCDQ2a2BOCnP1tfNg',
                      ),
                      _PhotoTile(
                        url:
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuC8J84QRR1xD8zPC9S26FPYoy5Hz5oDpf3XrPvsg4LckGW8A3cu2H9m4beKmjGrfJpIn1p8Cee0JSmMIEzSVXfuxgubhE6b2eJlbJVjrwe7B3HJQIPp2bwxA63wbyJ3eku7Qvov5LwA6s74rZ5T-w02-IBzAY9cIyggotAmnRB1nShUCZxD6qhIHU5giU5E3hSMPXBQ-t4mat41DH6ndZ56fPnUXmCZAkUsrJcHA5CqAhg_EKi3lHxkoEn3tSHCCBzcIwOYZ2Svjg',
                      ),
                      _AddPhotoTile(primary: primary),
                    ],
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primary,
                  minimumSize: const Size.fromHeight(52),
                ),
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const QuickSellContactPriceScreen(),
                    ),
                  );
                },
                child: const Text(
                  'Next',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PhotoTile extends StatelessWidget {
  const _PhotoTile({required this.url, this.badge});

  final String url;
  final String? badge;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(14),
          child: Image.network(
            url,
            fit: BoxFit.cover,
            width: double.infinity,
            height: double.infinity,
            errorBuilder: (_, __, ___) => Container(
              color: Colors.grey.shade800,
              alignment: Alignment.center,
              child: const Icon(Icons.image_not_supported, color: Colors.white),
            ),
          ),
        ),
        Positioned(
          top: 8,
          right: 8,
          child: CircleAvatar(
            backgroundColor: Colors.black45,
            child: IconButton(
              padding: EdgeInsets.zero,
              icon: const Icon(Icons.close, size: 18, color: Colors.white),
              onPressed: () {},
            ),
          ),
        ),
        if (badge != null)
          Positioned(
            bottom: 10,
            left: 10,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.black54,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                badge!,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _AddPhotoTile extends StatelessWidget {
  const _AddPhotoTile({required this.primary});

  final Color primary;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(
          color: primary.withOpacity(0.3),
          style: BorderStyle.solid,
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircleAvatar(
              radius: 20,
              backgroundColor: primary.withOpacity(0.15),
              child: Icon(Icons.add_a_photo, color: primary),
            ),
            const SizedBox(height: 10),
            const Text(
              'Add More Photos',
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class QuickSellContactPriceScreen extends StatelessWidget {
  const QuickSellContactPriceScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    return Scaffold(
      backgroundColor: const Color(0xFF121212),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(
                      Icons.arrow_back_ios_new,
                      color: Colors.white70,
                    ),
                  ),
                  Expanded(
                    child: Text(
                      'Contact & Price',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.titleMedium?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      height: 6,
                      decoration: BoxDecoration(
                        color: primary.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Container(
                      height: 6,
                      decoration: BoxDecoration(
                        color: primary.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Container(
                      height: 6,
                      decoration: BoxDecoration(
                        color: primary,
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: [
                  const SizedBox(height: 8),
                  _DarkFormField(
                    label: 'Selling Price',
                    prefix: '\$',
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 16),
                  const _DarkFormField(
                    label: 'Phone Number',
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 16),
                  const _DarkFormField(
                    label: 'Email Address',
                    initialValue: 'user@example.com',
                    keyboardType: TextInputType.emailAddress,
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Your contact details will only be shared with interested buyers.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white54, fontSize: 12),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 20),
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primary,
                  minimumSize: const Size.fromHeight(54),
                ),
                onPressed: () => Navigator.of(context).popUntil(
                  (route) => route.isFirst,
                ),
                child: const Text(
                  'Publish Listing',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DarkFormField extends StatelessWidget {
  const _DarkFormField({
    required this.label,
    this.initialValue,
    this.prefix,
    this.keyboardType,
  });

  final String label;
  final String? initialValue;
  final String? prefix;
  final TextInputType? keyboardType;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          initialValue: initialValue,
          keyboardType: keyboardType,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            prefixText: prefix,
            prefixStyle: const TextStyle(color: Colors.white),
            filled: true,
            fillColor: const Color(0xFF1E1E1E),
            hintStyle: const TextStyle(color: Colors.white54),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFF2D2D2D)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFF2D2D2D)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFEF4444)),
            ),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 16,
            ),
          ),
        ),
      ],
    );
  }
}

class MessagesListScreen extends StatefulWidget {
  const MessagesListScreen({
    super.key,
    required this.api,
    required this.profile,
    required this.onOpenChat,
  });

  final ApiClient api;
  final Profile? profile;
  final ValueChanged<ChatSummary> onOpenChat;

  @override
  State<MessagesListScreen> createState() => _MessagesListScreenState();
}

class _MessagesListScreenState extends State<MessagesListScreen> {
  bool loading = false;
  String? error;
  List<ChatSummary> chats = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!widget.api.hasToken) return;
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final data = await widget.api.fetchChats();
      setState(() {
        chats = data;
      });
    } catch (e) {
      setState(() => error = e.toString());
    } finally {
      setState(() {
        loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    if (!widget.api.hasToken) {
      return Scaffold(
        body: SafeArea(
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.lock, color: primary, size: 48),
                const SizedBox(height: 12),
                Text(
                  'Войдите, чтобы видеть сообщения',
                  style: theme.textTheme.titleMedium,
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).maybePop(),
                    icon: Icon(
                      Icons.arrow_back_ios_new,
                      color: theme.brightness == Brightness.dark
                          ? Colors.grey.shade500
                          : Colors.grey.shade600,
                    ),
                  ),
                  Expanded(
                    child: Text(
                      'Messages',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: _load,
                    icon: const Icon(Icons.refresh),
                  ),
                ],
              ),
            ),
            if (loading)
              const Expanded(
                child: Center(child: CircularProgressIndicator()),
              )
            else if (error != null)
              Expanded(
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Не удалось загрузить чаты',
                        style: theme.textTheme.titleMedium,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '$error',
                        style: theme.textTheme.bodyMedium
                            ?.copyWith(color: Colors.grey),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 12),
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [primary, primary.withOpacity(0.8)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: primary.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: FilledButton(
                          style: FilledButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: _load,
                          child: const Text('Повторить'),
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else if (chats.isEmpty)
              Expanded(
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.chat_bubble_outline, size: 48),
                      const SizedBox(height: 8),
                      Text(
                        'Чатов пока нет',
                        style: theme.textTheme.titleMedium,
                      ),
                    ],
                  ),
                ),
              )
            else
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    itemCount: chats.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (context, index) {
                      final chat = chats[index];
                      return ListTile(
                        onTap: chat.listingId == null
                            ? null
                            : () => widget.onOpenChat(chat),
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 8),
                        leading: CircleAvatar(
                          radius: 28,
                          backgroundImage: chat.imageUrl != null &&
                                  chat.imageUrl!.isNotEmpty
                              ? NetworkImage(chat.imageUrl!)
                              : null,
                          backgroundColor: Colors.grey.shade300,
                          child: chat.imageUrl == null || chat.imageUrl!.isEmpty
                              ? const Icon(Icons.directions_car)
                              : null,
                        ),
                        title: Text(
                          chat.title,
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        subtitle: Text(
                          chat.lastMessage,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        trailing: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              chat.time,
                              style: theme.textTheme.bodySmall,
                            ),
                            if (chat.unread > 0)
                              Container(
                                margin: const EdgeInsets.only(top: 6),
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: theme.colorScheme.primary,
                                  shape: BoxShape.circle,
                                ),
                                child: Text(
                                  '${chat.unread}',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class ChatScreen extends StatefulWidget {
  const ChatScreen({
    super.key,
    required this.chat,
    required this.api,
    required this.myProfile,
  });

  final ChatSummary chat;
  final ApiClient api;
  final Profile? myProfile;

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  List<ChatMessage> messages = [];
  bool loading = false;
  String? error;
  final TextEditingController _controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (widget.chat.listingId == null) return;
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final data = await widget.api.fetchListingMessages(
        listingId: widget.chat.listingId!,
        myUserId: widget.myProfile?.id.toString(),
      );
      setState(() {
        messages = data;
      });
    } catch (e) {
      setState(() {
        error = e.toString();
      });
    } finally {
      setState(() => loading = false);
    }
  }

  Future<void> _send() async {
    final text = _controller.text.trim();
    if (text.isEmpty || widget.chat.listingId == null) return;
    _controller.clear();
    final temp = ChatMessage(
      id: DateTime.now().millisecondsSinceEpoch,
      listingId: widget.chat.listingId,
      body: text,
      isMine: true,
      createdAt: 'now',
    );
    setState(() {
      messages = [...messages, temp];
    });
    try {
      await widget.api.sendListingMessage(
        listingId: widget.chat.listingId!,
        body: text,
      );
      await _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Не отправилось: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    return Scaffold(
      backgroundColor: const Color(0xFF121212),
      body: SafeArea(
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF121212).withOpacity(0.9),
                border: const Border(
                  bottom: BorderSide(color: Color(0xFF2A2A2A)),
                ),
              ),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(
                      Icons.arrow_back_ios_new,
                      color: Colors.white,
                    ),
                  ),
                  CircleAvatar(
                    backgroundImage: widget.chat.imageUrl != null
                        ? NetworkImage(widget.chat.imageUrl!)
                        : null,
                    backgroundColor: Colors.grey.shade300,
                    child: widget.chat.imageUrl == null
                        ? const Icon(Icons.directions_car)
                        : null,
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.chat.title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const Text(
                        'Online',
                        style: TextStyle(color: Colors.white70, fontSize: 12),
                      ),
                    ],
                  ),
                  const Spacer(),
                  IconButton(
                    onPressed: _load,
                    icon: const Icon(Icons.refresh, color: Colors.white),
                  ),
                ],
              ),
            ),
            if (loading)
              const Expanded(
                child: Center(child: CircularProgressIndicator()),
              )
            else if (error != null)
              Expanded(
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Не удалось загрузить чат',
                        style: theme.textTheme.titleMedium
                            ?.copyWith(color: Colors.white),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '$error',
                        style: const TextStyle(color: Colors.white70),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 12),
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [primary, primary.withOpacity(0.8)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: primary.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: FilledButton(
                          style: FilledButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: _load,
                          child: const Text('Повторить'),
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final msg = messages[index];
                    if (msg.isMine) {
                      return _OutgoingBubble(
                        text: msg.body,
                        time: msg.createdAt ?? '',
                        primary: primary,
                        delivered: false,
                        imageUrl: msg.imageUrl,
                      );
                    }
                    return _IncomingBubble(
                      text: msg.body,
                      time: msg.createdAt ?? '',
                      avatarUrl: widget.chat.imageUrl ??
                          'https://via.placeholder.com/64',
                    );
                  },
                ),
              ),
            Container(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
              decoration: const BoxDecoration(
                color: Color(0xFF121212),
                border: Border(
                  top: BorderSide(color: Color(0xFF2A2A2A)),
                ),
              ),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () {},
                    icon: const Icon(Icons.add_circle, color: Colors.white70),
                  ),
                  Expanded(
                    child: TextField(
                      controller: _controller,
                      style: const TextStyle(color: Colors.white),
                      decoration: InputDecoration(
                        hintText: 'Type a message...',
                        hintStyle: const TextStyle(color: Colors.white60),
                        filled: true,
                        fillColor: const Color(0xFF2A2A2A),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 14,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(999),
                          borderSide: BorderSide.none,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  CircleAvatar(
                    backgroundColor: primary,
                    child: IconButton(
                      icon: const Icon(Icons.send, color: Colors.white),
                      onPressed: _send,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _IncomingBubble extends StatelessWidget {
  const _IncomingBubble({
    required this.text,
    required this.time,
    required this.avatarUrl,
    this.showTyping = false,
  });

  final String text;
  final String time;
  final String avatarUrl;
  final bool showTyping;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        CircleAvatar(
          radius: 16,
          backgroundImage: NetworkImage(avatarUrl),
          backgroundColor: Colors.grey.shade300,
        ),
        const SizedBox(width: 8),
        Flexible(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                margin: const EdgeInsets.only(bottom: 6, top: 6),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF2A2A2A),
                  borderRadius: BorderRadius.circular(12).copyWith(
                    bottomLeft: Radius.zero,
                  ),
                ),
                child: showTyping
                    ? Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          _TypingDot(delay: -0.3),
                          SizedBox(width: 4),
                          _TypingDot(delay: -0.15),
                          SizedBox(width: 4),
                          _TypingDot(),
                        ],
                      )
                    : Text(
                        text,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                        ),
                      ),
              ),
              Text(
                time,
                style: const TextStyle(color: Colors.white60, fontSize: 11),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _OutgoingBubble extends StatelessWidget {
  const _OutgoingBubble({
    required this.text,
    required this.time,
    required this.primary,
    this.delivered = false,
    this.imageUrl,
  });

  final String text;
  final String time;
  final Color primary;
  final bool delivered;
  final String? imageUrl;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.end,
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Flexible(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              if (imageUrl != null)
                Container(
                  margin: const EdgeInsets.only(bottom: 6, top: 12),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  clipBehavior: Clip.hardEdge,
                  child: Image.network(
                    imageUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      color: Colors.grey.shade800,
                      height: 160,
                      alignment: Alignment.center,
                      child: const Icon(
                        Icons.image_not_supported,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              Container(
                margin: const EdgeInsets.only(bottom: 6, top: 6),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [primary, Colors.orange.shade500],
                  ),
                  borderRadius: BorderRadius.circular(12).copyWith(
                    bottomRight: Radius.zero,
                  ),
                ),
                child: Text(
                  text,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                  ),
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    time,
                    style:
                        const TextStyle(color: Colors.white60, fontSize: 11),
                  ),
                  if (delivered) ...[
                    const SizedBox(width: 4),
                    const Icon(
                      Icons.done_all,
                      size: 16,
                      color: Colors.lightBlueAccent,
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _TypingDot extends StatelessWidget {
  const _TypingDot({this.delay = 0});

  final double delay;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 700),
      curve: Curves.easeInOut,
      width: 6,
      height: 6,
      margin: EdgeInsets.only(
        left: delay == 0 ? 0 : 0,
      ),
      decoration: const BoxDecoration(
        color: Colors.white54,
        shape: BoxShape.circle,
      ),
    );
  }
}


class ProfileScreen extends StatefulWidget {
  const ProfileScreen({
    super.key,
    required this.api,
    required this.profile,
    required this.authLoading,
    required this.authError,
    required this.onLogin,
    required this.onLogout,
    required this.onRefreshProfile,
    required this.onOpenLogin,
  });

  final ApiClient api;
  final Profile? profile;
  final bool authLoading;
  final String? authError;
  final Future<void> Function(String login, String password) onLogin;
  final VoidCallback onLogout;
  final Future<void> Function() onRefreshProfile;
  final VoidCallback onOpenLogin;

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _loginController = TextEditingController();
  final _passwordController = TextEditingController();
  List<Listing> favorites = [];
  bool favLoading = false;
  String? favError;

  @override
  void didUpdateWidget(covariant ProfileScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.profile != null && oldWidget.profile != widget.profile) {
      _loadFavorites();
    }
  }

  Future<void> _loadFavorites() async {
    if (!widget.api.hasToken) return;
    setState(() {
      favLoading = true;
      favError = null;
    });
    try {
      final data = await widget.api.fetchFavorites();
      setState(() => favorites = data);
    } catch (e) {
      setState(() => favError = e.toString());
    } finally {
      setState(() => favLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    if (widget.profile == null) {
      return Scaffold(
        body: SafeArea(
          child: _UnauthedProfile(
            onOpenLogin: widget.onOpenLogin,
          ),
        ),
      );
    }

    final profile = widget.profile!;

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            await widget.onRefreshProfile();
            await _loadFavorites();
          },
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 32,
                    backgroundColor:
                        isDark ? Colors.grey.shade800 : Colors.grey.shade200,
                    child: const Icon(Icons.person, size: 32),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        profile.name,
                        style: theme.textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (profile.email != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          profile.email!,
                          style: theme.textTheme.bodyMedium?.copyWith(
                            color: Colors.grey,
                          ),
                        ),
                      ],
                      if (profile.phone != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          profile.phone!,
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 20),
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  _ProfileStat(label: 'Role', value: profile.role ?? '—'),
                  _ProfileStat(label: 'Favorites', value: '${favorites.length}'),
                  _ProfileStat(label: 'Listings', value: '—'),
                ],
              ),
              const SizedBox(height: 20),
              Text(
                'Избранное',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              if (favLoading)
                const Center(child: CircularProgressIndicator())
              else if (favError != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Не удалось загрузить избранное',
                      style: theme.textTheme.bodyMedium,
                    ),
                    TextButton(
                      onPressed: _loadFavorites,
                      child: const Text('Повторить'),
                    ),
                  ],
                )
              else if (favorites.isEmpty)
                Text(
                  'Пока пусто',
                  style: theme.textTheme.bodySmall,
                )
              else
                ...favorites.map(
                  (fav) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: _ListingTile(
                      car: fav,
                      onFavorite: () {},
                      onTap: () {},
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              _ProfileTile(
                icon: Icons.list,
                title: 'My Listings',
                subtitle: 'View and manage your listings',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => MyListingsScreen(api: widget.api)),
                  );
                },
              ),
              _ProfileTile(
                icon: Icons.gavel,
                title: 'My Auctions',
                subtitle: 'Track your auction listings',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => MyAuctionsScreen(api: widget.api)),
                  );
                },
              ),
              _ProfileTile(
                icon: Icons.calculate,
                title: 'Import Calculator',
                subtitle: 'Calculate import costs',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => CalculatorScreen(api: widget.api)),
                  );
                },
              ),
              _ProfileTile(
                icon: Icons.notifications,
                title: 'Notifications',
                subtitle: 'Manage notification settings',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => SettingsScreen(api: widget.api)),
                  );
                },
              ),
              _ProfileTile(
                icon: Icons.settings,
                title: 'Settings',
                subtitle: 'Language, theme, notifications',
              ),
              _ProfileTile(
                icon: Icons.logout,
                title: 'Logout',
                subtitle: 'Sign out of the account',
                onTap: widget.onLogout,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ProfileStat extends StatelessWidget {
  const _ProfileStat({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Container(
      width: 110,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
      decoration: BoxDecoration(
        color: isDark ? Colors.grey.shade900 : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value,
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: theme.textTheme.bodySmall?.copyWith(
              color: Colors.grey,
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileTile extends StatelessWidget {
  const _ProfileTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? Colors.grey.shade900 : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        leading: Icon(icon, color: theme.colorScheme.primary),
        title: Text(
          title,
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w600,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: theme.textTheme.bodySmall,
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({
    super.key,
    required this.api,
    required this.onSubmit,
    required this.onOpenRegister,
    required this.onGoogleSignIn,
  });

  final ApiClient api;
  final Future<void> Function(String login, String password) onSubmit;
  final VoidCallback onOpenRegister;
  final Future<void> Function() onGoogleSignIn;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _loginController = TextEditingController();
  final _passwordController = TextEditingController();
  bool loading = false;
  String? error;

  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  Future<void> _submit() async {
    if (_loginController.text.trim().isEmpty || _passwordController.text.trim().isEmpty) {
      setState(() => error = 'Заполните все поля');
      return;
    }

    setState(() {
      loading = true;
      error = null;
    });
    
    print('🔐 Attempting login with: ${_loginController.text.trim()}');
    
    try {
      await widget.onSubmit(
        _loginController.text.trim(),
        _passwordController.text.trim(),
      );
      print('✅ Login successful');
      // Success - screen will be closed by parent
    } catch (e) {
      print('❌ Login failed: $e');
      setState(() => error = e.toString().replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  Future<void> _handleGoogleSignIn() async {
    setState(() {
      loading = true;
      error = null;
    });

    try {
      // Check if running on desktop - show helpful message
      if (Theme.of(context).platform == TargetPlatform.linux ||
          Theme.of(context).platform == TargetPlatform.windows ||
          Theme.of(context).platform == TargetPlatform.macOS) {
        throw Exception(
          'Google Sign-In работает только на Android/iOS.\n\n'
          'На компьютере используйте email/телефон.\n\n'
          'Или запустите на Android эмуляторе.'
        );
      }

      final account = await _googleSignIn.signIn();
      
      if (account == null) {
        setState(() => loading = false);
        return;
      }

      final auth = await account.authentication;
      final idToken = auth.idToken;

      if (idToken == null) {
        throw Exception('Failed to get Google ID token');
      }

      // Call API
      final profile = await widget.api.loginWithGoogle(idToken);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Welcome, ${profile.name}!')),
        );
        Navigator.of(context).pop(); // Close login screen
      }
    } catch (e) {
      setState(() {
        error = e.toString().replaceFirst('Exception: ', '');
        loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final primary = theme.colorScheme.primary;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF111111), Color(0xFF1E0C0C)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const _Logo(),
                    const SizedBox(width: 10),
                    Text(
                      'idrom.am',
                      style: theme.textTheme.headlineSmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                Text(
                  'Войти',
                  style: theme.textTheme.headlineMedium?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Используйте e-mail или телефон из веб-версии',
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: Colors.white70,
                  ),
                ),
                const SizedBox(height: 24),
                _GlassCard(
                  child: Column(
                    children: [
                      _InputField(
                        controller: _loginController,
                        label: 'Email или телефон',
                        icon: Icons.person_outline,
                      ),
                      const SizedBox(height: 12),
                      _InputField(
                        controller: _passwordController,
                        label: 'Пароль',
                        icon: Icons.lock_outline,
                        obscure: true,
                      ),
                      if (error != null) ...[
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.red.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.red),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.error_outline, color: Colors.red, size: 20),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  error!,
                                  style: const TextStyle(color: Colors.red, fontSize: 13),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      Container(
                        width: double.infinity,
                        height: 50,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [primary, primary.withOpacity(0.8)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: primary.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: loading ? null : _submit,
                          child: loading
                              ? SizedBox(
                                  width: 18,
                                  height: 18,
                                  child:
                                      CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation<Color>(Colors.white)),
                                )
                              : Text(
                                  'Войти',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(child: Divider(color: Colors.white30)),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            child: Text(
                              'или',
                              style: TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                          ),
                          Expanded(child: Divider(color: Colors.white30)),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Container(
                        width: double.infinity,
                        height: 50,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.white30),
                          color: Colors.white.withOpacity(0.05),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.2),
                              blurRadius: 4,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            side: BorderSide(color: Colors.white30),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: loading ? null : _handleGoogleSignIn,
                          icon: Icon(Icons.g_mobiledata, color: Colors.white, size: 28),
                          label: Text(
                            'Войти через Google',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        width: double.infinity,
                        height: 40,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [Colors.grey.shade600, Colors.grey.shade700],
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: TextButton(
                          onPressed: loading ? null : widget.onOpenRegister,
                          style: TextButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            padding: EdgeInsets.symmetric(vertical: 8),
                          ),
                          child: Text('Зарегистрироваться',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _GlassCard extends StatelessWidget {
  const _GlassCard({required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.06),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white10),
        boxShadow: const [
          BoxShadow(
            color: Colors.black26,
            blurRadius: 16,
            offset: Offset(0, 10),
          ),
        ],
      ),
      child: child,
    );
  }
}

class _InputField extends StatelessWidget {
  const _InputField({
    this.controller,
    required this.label,
    required this.icon,
    this.obscure = false,
  });

  final TextEditingController? controller;
  final String label;
  final IconData icon;
  final bool obscure;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscure,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Colors.white70),
        prefixIcon: Icon(icon, color: Colors.white70),
        filled: true,
        fillColor: Colors.white.withOpacity(0.05),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.12)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.redAccent),
        ),
      ),
    );
  }
}

// RegisterScreen has been moved to screens/register_screen.dart

class _UnauthedProfile extends StatelessWidget {
  const _UnauthedProfile({
    required this.onOpenLogin,
  });

  final VoidCallback onOpenLogin;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.person_outline,
              size: 80,
              color: theme.colorScheme.primary.withOpacity(0.5),
            ),
            const SizedBox(height: 24),
            Text(
              'Войдите в аккаунт',
              style: theme.textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Чтобы создавать объявления, общаться с продавцами и сохранять избранное',
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyMedium?.copyWith(
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: onOpenLogin,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: theme.colorScheme.primary,
                ),
                icon: const Icon(Icons.login, color: Colors.white),
                label: const Text(
                  'Войти или зарегистрироваться',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
