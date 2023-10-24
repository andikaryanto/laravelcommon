<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaravelCommon\App\Repositories\Repository;
use LaravelCommon\Exceptions\ResponsableException;
use LaravelCommon\Responses\NotFoundResponse;
use Illuminate\Http\Request;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\App\Exceptions\ModelException;
use LaravelCommon\Responses\BadRequestResponse;

class Hydrator
{
    protected array $hydrateKeys = [];

    protected Model $model;

    protected $key;

    protected string $method;

    protected Repository $repository;

    protected Request $request;

    /**
     * Hydrator constructor
     *
     * @param string $key
     * @param Repository $repository
     */
    public function __construct(
        string $key,
        Repository $repository
    ) {
        $this->key = $key;
        $this->repository = $repository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $method)
    {

        $this->request = $request;
        $this->method = $method;
        try {
            if (strtoupper($method) == 'POST') {
                $this->post($request);
            }

            if (strtoupper($method)  == 'GET') {
                $this->get($request);
            }

            if (strtoupper($method)  == 'PUT') {
                $this->put($request);
            }

            if (strtoupper($method) == 'PATCH') {
                $this->patch($request);
            }

            if (strtoupper($method) == 'DELETE') {
                $this->delete($request);
            }
        } catch (Exception $e) {
            return new BadRequestResponse($e->getMessage(), ResponseConst::INVALID_DATA);
        }

        return $next($request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function get(Request $request)
    {
        $this->model = $this->getModel($request);
        $request->setResource($this->model);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    private function post(Request $request)
    {
        $this->model = $this->repository->newModel();
        $this->beforeHydrate();
        $request->setResource($this->model);
        $this->hydrate();
        $this->afterHydrate();
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    private function put(Request $request)
    {
        $this->model = $this->getModel($request);
        $request->setResource($this->model);
        $this->hydrate();
    }

    /**
     * Do something after hydrate
     * @return $this
     */
    public function afterHydrate()
    {
        return $this;
    }

    /**
     * Do something before hydrate
     * @return $this
     */
    public function beforeHydrate()
    {
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    private function delete(Request $request)
    {
        $this->model = $this->getModel($request);
        $request->setResource($this->model);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    private function patch(Request $request)
    {
        $this->model = $this->getModel($request);
        $request->setResource($this->model);

        $this->hydrate();
    }

    /**
     * hydrate
     *
     * @return void
     */
    public function hydrate()
    {
    }

    /**
     * Get model instance
     *
     * @param Request $request
     * @return mixed
     */
    private function getModel(Request $request)
    {
        $id = $request->route()->parameter($this->key);
        try {
            $resource = $this->repository->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ResponsableException($e->getMessage(), new NotFoundResponse('No Data Found'));
        }

        return $resource;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param array $modelSetter
     * @param array $relatedObjectGetter
     * @return Hydrator
     */
    public function when(string $key, array $modelSetter, array $relatedObjectGetter = []): Hydrator
    {
        $input = $this->request->input();

        $keyArr = explode('.', $key);
        $model = $modelSetter[0];
        $modelSetterFunction = $modelSetter[1];
        $field = $keyArr[0];

        if (isset($input[$field]) && !empty($relatedObjectGetter)) {
            $id = $keyArr[1];
            $relatedValue = $input[$field][$id];

            $relatedRepository = $relatedObjectGetter[0];
            $relatedFunction = $relatedObjectGetter[1];
            $relatedObject = $relatedRepository->$relatedFunction($relatedValue);

            if (is_null($relatedObject)) {
                throw new ModelException($field . ' with ID ' . $relatedValue . ' not found');
            }

            $model->$modelSetterFunction($relatedObject);
        }

        if (isset($input[$field]) && empty($relatedObjectGetter)) {
            $model->$modelSetterFunction($input[$field]);
        }

        return $this;
    }
}
