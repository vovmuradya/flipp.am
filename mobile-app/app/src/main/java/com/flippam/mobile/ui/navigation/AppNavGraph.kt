package com.flippam.mobile.ui.navigation

import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.flippam.mobile.ui.listings.ListingsScreen
import com.flippam.mobile.ui.listings.ListingsUiState
import com.flippam.mobile.ui.screens.AddListingScreen
import com.flippam.mobile.ui.screens.CarDetailsScreen
import com.flippam.mobile.ui.screens.ChatConversationScreen
import com.flippam.mobile.ui.screens.Conversation
import com.flippam.mobile.ui.screens.EditProfileScreen
import com.flippam.mobile.ui.screens.MessagingScreen
import com.flippam.mobile.ui.screens.SellerProfileScreen
import com.flippam.mobile.ui.screens.UserProfileScreen

@Composable
fun AppNavGraph(
    listingsState: ListingsUiState,
    onRetryListings: () -> Unit,
) {
    val navController = rememberNavController()
    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentDestination = AppDestination.bottomNavItems.firstOrNull { it.route == navBackStackEntry?.destination?.route }
        ?: AppDestination.Home

    val conversations = listOf(
        Conversation(1, "Мария", "Покажете авто сегодня?", "11:20", unread = true),
        Conversation(2, "Алексей", "Спасибо за встречу!", "Вчера"),
        Conversation(3, "Анна", "Отправила предоплату", "Пн"),
    )

    NavHost(
        navController = navController,
        startDestination = AppDestination.Home.route,
    ) {
        composable(AppDestination.Home.route) {
            ListingsScreen(
                state = listingsState,
                onRetry = onRetryListings,
                onListingClick = { listing -> navController.navigate("details/${listing.id}") },
                onOpenAdd = { navController.navigate(AppDestination.Add.route) },
                selectedDestination = currentDestination,
                onSelectDestination = { destination ->
                    if (destination == AppDestination.Add) {
                        navController.navigate(destination.route)
                    } else {
                        navController.navigate(destination.route) {
                            launchSingleTop = true
                            popUpTo(AppDestination.Home.route)
                        }
                    }
                },
            )
        }
        composable(AppDestination.Search.route) {
            ListingsScreen(
                state = listingsState,
                onRetry = onRetryListings,
                onListingClick = { listing -> navController.navigate("details/${listing.id}") },
                onOpenAdd = { navController.navigate(AppDestination.Add.route) },
                selectedDestination = currentDestination,
                onSelectDestination = { destination ->
                    navController.navigate(destination.route) { launchSingleTop = true }
                },
            )
        }
        composable(AppDestination.Messages.route) {
            MessagingScreen(
                conversations = conversations,
                onOpenChat = { chat -> navController.navigate("chat/${chat.id}") },
            )
        }
        composable(AppDestination.Profile.route) {
            UserProfileScreen(
                onEditProfile = { navController.navigate("editProfile") },
            )
        }
        composable(
            route = "details/{id}",
            arguments = listOf(navArgument("id") { type = NavType.LongType }),
        ) { backStackEntry ->
            val listingId = backStackEntry.arguments?.getLong("id")
            val listing = listingsState.listings.firstOrNull { it.id == listingId } ?: listingsState.listings.firstOrNull()
            if (listing != null) {
                CarDetailsScreen(
                    listing = listing,
                    onBack = { navController.popBackStack() },
                    onContact = { navController.navigate(AppDestination.Messages.route) },
                    onOpenSeller = { navController.navigate("sellerProfile") },
                )
            }
        }
        composable(
            route = "chat/{id}",
            arguments = listOf(navArgument("id") { type = NavType.LongType }),
        ) {
            ChatConversationScreen(
                title = "Диалог",
                onBack = { navController.popBackStack() },
            )
        }
        composable("sellerProfile") {
            SellerProfileScreen(onBack = { navController.popBackStack() })
        }
        composable("editProfile") {
            EditProfileScreen(onBack = { navController.popBackStack() })
        }
        composable(AppDestination.Add.route) {
            AddListingScreen(onBack = { navController.popBackStack() })
        }
    }
}
