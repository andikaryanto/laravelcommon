<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\Pricing;
use LaravelCommon\ViewModels\AbstractViewModel;

class PricingViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var Pricing $model
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
            'name' => $this->model->getName(),
            'description' => $this->model->getDescription()
        ];
    }
}
