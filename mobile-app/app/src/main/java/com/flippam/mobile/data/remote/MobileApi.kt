package com.flippam.mobile.data.remote

import retrofit2.http.GET
import retrofit2.http.Query

interface MobileApi {
    @GET("api/mobile/listings")
    suspend fun getListings(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("sort") sort: String? = null,
        @Query("category_id") categoryId: Long? = null,
        @Query("region_id") regionId: Long? = null,
        @Query("listing_type") listingType: String? = null,
        @Query("price_from") priceFrom: Double? = null,
        @Query("price_to") priceTo: Double? = null,
        @Query("q") query: String? = null,
        @Query("only_buy_now") onlyBuyNow: Boolean? = null,
        @Query("is_copart") isCopart: Boolean? = null,
    ): ListingsResponse
}
