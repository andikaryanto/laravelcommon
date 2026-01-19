<?php

namespace LaravelCommon\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelCommon\App\Queries\UserQuery;
use LaravelCommon\App\ViewModels\UserCollection;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\Responses\PagedJsonResponse;
use LaravelCommon\Responses\SuccessResponse;

class UserController extends Controller
{
    /**
     * Undocumented variable
     *
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * Undocumented function
     *
     * @param UserQuery $userRepository
     */
    public function __construct(
        UserQuery $userQuery
    ) {
        $this->userQuery = $userQuery;
    }

    public function store(Request $request)
    {
        $user = $request->getResource();

        return new SuccessResponse('OK', [], new UserViewModel($user, $request));
    }

    public function getAll(Request $request)
    {
        $users = $this->userQuery;

        return new PagedJsonResponse('OK', [], new UserCollection($users, $request));
    }
}
