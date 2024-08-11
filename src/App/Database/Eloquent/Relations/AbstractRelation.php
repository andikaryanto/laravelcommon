<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\Relation;

abstract class AbstractRelation
{
    abstract public function getRelation(): mixed;
}
