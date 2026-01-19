<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\Scope;
use LaravelCommon\ViewModels\AbstractViewModel;

class ScopeViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var Scope $model
     */
    protected $model;

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'id' => $this->model->getId(),
            'name' => $this->model->getName()
        ];
    }
}
