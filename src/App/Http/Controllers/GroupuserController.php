<?php

namespace LaravelCommon\App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\App\Queries\GroupuserQuery;
use LaravelCommon\App\ViewModels\GroupuserCollection;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\Responses\PagedJsonResponse;
use LaravelCommon\Responses\SuccessResponse;

class GroupuserController extends Controller
{
    /**
     * Undocumented variable
     *
     * @var GroupuserQuery
     */
    protected GroupuserQuery $groupuserQuery;

    /**
     * Undocumented function
     *
     * @param GroupuserQuery $userRepository
     */
    public function __construct(
        GroupuserQuery $groupuserQuery
    ) {
        $this->groupuserQuery = $groupuserQuery;
    }

    public function store(Request $request)
    {
        $user = $request->getResource();

        return new SuccessResponse('OK', [], new UserViewModel($user, $request));
    }

    public function getAll(Request $request)
    {
        $users = $this->groupuserQuery
            ->whereUserCreatedAfter(Carbon::create('2023-06-12 18:56:55'))
            ->whereScope((new Scope())->setId(2));

        return new PagedJsonResponse('OK', [], new GroupuserCollection($users, $request));
    }
}
