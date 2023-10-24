<?php

namespace LaravelCommon\Utilities\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ModelScope
{
    public const PERFORM_ADD_UPDATE = '1addUpdate';
    public const PERFORM_DELETE = '2delete';
    protected bool $isTransactionStarted = false;

    /**
     *
     * @var ModelScope|null
     */
    private static ?ModelScope $instance = null;

    /**
     *
     * @var array
     */
    private array $models = [];

    private function __construct()
    {
    }

    /**
     *
     * @return ModelScope
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Add model that will be persisted
     *
     * @param string $perfom
     * @param IModel $model
     * @param bool $needValidate - validate model that will be persisted
     * @return void
     */
    public function addModel(string $perfom, Model $model, $needValidate = true)
    {
        $isModelExist = false;
        if (isset($this->models)) {
            foreach ($this->models as $existedModel) {
                if ($model === $existedModel['model'] && $perfom === $model['perform']) {
                    $isModelExist = true;
                    break;
                }
            }
        }

        if (!$isModelExist) {
            $this->models[] = [
                'perform' => $perfom,
                'model' => $model,
                'needValidate' => $needValidate
            ];
        }
    }

    /**
     * Sort the models
     *
     * @return ModelScope
     */
    public function sort()
    {
        ksort($this->models);
        return $this;
    }

    /**
     * Get models scope
     *
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * Clean model scope
     *
     * @return void
     */
    public function clean()
    {
        $this->models = [];
    }

    /**
     * Determine if db transaction started;
     *
     * @return void
     */
    public function startTransaction()
    {
        DB::beginTransaction();
        $this->isTransactionStarted = true;
    }

    /**
     * Rollback transaction
     *
     * @return void
     */
    public function rollback()
    {
        DB::rollBack();
        $this->isTransactionStarted = false;
    }


    /**
     * Commit transaction
     *
     * @return void
     */
    public function commit()
    {
        DB::commit();
        $this->isTransactionStarted = false;
    }

    public function transactionHasStarted()
    {
        return $this->isTransactionStarted;
    }
}
