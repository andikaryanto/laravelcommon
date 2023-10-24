<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToManyRelation;

class Groupuser extends Model
{
    use HasFactory;
    use TraitModel;


    protected BelongsToManyRelation $scopes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->scopes = new BelongsToManyRelation($this, Scope::class, 'groupuser_scopes');
    }

    /**
     *
     * @return BelongsToManyRelation
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     *
     * @return Collection
     */
    public function setScopes(Collection $scopes)
    {
        return $this->scopes->set($scopes);
    }

    /**
     *
     * @param Scope $scope
     * @return Groupuser
     */
    public function addScope(Scope $scope): Groupuser
    {
        $this->scopes->add($scope);
        return $this;
    }

    /**
     *
     * @param Scope $scope
     * @return Groupuser
     */
    public function removeScope(Scope $scope): Groupuser
    {
        $this->scopes->remove($scope);
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->group_name;
    }

    /**
     *
     * @param string $groupname
     * @return Groupuser
     */
    public function setGroupName(string $groupname): Groupuser
    {
        $this->group_name = $groupname;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param string|null $description
     * @return Groupuser
     */
    public function setDescription(?string $description): Groupuser
    {
        $this->description = $description;
        return $this;
    }
}
