<?php

namespace LaravelCommon\Utilities\Database;

use Exception;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToManyRelation;
use LaravelCommon\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionProperty;

class ModelUnit
{
    /**
     * Prepare entity that will be validated and persisted.
     * Will persisted after entity unit flush
     *
     * @see entity Model->validate()
     *
     * @param Model $model
     * @param bool $needValidate - validate entity that will be persisted
     * @throws ValidationException
     * @return ModelUnit
     */
    public function persist(Model $model)
    {
        $modelScope = ModelScope::getInstance();
        if (!$modelScope->transactionHasStarted()) {
            $modelScope->startTransaction();
        }

        $model->save();

        $reflectionClass = new ReflectionClass($model);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);

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
        }

        return $this;
    }

    /**
     * Prepare entity that will be removed. Will removed after entity unit flush
     *
     * @param Model $model
     * @return ModelUnit
     */
    public function remove(Model $model)
    {
        $modelScope = ModelScope::getInstance();
        $modelScope = ModelScope::getInstance();
        if (!$modelScope->transactionHasStarted()) {
            $modelScope->startTransaction();
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
        $modelScope = ModelScope::getInstance();

        try {
            // $modelScope->sort();
            // foreach ($modelScope->getModels() as $value) {
            //     $model = $value['model'];
            //     if ($value['perform'] == ModelScope::PERFORM_ADD_UPDATE) {
            //         $model->save();
            //     } elseif ($value['perform'] == ModelScope::PERFORM_DELETE) {
            //         if (method_exists($model, 'trashed')) {
            //             $model->trashed();
            //         } else {
            //             $model->forceDelete();
            //         }
            //     }
            // }

            $modelScope->commit();
            // $modelScope->clean();
        } catch (Exception $e) {
            $modelScope->rollback();
            // $modelScope->clean();
            throw $e;
        }
    }
}
