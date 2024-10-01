<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\Branch;
use LaravelCommon\ViewModels\AbstractViewModel;

class BranchViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var Branch
     */
    protected $model;

    /**
     *
     * @inheritdoc
     */
    public function link()
    {
        return '#unimplemented';
    }

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
            'address' => $this->model->getAddress(),
            'phone' => $this->model->getPhone(),
            'fax' => $this->model->getFax(),
            'email' => $this->model->getEmail(),
            'regency' => $this->model->getRegency()
        ];
    }
}
