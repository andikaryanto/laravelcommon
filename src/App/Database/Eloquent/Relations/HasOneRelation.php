<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HasOneRelation extends AbstractRelation
{
    protected Model $ownerModel;
    protected ?Model $ownedModel = null;
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
        $this->ownerModel = $ownerModel;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localkey = $localkey;
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

        return $this->getRelation()->get()->first();
    }

    /**
     *
     * @return HasOne
     */
    public function getRelation(): HasOne
    {
        return $this->ownerModel->hasOne(
            $this->related,
            $this->foreignKey,
            $this->localkey
        );
    }
}
