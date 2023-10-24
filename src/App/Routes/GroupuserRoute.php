<?php

namespace LaravelCommon\App\Routes;

use Illuminate\Support\Facades\Route;
use LaravelCommon\App\Http\Controllers\GroupuserController;

class GroupuserRoute extends CommonRoute
{
    public static function register()
    {
        Route::prefix('api')->group(function () {
            Route::get('/groupusers', [GroupuserController::class, 'getAll']);
        });
    }
}
