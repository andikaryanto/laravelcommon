<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\PricingApplicationFeature;
use LaravelCommon\ViewModels\AbstractViewModel;

class PricingApplicationFeatureViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var PricingApplicationFeature
     */
    protected $model;

    public static function loadWith(array $embeds = [])
    {
        $with = [];
        $with['pricing'] = PricingViewModel::loadWith($embeds);
        $with['applicationFeature'] = ApplicationFeatureViewModel::loadWith($embeds);

        return $with;
    }

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        if (
            $this->request &&
            $this->request->get('embed') &&
            in_array('pricing', $this->request->get('embed'))
        ) {
            $pricing = $this->model->getPricing();
            if (!empty($pricing)) {
                $this->embedResource('pricing', new PricingViewModel($pricing, $this->request));
            }
        }

        if (
            $this->request &&
            $this->request->get('embed') &&
            in_array('application_feature', $this->request->get('embed'))
        ) {
            $applicationFeature = $this->model->getApplicationFeature();
            if (!empty($applicationFeature)) {
                $this->embedResource(
                    'application_feature',
                    new ApplicationFeatureViewModel($applicationFeature, $this->request)
                );
            }
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
            'pricing_id' => $this->model->pricing_id,
            'application_feature_id' => $this->model->application_feature_id,
            'is_active' => $this->model->getIsActive()
        ];
    }
}
