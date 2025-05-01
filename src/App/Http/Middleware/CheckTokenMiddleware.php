<?php

namespace LaravelCommon\App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Support\Facades\Cookie;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Queries\User\TokenQuery;
use LaravelCommon\App\Services\Jwt;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Responses\UnauthorizedResponse;
use LaravelCommon\System\Http\Request;

class CheckTokenMiddleware
{
    public const NAME = 'common.app.middlware.check-token-middleware';

    /**
     *
     * @var TokenQuery
     */
    protected TokenQuery $tokenQuery;

    /**
     *
     * @var Jwt
     */
    protected Jwt $jwt;

    /**
     * Undocumented function
     *
     * @param TokenQuery $tokenRepository
     * @param Jwt $jwt
     */
    public function __construct(
        TokenQuery $tokenQuery,
        Jwt $jwt
    ) {
        $this->tokenQuery = $tokenQuery;
        $this->jwt = $jwt;
    }

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
            $authorization = Cookie::get('access_token');

            if (is_null($authorization)) {
                if (!$request->hasHeader('Authorization')) {
                    return new UnauthorizedResponse('No Authorization header found', ResponseConst::NOT_AUTHORIZED);
                }

                $bearerAuthorization = $request->header('Authorization');
                if (empty($bearerAuthorization)) {
                    return new BadRequestResponse('Token is empty', ResponseConst::INVALID_CREDENTIAL);
                }

                $authorizationArr = explode(' ', $bearerAuthorization);

                if (count($authorizationArr) == 1) {
                    return new BadRequestResponse('Token is invalid', ResponseConst::INVALID_CREDENTIAL);
                }

                $authorization = $authorizationArr[1];
            }

            /**
             * @var Token $userToken
             */
            $userToken = $this->tokenQuery->whereToken($authorization)->getIterator()->first();
            if (empty($userToken)) {
                return new BadRequestResponse('No Token Match', ResponseConst::INVALID_CREDENTIAL);
            }

            if ($userToken->getExpiredAt() < Carbon::now()) {
                return new BadRequestResponse('Token Expired', ResponseConst::SESSION_EXPIRED);
            }

            $request->setUserToken($userToken);
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage());
        }
        return $next($request);
    }
}
