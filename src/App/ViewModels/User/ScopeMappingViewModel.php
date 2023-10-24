<?php

namespace LaravelCommon\App\ViewModels\User;

use LaravelCommon\App\Models\User;
use LaravelCommon\App\Models\User\ScopeMapping;
use LaravelCommon\App\ViewModels\ScopeViewModel;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\ViewModels\AbstractViewModel;
use stdClass;

class ScopeMappingViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var ScopeMapping $model
     */
    protected $model;

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        $user = $this->model->user;
        if (!empty($user)) {
            $this->embedResource('user', new UserViewModel($user, $this->request));
        }

        $scope = $this->model->scope;
        if (!empty($scope)) {
            $this->embedResource('scope', new ScopeViewModel($scope, $this->request));
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [

        ];
    }
}
