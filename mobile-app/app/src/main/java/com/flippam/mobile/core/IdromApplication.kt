package com.flippam.mobile.core

import android.app.Application

class IdromApplication : Application() {
    override fun onCreate() {
        super.onCreate()
        ServiceLocator.init(this)
    }
}
