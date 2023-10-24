<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToRelation
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

        return $this->getBelongsTo()->getResults();
    }

    /**
     *
     * @param Model $ownedModel
     * @return BelongsToRelation
     */
    public function set(Model $ownedModel): BelongsToRelation
    {
        $this->ownedModel = $ownedModel;
        $this->getBelongsTo()->associate($ownedModel);
        return $this;
    }

    /**
     *
     * @return BelongsTo
     */
    private function getBelongsTo(): BelongsTo
    {
        return $this->ownerModel->belongsTo(
            $this->related,
            $this->foreignKey,
            $this->ownerKey,
            $this->relation
        );
    }
}
