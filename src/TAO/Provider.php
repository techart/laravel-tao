<?php

namespace TAO;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;


class Provider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/tao.php' => config_path('tao.php'),
        ]);
        $this->loadViewsFrom(__DIR__.'/../../views', 'tao');

        foreach (array_keys(app()->tao->routers()) as $name) {
            $router = app()->tao->router($name);
            if (method_exists($router, 'boot')) {
                $router->boot();
            }
        }

        //app()->bind('\\Illuminate\\View\\FileViewFinder', 'TAO');

        Blade::directive('layout', function() {
            $layout = app()->tao->layout;
            return $layout;
        });

        //$this->app->tao->route();
    }
    
    public function register()
    {
        $this->app->bind('view.finder', function ($app) {
            return new \TAO\View\Finder($app['files'], $app['config']['view.paths']);
        });

        $this->app->singleton('session', function ($app) {
            return new \TAO\Session\Manager($app);
        });
        
        $this->app->singleton('tao', function() {
            $tao = app()->make('\\TAO\\TAO');
            $tao->app = $this->app;
            return $tao;
        });

        $this->app->singleton('taoFields', function() {
            $fields = app()->make(\TAO\Fields::class);
            $fields->init();
            return $fields;
        });

        $this->app->singleton('taoAdmin', function() {
            $admin = app()->make(\TAO\Admin::class);
            $admin->init();
            return $admin;
        });

        $this->app->singleton('taoAssets', function() {
            $assets = app()->make(\TAO\Assets::class);
            $assets->init();
            return $assets;
        });

        $this->app->singleton('taoView', function() {
            $assets = app()->make(\TAO\View::class);
            $assets->init();
            return $assets;
        });

        foreach (array_keys(app()->tao->routers()) as $name) {
            $router = app()->tao->router($name);
            if (method_exists($router, 'register')) {
                $router->register();
            }
        }

        $www = \TAO::publicPath();
        $link = "{$www}/tao";
        if (!is_link($link)) {
            $assets = str_replace('/src/TAO', '', __DIR__).'/public';
            symlink($assets, $link);
        }
        $link = "{$www}/storage";
        if (!is_link($link)) {
            symlink(storage_path('app/public'), $link);
        }
    }


}