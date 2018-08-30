<?php

namespace Newestapps\Eee\Providers;

use App\Http\Middleware\NW3E;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Newestapps\Eee\Commands\CreateIndexCommand;
use Newestapps\Eee\Commands\SSLCredentialGeneratorCommand;
use Newestapps\Eee\Http\Middleware\Nw3eMiddleware;
use Newestapps\Eee\Services\Nw3EService;

class NwEEEServiceProvider extends ServiceProvider {

    public function boot() {
        parent::boot();

        $this->commands([
            CreateIndexCommand::class,
            SSLCredentialGeneratorCommand::class,
        ]);
    }

    public function register() {
        define('NW3E_VERSION', 'nw3e-1.0.0');

        $this->app->singleton('nw-3e', function () {
            return new Nw3EService(4096, OPENSSL_KEYTYPE_RSA);
        });

        $this->middlewareGroup('nw3e', [
            'nw3e' => Nw3eMiddleware::class,
        ]);

        $this->publishes([
            __DIR__ . '/../Database/migrations/' => database_path('migrations'),
        ], 'nw-3e');
    }

}