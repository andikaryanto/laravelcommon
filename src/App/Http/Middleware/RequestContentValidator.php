<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Responses\BadRequestResponse;

class RequestContentValidator
{
     /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $method, ...$params)
    {
        try {
            $validator = Validator::make($request->all(), $this->$method(...$params));
            if ($validator->fails()) {
                return new BadRequestResponse('Failed to validate request', ResponseConst::INVALID_DATA, $validator->errors());
            }
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage());
        }

        return $next($request);
    }
}
