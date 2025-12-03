package com.flippam.mobile.ui.listings

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.ArrowBack
import androidx.compose.material.icons.outlined.Notifications
import androidx.compose.material.icons.outlined.PersonOutline
import androidx.compose.material.icons.outlined.StarBorder
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.flippam.mobile.data.repository.Listing
import com.flippam.mobile.ui.navigation.AppDestination

@Composable
fun ListingsScreen(
    state: ListingsUiState,
    onRetry: () -> Unit,
    onListingClick: (Listing) -> Unit,
    onOpenAdd: () -> Unit,
    selectedDestination: AppDestination,
    onSelectDestination: (AppDestination) -> Unit,
) {
    var showSortSheet by rememberSaveable { mutableStateOf(false) }
    val sheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)

    Surface(color = MaterialTheme.colorScheme.background) {
        Scaffold(
            bottomBar = {
                NavigationBar {
                    AppDestination.bottomNavItems.forEach { destination ->
                        NavigationBarItem(
                            selected = destination == selectedDestination,
                            onClick = {
                                if (destination == AppDestination.Add) {
                                    onOpenAdd()
                                } else {
                                    onSelectDestination(destination)
                                }
                            },
                            icon = { Icon(destination.icon, contentDescription = destination.label) },
                        )
                    }
                }
            },
        ) { paddingValues ->
            when {
                state.isLoading -> LoadingState(modifier = Modifier.padding(paddingValues))
                state.errorMessage != null -> ErrorState(
                    modifier = Modifier.padding(paddingValues),
                    message = state.errorMessage,
                    onRetry = onRetry,
                )
                else -> ListingsContent(
                    modifier = Modifier.padding(paddingValues),
                    listings = state.listings,
                    onListingClick = onListingClick,
                    onOpenSort = { showSortSheet = true },
                )
            }

            if (showSortSheet) {
                ModalBottomSheet(
                    sheetState = sheetState,
                    onDismissRequest = { showSortSheet = false },
                ) {
                    SortSheet(
                        selectedOption = "Сначала новые",
                        onSelect = { _ -> showSortSheet = false },
                        onClose = { showSortSheet = false },
                    )
                }
            }
        }
    }
}

@Composable
private fun ListingsContent(
    modifier: Modifier = Modifier,
    listings: List<Listing>,
    onListingClick: (Listing) -> Unit,
    onOpenSort: () -> Unit,
) {
    val highlights = remember(listings) {
        if (listings.size >= 4) listings.take(4) else sampleHighlights
    }

    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(bottom = 96.dp),
    ) {
        item {
            HeaderBar()
        }

        item {
            HighlightCarousel(highlights = highlights, onClick = onListingClick)
        }

        item {
            SortRow(onOpenSort = onOpenSort)
        }

        if (listings.isEmpty()) {
            item { EmptyState(modifier = Modifier.fillMaxWidth().padding(24.dp)) }
        } else {
            items(listings, key = { it.id }) { listing ->
                CompactListingRow(
                    listing = listing,
                    onClick = { onListingClick(listing) },
                )
            }
        }
    }
}

@Composable
private fun HeaderBar() {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(start = 16.dp, end = 16.dp, top = 32.dp, bottom = 8.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.SpaceBetween,
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(
                imageVector = Icons.Outlined.StarBorder,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.primary,
                modifier = Modifier.size(28.dp),
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "drom.am",
                style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
            )
        }
        Box {
            IconButton(onClick = { }) {
                Icon(
                    imageVector = Icons.Outlined.Notifications,
                    contentDescription = "Уведомления",
                )
            }
            Box(
                modifier = Modifier
                    .size(8.dp)
                    .offset(x = 6.dp, y = 6.dp)
                    .clip(CircleShape)
                    .background(MaterialTheme.colorScheme.primary)
            )
        }
    }
}

@Composable
private fun HighlightCarousel(
    highlights: List<Listing>,
    onClick: (Listing) -> Unit,
) {
    Column(modifier = Modifier.fillMaxWidth()) {
        LazyRow(
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            items(highlights, key = { it.id }) { listing ->
                Box(
                    modifier = Modifier
                        .width(240.dp)
                        .height(180.dp)
                        .clip(RoundedCornerShape(16.dp))
                        .background(Color.Gray),
                ) {
                    AsyncImage(
                        model = listing.previewPhoto,
                        contentDescription = listing.title,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize(),
                    )
                    Box(
                        modifier = Modifier
                            .matchParentSize()
                            .background(
                                Brush.verticalGradient(
                                    colors = listOf(Color.Black.copy(alpha = 0.5f), Color.Transparent),
                                    startY = 300f,
                                    endY = 0f,
                                )
                            )
                    )
                    Column(
                        modifier = Modifier
                            .align(Alignment.BottomStart)
                            .padding(12.dp),
                    ) {
                        Text(
                            text = listing.title,
                            style = MaterialTheme.typography.titleMedium.copy(color = Color.White),
                            maxLines = 1,
                        )
                        Text(
                            text = listing.price?.amount?.let { String.format("%,.0f %s", it, listing.price.currency ?: "USD") }
                                ?: "Цена по запросу",
                            style = MaterialTheme.typography.bodyMedium.copy(color = Color.White),
                        )
                    }
                }
            }
        }
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 8.dp),
            horizontalArrangement = Arrangement.Center,
        ) {
            repeat(4) { index ->
                Box(
                    modifier = Modifier
                        .padding(horizontal = 4.dp)
                        .size(8.dp)
                        .clip(CircleShape)
                        .background(if (index == 0) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface.copy(alpha = 0.2f))
                )
            }
        }
    }
}

@Composable
private fun SortRow(onOpenSort: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 8.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Text(
            text = "Сортировка",
            style = MaterialTheme.typography.bodyMedium.copy(color = MaterialTheme.colorScheme.primary),
        )
        Button(
            onClick = onOpenSort,
            shape = RoundedCornerShape(10.dp),
        ) {
            Icon(imageVector = Icons.Outlined.SwapVert, contentDescription = null)
            Spacer(modifier = Modifier.width(6.dp))
            Text(text = "Сначала новые")
        }
    }
}

@Composable
private fun CompactListingRow(
    listing: Listing,
    onClick: () -> Unit,
) {
    Surface(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 6.dp),
        shape = RoundedCornerShape(14.dp),
        tonalElevation = 1.dp,
        shadowElevation = 2.dp,
        onClick = onClick,
    ) {
        Row(
            modifier = Modifier.padding(12.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            AsyncImage(
                model = listing.previewPhoto,
                contentDescription = listing.title,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .size(width = 120.dp, height = 90.dp)
                    .clip(RoundedCornerShape(12.dp)),
            )
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.SemiBold),
                    maxLines = 1,
                )
                listing.vehicleSummary?.let {
                    Text(
                        text = it,
                        style = MaterialTheme.typography.bodySmall.copy(color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f)),
                        maxLines = 1,
                    )
                }
                Spacer(modifier = Modifier.height(6.dp))
                Text(
                    text = listing.price?.amount?.let { String.format("%,.0f %s", it, listing.price.currency ?: "USD") }
                        ?: "Цена по запросу",
                    style = MaterialTheme.typography.titleMedium.copy(color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.Bold),
                )
            }
            IconButton(onClick = { }) {
                Icon(
                    imageVector = Icons.Outlined.PersonOutline,
                    contentDescription = "В избранное",
                    tint = MaterialTheme.colorScheme.primary,
                )
            }
        }
    }
}

@Composable
private fun LoadingState(modifier: Modifier = Modifier) {
    Box(
        modifier = modifier.fillMaxSize(),
        contentAlignment = Alignment.Center,
    ) {
        CircularProgressIndicator()
    }
}

@Composable
private fun ErrorState(
    modifier: Modifier = Modifier,
    message: String,
    onRetry: () -> Unit,
) {
    Column(
        modifier = modifier
            .fillMaxSize()
            .padding(24.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(
            text = message,
            style = MaterialTheme.typography.titleMedium,
            textAlign = TextAlign.Center,
        )
        Spacer(modifier = Modifier.height(16.dp))
        Button(onClick = onRetry) {
            Text(text = "Повторить")
        }
    }
}

@Composable
private fun EmptyState(modifier: Modifier = Modifier) {
    Column(
        modifier = modifier,
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(
            modifier = Modifier.fillMaxWidth(),
            text = "Пока нет объявлений",
            textAlign = TextAlign.Center,
            style = MaterialTheme.typography.titleMedium,
        )
        Spacer(modifier = Modifier.height(12.dp))
        Text(
            text = "Попробуйте изменить фильтры",
            textAlign = TextAlign.Center,
            style = MaterialTheme.typography.bodyMedium,
        )
    }
}

@Composable
private fun SortSheet(
    selectedOption: String,
    onSelect: (String) -> Unit,
    onClose: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 12.dp),
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(vertical = 8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Text(
                text = "Сортировка",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
            )
            IconButton(onClick = onClose) {
                Icon(Icons.Outlined.ArrowBack, contentDescription = "Закрыть")
            }
        }
        val options = listOf("Сначала новые", "Цена: от дешевых", "Цена: от дорогих")
        options.forEach { option ->
            Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(vertical = 6.dp),
                shape = RoundedCornerShape(12.dp),
                tonalElevation = if (option == selectedOption) 2.dp else 0.dp,
                onClick = { onSelect(option) },
            ) {
                Row(
                    modifier = Modifier.padding(horizontal = 14.dp, vertical = 12.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween,
                ) {
                    Text(
                        text = option,
                        style = MaterialTheme.typography.bodyLarge.copy(
                            color = if (option == selectedOption) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface
                        ),
                    )
                    if (option == selectedOption) {
                        Icon(
                            imageVector = Icons.Outlined.StarBorder,
                            contentDescription = null,
                            tint = MaterialTheme.colorScheme.primary,
                        )
                    }
                }
            }
        }
    }
}

private val sampleHighlights = listOf(
    Listing(
        id = 0,
        title = "Porsche 911 Carrera",
        listingType = null,
        status = null,
        location = "Yerevan",
        price = com.flippam.mobile.data.repository.Money(150000.0, "USD"),
        outTheDoorPrice = null,
        priceBadge = null,
        priceAnomaly = false,
        buyNowPrice = null,
        vehicleSummary = "2023 • 5 000 км",
        mileage = 5000,
        previewPhoto = "https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=800&q=60",
        isFavorite = false,
        favoritesCount = 0,
        sellerScore = null,
    ),
    Listing(
        id = 1,
        title = "Lamborghini Huracan",
        listingType = null,
        status = null,
        location = "Moscow",
        price = com.flippam.mobile.data.repository.Money(256000.0, "USD"),
        outTheDoorPrice = null,
        priceBadge = null,
        priceAnomaly = false,
        buyNowPrice = null,
        vehicleSummary = "2022 • 3 200 км",
        mileage = 3200,
        previewPhoto = "https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=800&q=60",
        isFavorite = false,
        favoritesCount = 0,
        sellerScore = null,
    ),
)
