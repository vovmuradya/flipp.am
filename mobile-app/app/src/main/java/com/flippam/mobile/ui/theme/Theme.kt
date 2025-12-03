package com.flippam.mobile.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val LightColors = lightColorScheme(
    primary = PrimaryRed,
    onPrimary = androidx.compose.ui.graphics.Color.White,
    background = BackgroundLight,
    surface = BackgroundLight,
    onSurface = androidx.compose.ui.graphics.Color.Black,
)

private val DarkColors = darkColorScheme(
    primary = PrimaryRed,
    onPrimary = androidx.compose.ui.graphics.Color.White,
    background = BackgroundDark,
    surface = SurfaceDark,
    onSurface = androidx.compose.ui.graphics.Color.White,
    onSurfaceVariant = TextSecondaryDark,
)

@Composable
fun IdromTheme(
    useDarkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    val colors = if (useDarkTheme) DarkColors else LightColors

    MaterialTheme(
        colorScheme = colors,
        typography = Typography,
        content = content,
    )
}
