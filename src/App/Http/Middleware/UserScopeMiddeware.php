<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\App\Models\User;
use LaravelCommon\Responses\BadRequestResponse;

class UserScopeMiddeware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $method)
    {
        $user = $request->getUserToken()->getUser();
        try {
            $this->$method($user);
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage(), ResponseConst::INVALID_CREDENTIAL);
        }

        return $next($request);
    }

    /**
     *
     * @param User $user
     * @return mixed
     */
    public function isPartner(User $user)
    {
        if (!$this->isAuthorized($user, 'partner')) {
            throw new Exception('User is not partner') ;
        }
    }

    /**
     *
     * @param User $user
     * @param string $scope
     * @return boolean
     */
    private function isAuthorized(User $user, string $scope)
    {
        $isAuthorized = false;
        $userScopes = $user->getScopes();
        if (!empty($userScopes)) {
            foreach ($userScopes as $userScope) {
                if ($userScope->getName() == $scope) {
                    $isAuthorized = true;
                    break;
                }
            }
        }
        return $isAuthorized;
    }
}
