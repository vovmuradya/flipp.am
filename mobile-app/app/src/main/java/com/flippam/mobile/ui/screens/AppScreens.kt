package com.flippam.mobile.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.ArrowBack
import androidx.compose.material.icons.outlined.Call
import androidx.compose.material.icons.outlined.ChatBubbleOutline
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.FavoriteBorder
import androidx.compose.material.icons.outlined.LocationOn
import androidx.compose.material.icons.outlined.MoreVert
import androidx.compose.material.icons.outlined.PersonOutline
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material3.Button
import androidx.compose.material3.Divider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.flippam.mobile.data.repository.Listing

@Composable
fun CarDetailsScreen(
    listing: Listing,
    onBack: () -> Unit,
    onContact: () -> Unit,
    onOpenSeller: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background),
    ) {
        Box {
            AsyncImage(
                model = listing.previewPhoto,
                contentDescription = listing.title,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(260.dp),
            )
            Box(
                modifier = Modifier
                    .matchParentSize()
                    .background(
                        Brush.verticalGradient(
                            colors = listOf(Color.Black.copy(alpha = 0.45f), Color.Transparent),
                            startY = 200f,
                            endY = 0f,
                        )
                    )
            )
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(12.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                IconButton(onClick = onBack) {
                    Icon(Icons.Outlined.ArrowBack, contentDescription = "Назад", tint = Color.White)
                }
                IconButton(onClick = { }) {
                    Icon(Icons.Outlined.FavoriteBorder, contentDescription = "Избранное", tint = Color.White)
                }
            }
        }

        Column(
            modifier = Modifier
                .weight(1f)
                .verticalScroll(rememberScrollState())
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            Text(
                text = listing.title,
                style = MaterialTheme.typography.headlineSmall.copy(fontWeight = FontWeight.Bold),
            )
            Text(
                text = listing.price?.amount?.let { String.format("%,.0f %s", it, listing.price.currency ?: "USD") }
                    ?: "Цена по запросу",
                style = MaterialTheme.typography.headlineSmall.copy(color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.Bold),
            )
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                InfoTag(label = "Год", value = listing.vehicleSummary?.take(4) ?: "2023")
                InfoTag(label = "Пробег", value = listing.mileage?.let { "$it км" } ?: "5 000 км")
                InfoTag(label = "Коробка", value = "АКПП")
            }
            Divider()
            Text(
                text = "Локация",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Outlined.LocationOn, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                Spacer(modifier = Modifier.width(4.dp))
                Text(text = listing.location ?: "Ереван, Армения")
            }
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(160.dp)
                    .clip(RoundedCornerShape(12.dp))
                    .background(Color.Gray.copy(alpha = 0.2f)),
                contentAlignment = Alignment.Center,
            ) {
                Text(text = "Карта", color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
            }
            Spacer(modifier = Modifier.height(32.dp))
        }
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            OutlinedButton(
                modifier = Modifier.weight(1f),
                onClick = onOpenSeller,
                shape = RoundedCornerShape(12.dp),
            ) {
                Icon(Icons.Outlined.PersonOutline, contentDescription = null)
                Spacer(modifier = Modifier.width(6.dp))
                Text(text = "Продавец")
            }
            OutlinedButton(
                modifier = Modifier.weight(1f),
                onClick = onContact,
                shape = RoundedCornerShape(12.dp),
            ) {
                Icon(Icons.Outlined.Call, contentDescription = null)
                Spacer(modifier = Modifier.width(6.dp))
                Text(text = "Предложение")
            }
            Button(
                modifier = Modifier.weight(1f),
                onClick = onContact,
                shape = RoundedCornerShape(12.dp),
            ) {
                Icon(Icons.Outlined.ChatBubbleOutline, contentDescription = null)
                Spacer(modifier = Modifier.width(6.dp))
                Text(text = "Связаться")
            }
        }
    }
}

@Composable
private fun InfoTag(label: String, value: String) {
    Column(
        modifier = Modifier
            .padding(vertical = 4.dp)
    ) {
        Text(text = label, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
        Text(text = value, style = MaterialTheme.typography.bodyMedium.copy(fontWeight = FontWeight.SemiBold))
    }
}

data class Conversation(
    val id: Long,
    val title: String,
    val lastMessage: String,
    val time: String,
    val unread: Boolean = false,
)

@Composable
fun MessagingScreen(
    conversations: List<Conversation>,
    onOpenChat: (Conversation) -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(horizontal = 16.dp, vertical = 12.dp),
    ) {
        Text(
            text = "Сообщения",
            style = MaterialTheme.typography.headlineSmall.copy(fontWeight = FontWeight.Bold),
            modifier = Modifier.padding(vertical = 12.dp),
        )
        LazyColumn(
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            items(conversations, key = { it.id }) { chat ->
                Surface(
                    onClick = { onOpenChat(chat) },
                    shape = RoundedCornerShape(14.dp),
                    tonalElevation = 1.dp,
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(12.dp),
                        horizontalArrangement = Arrangement.spacedBy(12.dp),
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        Box(
                            modifier = Modifier
                                .size(48.dp)
                                .clip(CircleShape)
                                .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.1f)),
                            contentAlignment = Alignment.Center,
                        ) {
                            Text(
                                text = chat.title.take(1).uppercase(),
                                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                                color = MaterialTheme.colorScheme.primary,
                            )
                        }
                        Column(modifier = Modifier.weight(1f)) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Text(
                                    text = chat.title,
                                    style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.SemiBold),
                                )
                                if (chat.unread) {
                                    Box(
                                        modifier = Modifier
                                            .padding(start = 8.dp)
                                            .size(8.dp)
                                            .clip(CircleShape)
                                            .background(MaterialTheme.colorScheme.primary)
                                    )
                                }
                            }
                            Text(
                                text = chat.lastMessage,
                                style = MaterialTheme.typography.bodySmall,
                                maxLines = 1,
                                color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f),
                            )
                        }
                        Text(
                            text = chat.time,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f),
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun ChatConversationScreen(
    title: String,
    onBack: () -> Unit,
) {
    val messages = remember {
        listOf(
            ChatMessage("Здравствуйте! Машина доступна?", true),
            ChatMessage("Да, можно посмотреть сегодня вечером", false),
            ChatMessage("Отлично, какой адрес?", true),
        )
    }
    var input by remember { mutableStateOf("") }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background),
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 12.dp, vertical = 8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack) {
                Icon(Icons.Outlined.ArrowBack, contentDescription = "Назад")
            }
            Text(
                text = title,
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
            )
            IconButton(onClick = { }) {
                Icon(Icons.Outlined.MoreVert, contentDescription = null)
            }
        }
        LazyColumn(
            modifier = Modifier
                .weight(1f)
                .padding(horizontal = 12.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            items(messages) { message ->
                MessageBubble(message)
            }
        }
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(12.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            OutlinedTextField(
                modifier = Modifier.weight(1f),
                value = input,
                onValueChange = { input = it },
                placeholder = { Text("Сообщение") },
                shape = RoundedCornerShape(16.dp),
                trailingIcon = { Icon(Icons.Outlined.Email, contentDescription = null) },
                singleLine = true,
            )
            Button(
                onClick = { input = "" },
                shape = RoundedCornerShape(14.dp),
            ) {
                Icon(Icons.Outlined.Send, contentDescription = "Отправить")
            }
        }
    }
}

private data class ChatMessage(val text: String, val isMine: Boolean)

@Composable
private fun MessageBubble(message: ChatMessage) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = if (message.isMine) Arrangement.End else Arrangement.Start,
    ) {
        Surface(
            color = if (message.isMine) MaterialTheme.colorScheme.primary.copy(alpha = 0.15f) else MaterialTheme.colorScheme.surface,
            shape = RoundedCornerShape(16.dp),
            tonalElevation = 1.dp,
        ) {
            Text(
                text = message.text,
                modifier = Modifier.padding(horizontal = 14.dp, vertical = 10.dp),
                color = if (message.isMine) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface,
            )
        }
    }
}

@Composable
fun SellerProfileScreen(
    onBack: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack) {
                Icon(Icons.Outlined.ArrowBack, contentDescription = "Назад")
            }
            Text(text = "Профиль продавца", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold))
            Spacer(modifier = Modifier.size(24.dp))
        }
        Row(
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            modifier = Modifier.padding(vertical = 12.dp),
        ) {
            Box(
                modifier = Modifier
                    .size(64.dp)
                    .clip(CircleShape)
                    .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.15f)),
                contentAlignment = Alignment.Center,
            ) {
                Icon(Icons.Outlined.PersonOutline, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
            }
            Column {
                Text("Артём Захаров", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.SemiBold))
                Text("4.9 ★ (120 отзывов)", color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
            }
        }
        Text("Объявления продавца", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold))
        LazyColumn(
            modifier = Modifier.weight(1f),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            items(sampleSellerListings, key = { it.id }) { listing ->
                Surface(
                    shape = RoundedCornerShape(14.dp),
                    tonalElevation = 1.dp,
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(12.dp),
                        horizontalArrangement = Arrangement.spacedBy(12.dp),
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        AsyncImage(
                            model = listing.previewPhoto,
                            contentDescription = listing.title,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier
                                .size(90.dp)
                                .clip(RoundedCornerShape(12.dp)),
                        )
                        Column {
                            Text(listing.title, style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.SemiBold))
                            Text(
                                listing.vehicleSummary ?: "",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f),
                            )
                            Spacer(modifier = Modifier.height(6.dp))
                            Text(
                                text = listing.price?.amount?.let { String.format("%,.0f %s", it, listing.price.currency ?: "USD") } ?: "—",
                                style = MaterialTheme.typography.titleMedium.copy(color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.Bold),
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun UserProfileScreen(
    onEditProfile: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Text(text = "Мой профиль", style = MaterialTheme.typography.headlineSmall.copy(fontWeight = FontWeight.Bold))
            IconButton(onClick = onEditProfile) {
                Icon(Icons.Outlined.Edit, contentDescription = "Редактировать")
            }
        }
        Row(verticalAlignment = Alignment.CenterVertically) {
            Box(
                modifier = Modifier
                    .size(72.dp)
                    .clip(CircleShape)
                    .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.1f)),
                contentAlignment = Alignment.Center,
            ) {
                Text("AZ", style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold))
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column {
                Text("Артём Захаров", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.SemiBold))
                Text("Покупатель", color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
            }
        }
        Surface(
            shape = RoundedCornerShape(14.dp),
            tonalElevation = 1.dp,
        ) {
            Column(modifier = Modifier.padding(14.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                ProfileRow(icon = Icons.Outlined.Email, label = "Почта", value = "artem@example.com")
                Divider()
                ProfileRow(icon = Icons.Outlined.Call, label = "Телефон", value = "+374 00 000 000")
                Divider()
                ProfileRow(icon = Icons.Outlined.LocationOn, label = "Город", value = "Ереван")
            }
        }
    }
}

@Composable
fun EditProfileScreen(
    onBack: () -> Unit,
) {
    var name by remember { mutableStateOf("Артём Захаров") }
    var phone by remember { mutableStateOf("+374 00 000 000") }
    var city by remember { mutableStateOf("Ереван") }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack) { Icon(Icons.Outlined.ArrowBack, contentDescription = "Назад") }
            Text(text = "Редактировать профиль", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold))
            Spacer(modifier = Modifier.size(24.dp))
        }
        OutlinedTextField(
            value = name,
            onValueChange = { name = it },
            modifier = Modifier.fillMaxWidth(),
            label = { Text("Имя") },
            shape = RoundedCornerShape(12.dp),
        )
        OutlinedTextField(
            value = phone,
            onValueChange = { phone = it },
            modifier = Modifier.fillMaxWidth(),
            label = { Text("Телефон") },
            shape = RoundedCornerShape(12.dp),
        )
        OutlinedTextField(
            value = city,
            onValueChange = { city = it },
            modifier = Modifier.fillMaxWidth(),
            label = { Text("Город") },
            shape = RoundedCornerShape(12.dp),
        )
        Spacer(modifier = Modifier.height(12.dp))
        Button(
            onClick = onBack,
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp),
        ) {
            Text("Сохранить")
        }
    }
}

@Composable
fun AddListingScreen(
    onBack: () -> Unit,
) {
    var title by remember { mutableStateOf("") }
    var price by remember { mutableStateOf("") }
    var description by remember { mutableStateOf("") }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack) { Icon(Icons.Outlined.ArrowBack, contentDescription = "Назад") }
            Text(text = "Новое объявление", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold))
            Spacer(modifier = Modifier.size(24.dp))
        }
        OutlinedTextField(
            value = title,
            onValueChange = { title = it },
            modifier = Modifier.fillMaxWidth(),
            label = { Text("Модель") },
            shape = RoundedCornerShape(12.dp),
        )
        OutlinedTextField(
            value = price,
            onValueChange = { price = it },
            modifier = Modifier.fillMaxWidth(),
            label = { Text("Цена, USD") },
            shape = RoundedCornerShape(12.dp),
        )
        OutlinedTextField(
            value = description,
            onValueChange = { description = it },
            modifier = Modifier
                .fillMaxWidth()
                .height(120.dp),
            label = { Text("Описание") },
            shape = RoundedCornerShape(12.dp),
        )
        Spacer(modifier = Modifier.height(12.dp))
        Button(
            onClick = onBack,
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp),
        ) {
            Text("Опубликовать")
        }
    }
}

@Composable
private fun ProfileRow(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    label: String,
    value: String,
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
            Spacer(modifier = Modifier.width(8.dp))
            Text(label, style = MaterialTheme.typography.bodyMedium)
        }
        Text(value, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.7f))
    }
}

private val sampleSellerListings = listOf(
    Listing(
        id = 200,
        title = "BMW M4 Competition",
        listingType = null,
        status = null,
        location = "Ереван",
        price = com.flippam.mobile.data.repository.Money(72000.0, "USD"),
        outTheDoorPrice = null,
        priceBadge = null,
        priceAnomaly = false,
        buyNowPrice = null,
        vehicleSummary = "2021 • 10 000 км",
        mileage = 10000,
        previewPhoto = "https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=800&q=60",
        isFavorite = false,
        favoritesCount = 0,
        sellerScore = null,
    ),
    Listing(
        id = 201,
        title = "Mercedes-Benz G63",
        listingType = null,
        status = null,
        location = "Ереван",
        price = com.flippam.mobile.data.repository.Money(180000.0, "USD"),
        outTheDoorPrice = null,
        priceBadge = null,
        priceAnomaly = false,
        buyNowPrice = null,
        vehicleSummary = "2022 • 6 000 км",
        mileage = 6000,
        previewPhoto = "https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=800&q=60",
        isFavorite = false,
        favoritesCount = 0,
        sellerScore = null,
    ),
)
