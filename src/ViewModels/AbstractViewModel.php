<?php

namespace LaravelCommon\ViewModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelCommon\Responses\BaseResponse;

abstract class AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    protected $model;

    /**
     * @var ?Request
     */
    protected $request;

    /**
     *
     * @var array
     */
    protected $resource = [];

    /**
     * @param Model $model
     */
    public function __construct(Model $model, ?Request $request = null)
    {
        $this->model = $model;
        $this->request = $request;
    }

    public function link()
    {
        return '#unimplemented';
    }

    /**
     * Convert instance to array add auto add resource available
     */
    public function finalArray()
    {
        $timeZone = config('common-config')['time_zone'];
        $settingTimeZone = $timeZone['time_zone'];


        $this->resource = $this->toArray();

        $this->resource['created_at'] =  !is_null($this->model->created_at)
            ? (
                $timeZone['use_custom_timezone'] ?
                $this->model->created_at->setTimeZOne($settingTimeZone)->format('Y-m-d H:i:s') :
                $this->model->created_at->format('Y-m-d H:i:s')
            )
            : null;

        $this->resource['updated_at'] = !is_null($this->model->updated_at)
            ? (
                $timeZone['use_custom_timezone'] ?
                $this->model->updated_at->setTimeZOne($settingTimeZone)->format('Y-m-d H:i:s') :
                $this->model->updated_at->format('Y-m-d H:i:s')
            )
            : null;

        $this->resource['_link']['self'] =  config('app.url') . $this->link();

        if ($this->getIsAutoAddResource()) {
            $this->addResource();
        }
        return $this->resource;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param AbstractViewModel|AbstractCollection $value
     * @return void
     */
    public function embedResource(
        string $key,
        AbstractViewModel|Collection $value
    ) {

        if ($this->request != null && $this->request->getPathInfo() == '/graphql') {
            if ($value instanceof AbstractViewModel) {
                $this->resource[$key] = $value->finalArray();
            }

            if ($value instanceof Collection) {
                $viewModelArray = [];
                foreach ($value as $viewmodel) {
                    /**
                     * @var AbstractViewModel $viewmodel
                     */
                    $viewModelArray[] = $viewmodel->finalArray();
                }
                $this->resource[$key] = $viewModelArray;
            }
        } else {
            $useResourceKey = config('common-config')['api']['use_resource_key'];
            if ($value instanceof AbstractViewModel) {
                if ($useResourceKey) {
                    $this->resource[BaseResponse::RESOURCES_KEY][$key] = $value->finalArray();
                } else {
                    $this->resource[$key] = $value->finalArray();
                }
            }

            if ($value instanceof Collection) {
                $viewModelArray = [];
                foreach ($value as $viewmodel) {
                    /**
                     * @var AbstractViewModel $viewmodel
                     */
                    $viewModelArray[] = $viewmodel->finalArray();
                }

                if ($useResourceKey) {
                    $this->resource[BaseResponse::RESOURCES_KEY][$key] = $viewModelArray;
                } else {
                    $this->resource[$key] = $viewModelArray;
                }
            }
        }
    }

    /**
     * Convert instance to array
     */
    abstract public function toArray();

    /**
     * Add resource to view model
     */
    abstract public function addResource();

    /**
     *  set auto add Resource
     */
    public function setIsAutoAddResource(bool $isAutoAddResource): self
    {
        $this->isAutoAddResource = $isAutoAddResource;
        return $this;
    }

    /**
     * if set true then view model will auto add available resource you define
     * @return bool
     */
    public function getIsAutoAddResource(): bool
    {
        return $this->isAutoAddResource;
    }

    /**
     * Get model instance
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->model;
    }
}
