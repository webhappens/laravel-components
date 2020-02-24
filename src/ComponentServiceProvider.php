<?php

namespace WebHappens\Components;

use Illuminate\Support\ServiceProvider;

class ComponentServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        view()->addNamespace('components', app_path('Components'));
    }
}
