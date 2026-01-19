<?php

namespace LaravelCommon\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelCommon\App\Services\UserService;
use LaravelCommon\App\ViewModels\User\TokenViewModel;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Responses\SuccessResponse;

class AuthController extends Controller
{
    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     *
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function generateToken(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $userToken = $this->userService->generateToken($email, $password);
        if (empty($userToken)) {
            return new BadRequestResponse('User not found', [], null);
        }
        return (new SuccessResponse('OK', [], new TokenViewModel($userToken)));
    }
}
