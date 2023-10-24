<?php

namespace LaravelCommon\App\ViewModels\Groupuser;

use LaravelCommon\App\Models\Groupuser\ScopeMapping;
use LaravelCommon\App\ViewModels\GroupuserViewModel;
use LaravelCommon\App\ViewModels\ScopeViewModel;
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
        $groupuser = $this->model->getGroupuser();
        if (!empty($groupuser)) {
            $this->embedResource('groupuser', new GroupuserViewModel($groupuser, $this->request));
        }

        $scope = $this->model->getScope();
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
