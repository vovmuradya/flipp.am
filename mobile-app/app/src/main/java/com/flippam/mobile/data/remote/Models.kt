package com.flippam.mobile.data.remote

import com.squareup.moshi.Json

data class ListingsResponse(
    val data: List<ListingDto> = emptyList(),
    val meta: PaginationMeta? = null,
    val links: PaginationLinks? = null,
    val status: String? = null,
)

data class ListingDto(
    val id: Long,
    val title: String,
    val slug: String?,
    val status: String?,
    @Json(name = "listing_type") val listingType: String?,
    val price: PriceDto?,
    @Json(name = "is_buy_now_available") val isBuyNowAvailable: Boolean?,
    @Json(name = "buy_now_price") val buyNowPrice: Double?,
    @Json(name = "buy_now_currency") val buyNowCurrency: String?,
    @Json(name = "operational_status") val operationalStatus: String?,
    val region: RegionDto?,
    val seller: SellerDto?,
    val vehicle: VehicleDto?,
    val photos: PhotoGroupDto?,
    @Json(name = "is_favorite") val isFavorite: Boolean?,
    @Json(name = "favorites_count") val favoritesCount: Int?,
    @Json(name = "created_at") val createdAt: String?,
    @Json(name = "updated_at") val updatedAt: String?,
)

data class PriceDto(
    val amount: Double?,
    val currency: String?,
)

data class RegionDto(
    val id: Long?,
    val name: String?,
)

data class SellerDto(
    val id: Long?,
    val name: String?,
    val phone: String?,
    val role: String?,
)

data class VehicleDto(
    val make: String?,
    val model: String?,
    val year: Int?,
    val mileage: Int?,
    @Json(name = "body_type") val bodyType: String?,
    val transmission: String?,
    @Json(name = "fuel_type") val fuelType: String?,
    @Json(name = "engine_displacement_cc") val engineDisplacementCc: Int?,
    @Json(name = "exterior_color") val exteriorColor: String?,
    @Json(name = "is_from_auction") val isFromAuction: Boolean?,
    @Json(name = "auction_ends_at") val auctionEndsAt: String?,
    @Json(name = "source_auction_url") val sourceAuctionUrl: String?,
)

data class PhotoGroupDto(
    val primary: String?,
    val all: List<String> = emptyList(),
)

data class PaginationMeta(
    val current_page: Int?,
    val last_page: Int?,
    val per_page: Int?,
    val total: Int?,
)

data class PaginationLinks(
    val first: String?,
    val last: String?,
    val next: String?,
    val prev: String?,
)
