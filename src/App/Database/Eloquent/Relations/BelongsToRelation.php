<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery;

class BelongsToRelation extends AbstractRelation
{
    protected Model $ownerModel;
    protected ?Model $ownedModel = null;
    protected string $related;
    protected ?string $foreignKey = null;
    protected ?string $ownerKey = null;
    protected ?string $relation = null;

    /**
     * Undocumented function
     *
     * @param Model $owningModel
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param string|null $relation
     */
    public function __construct(
        Model $ownerModel,
        string $related,
        ?string $foreignKey = null,
        ?string $ownerKey = null,
        ?string $relation = null
    ) {
        $this->ownerModel = $ownerModel;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
    }

    /**
     * get related data
     *
     * @return ?Model
     */
    public function get(): ?Model
    {
        if (!is_null($this->ownedModel)) {
            return $this->ownedModel;
        }

        return $this->getRelation()->getResults();
    }

    /**
     *
     * @param Model $ownedModel
     * @return BelongsToRelation
     */
    public function set(?Model $ownedModel): BelongsToRelation
    {
        if (!is_null(($ownedModel))) {
            $this->ownedModel = $ownedModel;
            $this->getRelation()->associate($ownedModel);
        } else {
            $onwedPersistedModel = $this->getRelation()->getResults();
            if (!is_null($onwedPersistedModel)) {
                $this->getRelation()->dissociate();
            }
            $this->ownedModel = null;
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
            $mock = Mockery::mock(BelongsTo::class)->makePartial();
            $mock->shouldReceive('associate')->andReturn($this->ownedModel);
            $mock->shouldReceive('getResults')->andReturn(null);

            return $mock;
        }

        return $this->ownerModel->belongsTo(
            $this->related,
            $this->foreignKey,
            $this->ownerKey,
            $this->relation
        );
    }
}
