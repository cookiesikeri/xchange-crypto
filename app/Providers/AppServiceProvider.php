<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {

            $view->with('site', \App\Models\SiteSetting::first());
            $view->with('general', \App\Models\GeneralDetail::first());
            $view->with('unreadm', \App\Models\ContactMessage::where('is_treated', 0)->where('status', 0)->orderBy('id', 'desc')->get());
            $view->with('unreadcount', \App\Models\ContactMessage::where('is_treated', 0)->where('status', 0)->count());
            $view->with('readmsgcount', \App\Models\ContactMessage::where('is_treated', 1)->where('status', 1)->count());




        });

                //     if(App::environment() == "production")
        // {
        //     $url = \Request::url();
        //     $check = strstr($url,"http://");
        //     if($check)
        //     {
        //        $newUrl = str_replace("http","https",$url);
        //        header("Location:".$newUrl);

        //     }
        // }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Interfaces\VendorInterface',
            'App\Repositories\VendorRepository'
        );

        $this->app->bind(
            'App\Interfaces\UserInterface',
            'App\Repositories\UserRepository'
        );
    }
}
