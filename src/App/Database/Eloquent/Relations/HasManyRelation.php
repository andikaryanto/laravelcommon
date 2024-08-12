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
    protected Collection $addModelCollection;
    protected Collection $removeModelCollection;
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
        $this->removeModelCollection = new Collection();
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
        $all = $this->getRelation()->get();
        // $this->addModelCollection will be emptied using emptyAddedModelCollection when data is persisted
        // so when it's not persisted the idea is to get the persisted relation and added collection.
        // when data persisted means $this->addModelCollection is in database then $this->getRelation()->get()
        // get them from database so that's why $this->addModelCollection should be emptied when  $ownerModel is persisted
        // this is not done yet, see UnitOfWork class at HasManyRelation section
        foreach ($this->addModelCollection as $addedModel) {
            $all->add($addedModel);
        }

        return $all;
    }

    public function getAddedModelCollection(): Collection
    {
        return $this->addModelCollection;
    }

    public function emptyAddedModelCollection(): HasManyRelation
    {
        $this->addModelCollection = new Collection();
        return $this;
    }

    public function add(Model $model): HasManyRelation
    {
        if (empty($model->getKey())) {
            $existCollection = $this->get();
            $alreadyIn = $existCollection->filter(
                function ($existModel) use ($model) {
                    return spl_object_hash($existModel) == spl_object_hash($model);
                }
            )->count() > 0;

            if (!$alreadyIn) {
                $this->addModelCollection->add($model);
            }
        } else {
            // if the model already persisted in database means it already has ID.
            // $this->get() will get it from database
            // the only reason we set up here is when we need to "mock"
            // for unit test because we dont involve database to test.
            $existCollection = $this->get();
            $alreadyIn = $existCollection->filter(
                function ($existModel) use ($model) {
                    return $existModel->isEqualTo($model);
                }
            )->count() > 0;

            if (!$alreadyIn) {
                $this->addModelCollection->add($model);
            }
        }

        return $this;
    }

    // /**
    //  * Remove model from collection
    //  *
    //  * @param Model $model
    //  * @return BelongsToManyRelation
    //  */
    // public function remove(Model $model): HasManyRelation
    // {
    //     if (empty($model->getKey())) {
    //         $class = get_class($model);
    //         throw new Exception("Cannot add non-persisted $class model");
    //     } else {
    //         $existCollection = $this->get();
    //         // $existCollection->()
    //         // $inAddedFound = $existCollection->filter(
    //         //     function ($addModel) use ($model) {
    //         //         return spl_object_hash($addModel) == spl_object_hash($model);
    //         //     }
    //         // )->count() > 0;

    //         // if ($inAddedFound) {
    //         //     $this->addModelCollection = $this->addModelCollection->filter(
    //         //         function ($addModel) use ($model) {
    //         //             return $addModel->getKey() != $model->getKey();
    //         //         }
    //         //     );
    //         // } else {
    //         //     $this->removeModelCollection->add($model);
    //         // }
    //     }
    //     return $this;
    // }

    /**
     *
     * @return mixed
     */
    public function getRelation(): mixed
    {
        if (App::runningUnitTests()) {
            // Create a mock of the BelongsToMany relationship
            $mock = Mockery::mock(BelongsToMany::class)->makePartial();

            // Configure the mock to return an empty collection when the get method is called
            $mock->shouldReceive('get')->andReturn(new Collection());

            return $mock;
        }

        return $this->ownerModel->hasMany(
            $this->related,
            $this->foreignKey,
            $this->localkey
        );
    }
}
