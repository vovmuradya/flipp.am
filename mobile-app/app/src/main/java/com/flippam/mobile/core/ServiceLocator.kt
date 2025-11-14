package com.flippam.mobile.core

import android.app.Application
import com.flippam.mobile.BuildConfig
import com.flippam.mobile.data.remote.MobileApi
import com.flippam.mobile.data.repository.ListingsRepository
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import java.util.concurrent.TimeUnit

object ServiceLocator {
    private lateinit var application: Application

    private val moshi: Moshi by lazy {
        Moshi.Builder()
            .addLast(KotlinJsonAdapterFactory())
            .build()
    }

    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = if (BuildConfig.DEBUG) {
            HttpLoggingInterceptor.Level.BODY
        } else {
            HttpLoggingInterceptor.Level.NONE
        }
    }

    private val defaultHeadersInterceptor = Interceptor { chain ->
        val request = chain.request()
            .newBuilder()
            .header("Accept", "application/json")
            .build()
        chain.proceed(request)
    }

    private val okHttpClient: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .addInterceptor(defaultHeadersInterceptor)
            .addInterceptor(loggingInterceptor)
            .build()
    }

    private val retrofit: Retrofit by lazy {
        Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(MoshiConverterFactory.create(moshi))
            .build()
    }

    val mobileApi: MobileApi by lazy {
        retrofit.create(MobileApi::class.java)
    }

    val listingsRepository: ListingsRepository by lazy {
        ListingsRepository(mobileApi)
    }

    fun init(app: Application) {
        application = app
    }
}
