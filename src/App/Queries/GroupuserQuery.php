<?php

namespace LaravelCommon\App\Queries;

use Carbon\Carbon;
use DateTime;
use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\Models\Groupuser;
use LaravelCommon\App\Models\Scope;

class GroupuserQuery extends Query
{
    public function identityClass(): string
    {
        return Groupuser::class;
    }

    public function whereUserCreatedBefore(Carbon $date): GroupuserQuery
    {
        $this->joinWith('users', 'groupusers.id', '=', 'users.groupuser_id')
            ->where('users.created_at', '<', $date->format('Y-m-d H:i:s'));
        return $this;
    }

    public function whereUserCreatedAfter(Carbon $date): GroupuserQuery
    {
        $this->joinWith('users', 'groupusers.id', '=', 'users.groupuser_id')
            ->where('users.created_at', '>', $date->format('Y-m-d H:i:s'));
        return $this;
    }

    public function whereUserScope(Scope $scope): GroupuserQuery
    {
        $this->joinWith('users', 'groupusers.id', '=', 'users.groupuser_id')
            ->joinWith('user_scopes', 'users.id', '=', 'user_scopes.user_id')
            ->joinWith('scopes', 'user_scopes.scope_id', '=', 'scopes.id')
            ->where('scopes.id', '=', $scope->getId());

        return $this;
    }

    public function whereGroupName(string $groupName)
    {
        $this->where('group_name', '=', $groupName);
        return $this;
    }
}
