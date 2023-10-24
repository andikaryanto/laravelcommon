<?php

namespace LaravelCommon\App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Repository
{
    /**
     *
     * @var string
     */
    protected string $modelClass;

    public function __construct(
        string $modelClass
    ) {
        $this->modelClass = $modelClass;
    }


    /**
     *
     * @param mixed $id
     * @return mixed
     */
    public function find($id)
    {
        $modelClass = $this->modelClass;
        return $modelClass::find($id);
    }

    /**
     *
     * @param mixed $id
     * @throws ModelNotFoundException
     * @return mixed
     */
    public function findOrFail($id)
    {
        $modelClass = $this->modelClass;
        return $modelClass::findOrFail($id);
    }

    /**
     *
     * @param mixed $id
     * @return mixed
     */
    public function findOrNew($id)
    {
        $modelClass = $this->modelClass;
        return $modelClass::findOrNew($id);
    }

    /**
     *
     * @param mixed $id
     * @return mixed
     */
    public function all()
    {
        $modelClass = $this->modelClass;
        return $modelClass::all();
    }

    /**
     * @inheritDoc
     */
    public function collectionClass(): string
    {
        throw new Exception('"collectionClass" needs to be overrided in your repository classes');
    }

    /**
     * @inheritDoc
     */
    public function viewModelClass(): string
    {
        throw new Exception('"viewModelClass" needs to be overrided in your repository classes');
    }

    /**
     * @inheritDoc
     */
    public function validateEntity(Model $model): void
    {
    }

    /**
     *
     * @return mixed
     */
    public function newModel()
    {
        $modelClass = $this->modelClass;
        return new $modelClass();
    }
}
