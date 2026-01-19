<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\LoggingConfig;
use LaravelCommon\ViewModels\AbstractViewModel;
use stdClass;

class LoggingConfigViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var LoggingConfig $model
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
            "is_enabled" => (bool)$this->model->getIsEnabled()
        ];
    }
}
