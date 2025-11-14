package com.flippam.mobile

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.viewModels
import androidx.compose.runtime.getValue
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.flippam.mobile.core.ServiceLocator
import com.flippam.mobile.ui.listings.ListingsScreen
import com.flippam.mobile.ui.listings.ListingsViewModel
import com.flippam.mobile.ui.listings.ListingsViewModelFactory
import com.flippam.mobile.ui.theme.IdromTheme

class MainActivity : ComponentActivity() {
    private val listingsViewModel: ListingsViewModel by viewModels {
        ListingsViewModelFactory(ServiceLocator.listingsRepository)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        setContent {
            IdromTheme {
                val uiState by listingsViewModel.state.collectAsStateWithLifecycle()
                ListingsScreen(
                    state = uiState,
                    onRetry = { listingsViewModel.refreshListings() }
                )
            }
        }
    }
}
