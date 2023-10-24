<?php

namespace LaravelCommon\ViewModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $this->resource['id'] = $this->model->getId();

        $this->resource = array_merge($this->resource, $this->toArray());

        $this->resource['created_at'] =  !is_null($this->model->created_at)
            ? $this->model->created_at->format('Y-m-d H:i:s')
            : null;

        $this->resource['updated_at'] = !is_null($this->model->updated_at)
            ? $this->model->updated_at->format('Y-m-d H:i:s')
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
        AbstractViewModel|AbstractCollection $value
    ) {

        if ($this->request != null && $this->request->getPathInfo() == '/graphql') {
            if ($value instanceof AbstractViewModel) {
                $this->resource[$key] = $value->finalArray();
            }

            if ($value instanceof AbstractCollection) {
                $this->resource[$key] = $value->finalProcceed();
            }
        } else {
            if ($value instanceof AbstractViewModel) {
                $this->resource[BaseResponse::RESOURCES_KEY][$key] = $value->finalArray();
            }

            if ($value instanceof AbstractCollection) {
                $this->resource[BaseResponse::RESOURCES_KEY][$key] = $value->finalProcceed();
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
