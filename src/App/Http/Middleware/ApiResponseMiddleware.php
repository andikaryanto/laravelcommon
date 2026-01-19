<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelCommon\App\Services\RollbarLoggerService;
use LaravelCommon\Exceptions\ResponsableException;
use LaravelCommon\Responses\BaseResponse;
use LaravelCommon\Responses\JsonResponse as ResponsesJsonResponse;
use LaravelCommon\Responses\PagedJsonResponse;

class ApiResponseMiddleware
{
    public const NAME = 'common.app.middleware.api-response-middleware';

    /**
     *
     * @var RollbarLoggerService
     */
    protected RollbarLoggerService $rollbarLoggerService;

    public function __construct(
        RollbarLoggerService $rollbarLoggerService
    ) {
        $this->rollbarLoggerService = $rollbarLoggerService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response =  $next($request);
        if ($request->is('api/*')) {
            if ($response instanceof BaseResponse) {
                if (
                    $response instanceof PagedJsonResponse ||
                    $response instanceof ResponsesJsonResponse
                ) {
                    $response->buildData();
                    return $response->sendJson();
                } else {
                    return $response->sendJson();
                }
            }

            if ($response instanceof JsonResponse) {
                return $response;
            }

            if (
                $response instanceof Response ||
                $response instanceof JsonResponse
            ) {
                if ($response->exception) {
                    if ($this->rollbarLoggerService->isSetup()) {
                        $this->rollbarLoggerService->error(
                            "common_exception",
                            json_encode($response->exception->getTrace()),
                            $response->exception->getTrace()
                        );
                    }

                    if ($response->exception instanceof ResponsableException) {
                        return $response->exception->getResponse()->send();
                    } else {
                        throw $response->exception;
                    }
                }
            }

            throw new Exception('Controller does not return response');
        }
        return $response;
    }
}
