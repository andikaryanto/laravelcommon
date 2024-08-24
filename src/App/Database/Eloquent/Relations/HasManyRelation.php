<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Mockery;

class HasManyRelation extends AbstractRelation
{
    protected Model $ownerModel;
    protected Collection $testCollection;
    protected Collection $addModelCollection;
    protected Collection $removedModelCollection;
    protected string $related;
    protected ?string $foreignKey = null;
    protected ?string $localkey = null;

    /**
     * Undocumented function
     *
     * @param Model $owningModel
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $localkey
     */
    public function __construct(
        Model $ownerModel,
        string $related,
        ?string $foreignKey = null,
        ?string $localkey = null
    ) {
        $this->addModelCollection = new Collection();
        $this->removedModelCollection = new Collection();
        $this->testCollection = new Collection();
        $this->ownerModel = $ownerModel;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localkey = $localkey;
    }

    /**
     * get related data
     *
     * @return ?Collection
     */
    public function get(): ?Collection
    {
        /**
         * @var Collection $all
         */
        $all = $this->getRelation()->get();
        // $this->addModelCollection will be emptied using emptyAddedModelCollection when data is persisted
        // so when it's not persisted the idea is to get the persisted relation and added collection.
        // when data persisted means $this->addModelCollection is in database then $this->getRelation()->get()
        // get them from database so that's why $this->addModelCollection should be emptied when  $ownerModel is persisted
        // this is not done yet, see UnitOfWork class at HasManyRelation section
        // foreach ($this->addModelCollection as $addedModel) {
        //     $all->add($addedModel);
        // }

        return $all;
    }
    public function getTestCollection(): Collection
    {
        return $this->testCollection;
    }

    public function getAddedModelCollection(): Collection
    {
        return $this->addModelCollection;
    }

    public function getRemovedModelCollection(): Collection
    {
        return $this->removedModelCollection;
    }

    public function emptyAddedModelCollection(): HasManyRelation
    {
        $this->addModelCollection = new Collection();
        return $this;
    }

    public function emptyRemovedModelCollection(): HasManyRelation
    {
        $this->removedModelCollection = new Collection();
        return $this;
    }

    public function add(Model $model): HasManyRelation
    {
        if (empty($model->getKey())) {
            $this->removedModelCollection = $this->removedModelCollection->filter(
                function ($existModel) use ($model) {
                    return spl_object_hash($existModel) != spl_object_hash($model);
                }
            );

            $alreadyIn = $this->addModelCollection->filter(
                function ($existModel) use ($model) {
                    return spl_object_hash($existModel) == spl_object_hash($model);
                }
            )->count() > 0;

            if (!$alreadyIn) {
                $this->addModelCollection->add($model);
            }
        } else {
            $this->removedModelCollection = $this->removedModelCollection->filter(
                function ($existModel) use ($model) {
                    return !$existModel->isEqualTo($model);
                }
            );

            $existCollection = $this->get();
            $alreadyIn = $existCollection->filter(
                function ($existModel) use ($model) {
                    return $existModel->isEqualTo($model);
                }
            )->count() > 0;

            $alreadyInAddedCollection = $this->addModelCollection->filter(
                function ($existModel) use ($model) {
                    return $existModel->isEqualTo($model);
                }
            )->count() > 0;

            if (!$alreadyIn && !$alreadyInAddedCollection) {
                $this->addModelCollection->add($model);
            }
        }

        return $this;
    }

    /**
     * Remove model from collection
     *
     * @param Model $model
     * @return HasManyRelation
     */
    public function remove(Model $model): HasManyRelation
    {
        if (empty($model->getKey())) {
            $this->addModelCollection = $this->addModelCollection->filter(
                function ($existModel) use ($model) {
                    return spl_object_hash($existModel) != spl_object_hash($model);
                }
            );
        } else {
            $this->addModelCollection = $this->addModelCollection->filter(
                function ($existModel) use ($model) {
                    return !$existModel->isEqualTo($model);
                }
            );

            $existCollection = $this->get();
            $isExistInDatabase = $existCollection->filter(
                function ($existModel) use ($model) {
                    return $existModel->isEqualTo($model);
                }
            )->count() > 0;

            $alreayInRemove = $this->removedModelCollection->filter(
                function ($existModel) use ($model) {
                    return $existModel->isEqualTo($model);
                }
            )->count() > 0;

            if ($isExistInDatabase & !$alreayInRemove) {
                $this->removedModelCollection->add($model);
            }
        }
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getRelation(): mixed
    {
        if ($this->isUnitTest()) {
            $mock = Mockery::mock(BelongsToMany::class)->makePartial();
            $mock->shouldReceive('get')->andReturn($this->testCollection);

            return $mock;
        }

        return $this->ownerModel->hasMany(
            $this->related,
            $this->foreignKey,
            $this->localkey
        );
    }
}
