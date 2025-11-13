package com.flippam.mobile.data.repository

import com.flippam.mobile.data.remote.ListingDto
import com.flippam.mobile.data.remote.VehicleDto

data class Money(
    val amount: Double?,
    val currency: String?,
)

data class Listing(
    val id: Long,
    val title: String,
    val listingType: String?,
    val status: String?,
    val location: String?,
    val price: Money?,
    val buyNowPrice: Money?,
    val vehicleSummary: String?,
    val mileage: Int?,
    val previewPhoto: String?,
    val isFavorite: Boolean,
    val favoritesCount: Int,
)

data class ListingQuery(
    val query: String? = null,
    val listingType: String? = null,
    val categoryId: Long? = null,
    val regionId: Long? = null,
    val priceFrom: Double? = null,
    val priceTo: Double? = null,
    val onlyBuyNow: Boolean? = null,
    val isCopart: Boolean? = null,
    val page: Int = 1,
    val perPage: Int = 20,
    val sort: String? = null,
)

internal fun ListingDto.toDomain(): Listing {
    val locationLabel = region?.name
    val preview = when {
        photos?.primary != null -> photos.primary
        photos?.all?.isNotEmpty() == true -> photos.all.first()
        else -> null
    }

    return Listing(
        id = id,
        title = title,
        listingType = listingType,
        status = status,
        location = locationLabel,
        price = price?.let { Money(it.amount, it.currency) },
        buyNowPrice = if (buyNowPrice != null || buyNowCurrency != null) {
            Money(buyNowPrice, buyNowCurrency)
        } else null,
        vehicleSummary = vehicle?.toSummary(),
        mileage = vehicle?.mileage,
        previewPhoto = preview,
        isFavorite = isFavorite ?: false,
        favoritesCount = favoritesCount ?: 0,
    )
}

private fun VehicleDto.toSummary(): String? {
    val parts = listOfNotNull(year?.toString(), make, model)
    if (parts.isEmpty()) return null
    return parts.joinToString(" • ")
}
