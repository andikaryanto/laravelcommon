<?php

namespace LaravelCommon\App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Responses\UnauthorizedResponse;

class CheckScope
{
    public const NAME = 'common.app.middleware.check-scope';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $isAuthorized = false;
        $user = $request->getUserToken()->getUser();
        $groupuser = $user->getGroupuser();

        $userScopes = $user->getScopes();
        if (count($scopes) > 0) {
            if (!empty($userScopes)) {
                foreach ($userScopes as $userScope) {
                    if (in_array($userScope->getName(), $scopes) || $userScope->getName() == 'superadmin') {
                        $isAuthorized = true;
                        break;
                    }
                }
            }

            if (!$isAuthorized && !empty($groupuser)) {
                $groupuserScopes = $groupuser->getScopes();
                if (!empty($groupuserScopes)) {
                    foreach ($groupuserScopes as $groupScope) {
                        if (in_array($groupScope->getName(), $scopes)  || $groupScope->getName() == 'superadmin') {
                            $isAuthorized = true;
                            break;
                        }
                    }
                }
            }
        } else {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return new UnauthorizedResponse('You are not authorized to view or modify the data', ResponseConst::NOT_AUTHORIZED);
        }


        return $next($request);
    }
}
