<?php

namespace LaravelCommon\App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\App\Queries\ScopeQuery;
use LaravelCommon\App\ViewModels\ScopeCollection;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\Responses\PagedJsonResponse;
use LaravelCommon\Responses\SuccessResponse;

class ScopeController extends Controller
{
    /**
     * Undocumented variable
     *
     * @var ScopeQuery
     */
    protected ScopeQuery $groupuserQuery;

    /**
     * Undocumented function
     *
     * @param ScopeQuery $userRepository
     */
    public function __construct(
        ScopeQuery $groupuserQuery
    ) {
        $this->groupuserQuery = $groupuserQuery;
    }

    public function getAll(Request $request)
    {
        return new PagedJsonResponse('OK', [], new ScopeCollection($this->groupuserQuery, $request));
    }
}
