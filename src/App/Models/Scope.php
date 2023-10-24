<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\Groupuser\ScopeMapping as GroupuserScopeMapping;
use LaravelCommon\App\Models\User\ScopeMapping;

class Scope extends Model
{
    use HasFactory;
    use TraitModel;

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     * @return Scope
     */
    public function setName(string $name): Scope
    {
        $this->name = $name;
        return $this;
    }
}
