<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Support\Collection;
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

    public static function loadWith(array $embeds = [])
    {
        $with = [];

        if (in_array('application_feature', $embeds, true)) {
            $with['applicationFeatures'] = ApplicationFeatureViewModel::loadWith($embeds);
        }

        return $with;
    }

    public function addResource()
    {
        if (
            $this->request &&
            $this->request->get('embed') &&
            in_array('application_feature', $this->request->get('embed'))
        ) {
            $applicationFeatures = $this->model->getApplicationFeatures();
            if ($applicationFeatures->count() > 0) {
                $applicationFeatureViewModels = new Collection();
                foreach ($applicationFeatures as $applicationFeature) {
                    $applicationFeatureViewModels->add(new ApplicationFeatureViewModel($applicationFeature, $this->request));
                }

                $this->embedResource('application_features', $applicationFeatureViewModels);
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
            'name' => $this->model->getName(),
            'description' => $this->model->getDescription()
        ];
    }
}
