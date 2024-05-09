<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\User;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\ViewModels\PaggedCollection;

class UserCollection extends PaggedCollection
{   
    public function loadWith(): array
    {
        return UserViewModel::loadWith();
    }

    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?UserViewModel
    {
        if ($model instanceof User) {
            return new UserViewModel($model, $this->request);
        }
    }
}
