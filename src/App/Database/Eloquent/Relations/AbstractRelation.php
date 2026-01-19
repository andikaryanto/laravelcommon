<?php

namespace LaravelCommon\App\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;

abstract class AbstractRelation
{
    abstract public function getRelation(): mixed;
    protected ?string $name = null;

    protected function isUnitTest()
    {
        return App::runningUnitTests() && env('TEST_TYPE') === 'unit';
    }

    public function setName(string $name): AbstractRelation
    {
        $this->name = $name;
        return $this;
    }
}
