<?php

namespace LaravelCommon\App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Route;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Responses\NotAllowedResponse;
use LaravelCommon\Responses\SuccessResponse;

class RouteCheckerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        // $routeMethods = Route::getCurrentRoute()->getName();
        // return new SuccessResponse('', [], $request->route()->methods);

        // if(!in_array(strtoupper($request->method()), $routeMethods)){
        //     return new SuccessResponse('', [], $routeMethods);
        //     return new NotAllowedResponse('Method not allowed', ResponseConst::NOT_ALLOWED_METHOD);
        // }

        return $next($request);
    }
}
