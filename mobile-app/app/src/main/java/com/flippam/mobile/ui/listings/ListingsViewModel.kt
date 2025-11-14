package com.flippam.mobile.ui.listings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.flippam.mobile.data.repository.Listing
import com.flippam.mobile.data.repository.ListingQuery
import com.flippam.mobile.data.repository.ListingsRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class ListingsUiState(
    val isLoading: Boolean = false,
    val listings: List<Listing> = emptyList(),
    val errorMessage: String? = null,
)

class ListingsViewModel(
    private val repository: ListingsRepository,
) : ViewModel() {

    private var currentQuery: ListingQuery = ListingQuery()

    private val _state = MutableStateFlow(ListingsUiState(isLoading = true))
    val state: StateFlow<ListingsUiState> = _state

    init {
        refreshListings()
    }

    fun refreshListings(newQuery: ListingQuery = currentQuery) {
        currentQuery = newQuery
        _state.update { it.copy(isLoading = true, errorMessage = null) }

        viewModelScope.launch {
            runCatching { repository.fetchListings(newQuery) }
                .onSuccess { listings ->
                    _state.value = ListingsUiState(isLoading = false, listings = listings)
                }
                .onFailure { error ->
                    _state.value = ListingsUiState(
                        isLoading = false,
                        listings = emptyList(),
                        errorMessage = error.localizedMessage ?: "Не удалось загрузить объявления",
                    )
                }
        }
    }
}

class ListingsViewModelFactory(
    private val repository: ListingsRepository,
) : ViewModelProvider.Factory {
    @Suppress("UNCHECKED_CAST")
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        if (modelClass.isAssignableFrom(ListingsViewModel::class.java)) {
            return ListingsViewModel(repository) as T
        }
        throw IllegalArgumentException("Unknown ViewModel class: ${modelClass.name}")
    }
}
