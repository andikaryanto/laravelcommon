<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\System\Http\Request;

class TokenHasUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $userToken = $request->getUserToken();

            $user = $userToken->getUser();

            if (is_null($user)) {
                return new BadRequestResponse('No user is found in token', ResponseConst::INVALID_CREDENTIAL);
            }

            if ($userToken->getCreatedAtUtc() < $user->getPasswordChangedAt()) {
                return new BadRequestResponse('Invalid Token Changed', ResponseConst::INVALID_CREDENTIAL);
            }
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage());
        }
        return $next($request);
    }
}
