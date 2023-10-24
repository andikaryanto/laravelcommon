<?php

namespace LaravelCommon\App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use DateTime;
use Exception;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Queries\User\TokenQuery;
use LaravelCommon\App\Repositories\User\TokenRepository;
use LaravelCommon\App\Services\Jwt;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Responses\UnauthorizedResponse;
use LaravelCommon\System\Http\Request;

class CheckToken
{
    public const NAME = 'common.app.middlware.check-token';

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
            if ($request->hasHeader('Authorization')) {
                $bearerAuthorization = $request->header('Authorization');
                if (empty($bearerAuthorization)) {
                    return new BadRequestResponse('Token is empty', ResponseConst::INVALID_CREDENTIAL);
                }

                $authorizationArr = explode(' ', $bearerAuthorization);

                if (count($authorizationArr) == 1) {
                    return new BadRequestResponse('Token is invalid', ResponseConst::INVALID_CREDENTIAL);
                }

                $authorization = $authorizationArr[1];

                /**
                 * @var Token $userToken
                 */
                $userToken = $this->tokenQuery->whereToken($authorization)->getIterator()->first();
                if (empty($userToken)) {
                    return new BadRequestResponse('Invalid Token', ResponseConst::INVALID_CREDENTIAL);
                }

                if ($userToken->getExpiredAt() < Carbon::now()) {
                    return new BadRequestResponse('Token Expired', ResponseConst::SESSION_EXPIRED);
                }
                $user = $userToken->getUser();

                $jwtPayload = $this->jwt->decodeUserToken($authorization);

                if ($user->getPassword() != $jwtPayload->password) {
                    return new BadRequestResponse('Invalid Token', ResponseConst::INVALID_CREDENTIAL);
                }

                $request->setUserToken($userToken);
            } else {
                return new UnauthorizedResponse('No Authorization header found', ResponseConst::NOT_AUTHORIZED);
            }
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage());
        }
        return $next($request);
    }
}
