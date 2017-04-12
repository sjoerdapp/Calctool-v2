<?php

namespace CalculatieTool\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

use Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * A namespace is applied to a grou of controller routes in the routes file.
     *
     * @var string
     */ 
    protected $namespace            = 'CalculatieTool\Http\Controllers';
    protected $namespaceApi         = 'CalculatieTool\Http\Controllers\Api';
    protected $namespaceAdmin       = 'CalculatieTool\Http\Controllers\Admin';

    /**
     * A namespace is applied to a grou of controller routes in the routes file.
     *
     * @var string
     */ 
    protected $namespaceAccount     = 'Account';
    protected $namespaceCalculation = 'Calculation';
    protected $namespaceInvoice     = 'Invoice';
    protected $namespaceProducts    = 'Products';
    protected $namespaceProposal    = 'Poposal';
    protected $namespaceRelation    = 'Relation';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('project_id', '[0-9]{5,}');
        Route::pattern('relation_id', '[0-9]{5,}');
        Route::pattern('contact_id', '[0-9]{3,}');
        Route::pattern('resource_id', '[0-9]+');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
        $this->mapServiceRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        /* Default application routes */
        Route::group([
            'namespace' => $this->namespace
        ], function ($router) {
            require base_path('routes/web.php');
        });

        /* Admin application routes */
        Route::group([
            'namespace' => $this->namespace,
            'before' => 'admin',
            'prefix' => 'admin',
            'middleware' => 'admin'
        ], function ($router) {
            require base_path('routes/admin.php');
        });
    }

    /**
     * Define the "api" routes for the application.
      *
     * @return void
     */
    protected function mapApiRoutes()
    {
        /* Admin application routes */
        Route::group([
            'namespace' => $this->namespaceApi,
            'prefix' => 'api/v1',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "api" routes for the application.
      *
     * @return void
     */
    protected function mapServiceRoutes()
    {
        /* Admin application routes */
        Route::group([
            'namespace' => $this->namespaceApi,
            'prefix' => 'api/v1',
        ], function ($router) {
            require base_path('routes/service.php');
        });
    }

}
