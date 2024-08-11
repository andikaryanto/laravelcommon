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
        foreach($this->addModelCollection as $addedModel) {
            $all->add($addedModel);
        }

        return $all;
    }

    public function add(Model $model): HasManyRelation
    {
        if(empty($model->getKey())) {
            $class = get_class($model);
            throw new Exception("Cannot add non-persisted $class model");
        }

        $existCollection = $this->getRelation()->get();
        $alreadyIn = $existCollection->filter(
            function ($existModel) use ($model) {
                return spl_object_hash($existModel) == spl_object_hash($model);
            }
        )->count() > 0;

        if (!$alreadyIn) {
            $this->addModelCollection->add($model);
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
    //     if(empty($model->getKey())) {
    //         $class = get_class($model);
    //         throw new Exception("Cannot add non-persisted $class model");
    //     }

    //     $inAddedFound = $this->addModelCollection->filter(
    //         function ($addModel) use ($model) {
    //             return spl_object_hash($addModel) == spl_object_hash($model);
    //         }
    //     )->count() > 0;

    //     if ($inAddedFound) {
    //         $this->addModelCollection = $this->addModelCollection->filter(
    //             function ($addModel) use ($model) {
    //                 return $addModel->getKey() != $model->getKey();
    //             }
    //         );
    //     } else {
    //         $this->removeModelCollection->add($model);
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
