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

    public static function loadWith()
    {
        return [
            'pricing' => PricingViewModel::loadWith()
        ];
    }

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
        $pricing = $this->model->getPricing();
        if (!empty($pricing)) {
            $this->embedResource('pricing', new PricingViewModel($pricing, $this->request));
        }

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
            'regency' => $this->model->getRegency(),
            'logo_url' => str_replace('public/', '', $this->model->getLogoUrl()),
            'confirmation_phone' => $this->model->getConfirmationPhone(),
        ];
    }
}
