package com.flippam.mobile.ui.navigation

import androidx.annotation.DrawableRes
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.ChatBubbleOutline
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.PersonOutline
import androidx.compose.material.icons.outlined.Search
import androidx.compose.ui.graphics.vector.ImageVector

sealed class AppDestination(
    val route: String,
    val label: String,
    val icon: ImageVector,
) {
    data object Home : AppDestination("home", "Главная", Icons.Outlined.Home)
    data object Search : AppDestination("search", "Поиск", Icons.Outlined.Search)
    data object Add : AppDestination("add", "Добавить", Icons.Outlined.Add)
    data object Messages : AppDestination("messages", "Чаты", Icons.Outlined.ChatBubbleOutline)
    data object Profile : AppDestination("profile", "Профиль", Icons.Outlined.PersonOutline)

    companion object {
        val bottomNavItems = listOf(Home, Search, Add, Messages, Profile)
    }
}
