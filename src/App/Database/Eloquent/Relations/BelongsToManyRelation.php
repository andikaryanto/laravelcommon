<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use IteratorAggregate;

class BelongsToManyRelation implements IteratorAggregate
{
    protected Collection $addModelCollection;
    protected Collection $removeModelCollection;
    protected Collection $syncModelColection;

    public string $related;
    public ?string $table = null;
    public ?string $foreignPivotKey = null;
    public ?string $relatedPivotKey = null;
    public ?string $parentKey = null;
    public ?string $relatedKey = null;
    public ?string $relation = null;

    protected Model $parentModel;

    /**
     * Create a new belongs to many relationship instance.
     *
     * @param  Model  $parentModel
     * @param  string  $related
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @return void
     */
    public function __construct(
        Model $parentModel,
        string $related,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null,
        ?string $relation = null
    ) {
        $this->addModelCollection = new Collection();
        $this->removeModelCollection = new Collection();
        $this->syncModelColection = new Collection();
        $this->parentModel = $parentModel;
        $this->related = $related;
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relation = $relation;
    }

    /**
     *
     * @return Collection
     */
    public function getSyncedCollection(): Collection
    {
        return $this->syncModelColection;
    }

    /**
     * Add model to collection
     *
     * @param Model $model
     * @return BelongsToManyRelation
     */
    public function add(Model $model): BelongsToManyRelation
    {
        $existCollection = $this->getBelongsToMany()->get();
        $alreadyIn = $existCollection->filter(
            function ($existModel) use ($model) {
                return $existModel->getKey() != $model->getKey();
            }
        )->count() > 0;

        if (!$alreadyIn) {
            $this->addModelCollection->add($model);
        }

        return $this;
    }

    /**
     * Remove model from collection
     *
     * @param Model $model
     * @return BelongsToManyRelation
     */
    public function remove(Model $model): BelongsToManyRelation
    {
        $inAddedFound = $this->addModelCollection->filter(
            function ($addModel) use ($model) {
                return $addModel->getKey() == $model->getKey();
            }
        )->count() > 0;

        if ($inAddedFound) {
            $this->addModelCollection = $this->addModelCollection->filter(
                function ($addModel) use ($model) {
                    return $addModel->getKey() != $model->getKey();
                }
            );
        } else {
            $this->removeModelCollection->add($model);
        }
        return $this;
    }

    /**
     * Set collection of model
     *
     * @param Collection $modelCollection
     * @return BelongsToManyRelation
     */
    public function set(Collection $modelCollection): BelongsToManyRelation
    {
        $this->syncModelColection = $modelCollection;
        return $this;
    }

    /**
     *
     * @param Model $parentModel
     * @return BelongsToManyRelation
     */
    public function setParentModel(Model $parentModel): BelongsToManyRelation
    {
        $this->parentModel = $parentModel;
        return $this;
    }

    /**
     *
     * @return Model
     */
    public function getParentModel(): Model
    {
        return $this->parentModel;
    }

    /**
     *
     * @return Collection
     */
    private function getCollection(): Collection
    {
        $allCollection = new Collection();
        $existCollection = $this->getBelongsToMany()->get();

        foreach ($existCollection as $existModel) {
            $allCollection->add($existModel);
        }

        foreach ($this->addModelCollection as $addModel) {
            $allCollection->add($addModel);
        }

        return $allCollection;
    }

    /**
     * Persist model to database
     *
     * @return void
     */
    public function doAttach()
    {
        if ($this->addModelCollection->count() > 0) {
            foreach ($this->addModelCollection as $addModel) {
                $this->getBelongsToMany()->attach($addModel);
            }
            $this->addModelCollection = new Collection();
        }
    }

    /**
     * Remove model from database
     *
     * @return void
     */
    public function doDetach()
    {
        if ($this->removeModelCollection->count() > 0) {
            foreach ($this->removeModelCollection as $removeModel) {
                $this->getBelongsToMany()->detach($removeModel);
            }
            $this->removeModelCollection = new Collection();
        }
    }

    /**
     *
     * @return BelongsToMany
     */
    protected function getBelongsToMany(): BelongsToMany
    {
        return $this->parentModel->belongsToMany(
            $this->related,
            $this->table,
            $this->foreignPivotKey,
            $this->relatedPivotKey,
            $this->parentKey,
            $this->relatedKey,
            $this->relation
        );
    }

    /**
     * Sync Model Collection
     *
     * @return void
     */
    public function doSync()
    {
        $this->getBelongsToMany()->sync($this->syncModelColection);
    }

    /**
     * Get an iterator for the collection.
     *
     * @return Collection
     */
    public function getIterator(): Collection
    {
        return $this->getCollection();
    }
}
