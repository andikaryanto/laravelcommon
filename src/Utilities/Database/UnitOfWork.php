<?php

namespace LaravelCommon\Utilities\Database;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToManyRelation;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;
use LaravelCommon\App\Database\Eloquent\Relations\HasManyRelation;
use LaravelCommon\App\Models\AuthenticableBaseModel;
use LaravelCommon\App\Models\BaseModel;
use LaravelCommon\App\Services\IncomingRequestService;
use LaravelCommon\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionProperty;

/**
 * singleton instance that is created using provider see: CommonAppServiceProvider
 * we need to this singleton to share between classes while this class is injected
 * it is responsible to do data transaction in entire application.
 */
class UnitOfWork
{
    private bool $isTransactionStarted = false;

    protected IncomingRequestService $incomingRequestService;

    public function __construct(
        IncomingRequestService $incomingRequestService
    ) {
        $this->incomingRequestService = $incomingRequestService;
    }

    /**
     * Prepare entity that will be validated and persisted.
     * Will persisted after entity unit flush
     *
     * @see entity Model->validate()
     *
     * @param BaseModel|AuthenticableBaseModel $model
     * @param bool $needValidate - validate entity that will be persisted
     * @throws ValidationException
     * @return UnitOfWork
     */
    public function persist(BaseModel|AuthenticableBaseModel $model)
    {
        // $modelScope = ModelScope::getInstance();
        $this->startTransaction();
        try {
            if (empty($model->getId()) && $this->incomingRequestService->getUser()) {
                $model->setCreatedBy($this->incomingRequestService->getUser());
            }

            if (!empty($model->getId()) && $this->incomingRequestService->getUser()) {
                $model->setUpdatedBy($this->incomingRequestService->getUser());
            }

            $reflectionClass = new ReflectionClass($model);
            $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);

            $model->save();

            foreach ($properties as $property) {
                if (
                    $property->getType() &&
                    $property->getType()->getName() == BelongsToManyRelation::class
                ) {
                    $value = $property->getValue($model);
                    $value->setParentModel($model);

                    if ($value->getSyncedCollection()->count() > 0) {
                        $value->doSync();
                    } else {
                        $value->doAttach();
                        $value->doDetach();
                    }
                }

                if (
                    $property->getType() &&
                    $property->getType()->getName() == HasManyRelation::class
                ) {
                    $value = $property->getValue($model);
                    foreach ($value->getAddedModelCollection() as $addedCollection) {
                        $hasManyReflectionClass = new ReflectionClass($addedCollection);
                        $hasManyProperties = $hasManyReflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);

                        foreach ($hasManyProperties as $hasManyProperty) {
                            if (
                                $hasManyProperty->getType() &&
                                $hasManyProperty->getType()->getName() == BelongsToRelation::class
                            ) {
                                $belongsToRelation = $hasManyProperty->getValue($addedCollection);
                                $belongsToRelationModel = $belongsToRelation->get();
                                if (spl_object_hash($belongsToRelationModel) == spl_object_hash($model)) {
                                    $belongsToRelation->getRelation()->associate($model);
                                }
                            }
                        }

                        $addedCollection->save();
                    }

                    $value->emptyAddedModelCollection();
                }
            }
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $this;
    }

    protected function startTransaction()
    {
        if (!$this->isTransactionStarted) {
            DB::beginTransaction();
            $this->isTransactionStarted = true;
        }
    }

    protected function commit()
    {
        if ($this->isTransactionStarted) {
            $this->isTransactionStarted = false;
            DB::commit();
        }
    }

    protected function rollback()
    {
        if ($this->isTransactionStarted) {
            $this->isTransactionStarted = false;
            DB::rollBack();
        }
    }

    /**
     * Prepare entity that will be removed. Will removed after entity unit flush
     *
     * @param Model $model
     * @return UnitOfWork
     */
    public function remove(Model $model)
    {
        // $modelScope = ModelScope::getInstance();
        if (!$this->isTransactionStarted) {
            DB::beginTransaction();
            $this->isTransactionStarted = true;
        }

        if (method_exists($model, 'trashed')) {
            $model->trashed();
        } else {
            $model->forceDelete();
        }

        // $modelScope->addModel(ModelScope::PERFORM_DELETE, $model);
        return $this;
    }

    /**
     * Persist all entities to table
     *
     * @return void
     */
    public function flush()
    {
        try {
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
