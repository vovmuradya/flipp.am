package com.flippam.mobile.data.repository

import com.flippam.mobile.data.remote.MobileApi
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class ListingsRepository(
    private val api: MobileApi,
) {
    suspend fun fetchListings(query: ListingQuery = ListingQuery()): List<Listing> = withContext(Dispatchers.IO) {
        val response = api.getListings(
            page = query.page,
            perPage = query.perPage,
            sort = query.sort,
            categoryId = query.categoryId,
            regionId = query.regionId,
            listingType = query.listingType,
            priceFrom = query.priceFrom,
            priceTo = query.priceTo,
            query = query.query,
            onlyBuyNow = query.onlyBuyNow,
            isCopart = query.isCopart,
        )
        response.data.map { it.toDomain() }
    }
}
