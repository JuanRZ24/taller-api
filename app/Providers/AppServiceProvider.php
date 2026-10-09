<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
            })
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {
                $protegida = collect($routeInfo->route->gatherMiddleware())
                    ->contains(fn ($m) => is_string($m) && Str::startsWith($m, 'auth:'));

                if (! $protegida) {
                    $operation->security = [];
                }
            });
    }
}