<?php

namespace LaravelCommon\App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Exception;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Exceptions\ResponsableException;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Exceptions\ModelException;
use LaravelCommon\Exceptions\ValidationException;

class ResourceValidationMiddleware
{
    public const NAME = 'common.app.middleware.resource-validation-middleware';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $resource = $request->getResource();
        try {
            // $resource->validate();
        } catch (ValidationException $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::INVALID_DATA));
        } catch (ModelException $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::INVALID_DATA));
        } catch (Exception $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::FAILED_SAVE_DATA));
        }

        return $next($request);
    }
}
