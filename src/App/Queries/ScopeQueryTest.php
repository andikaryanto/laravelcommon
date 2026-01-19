<?php

namespace LaravelCommon\App\Queries;

use Codeception\Specify;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\Tests\IntegrationTest;
use Prophecy\PhpUnit\ProphecyTrait;

class ScopeQueryTest extends IntegrationTest
{
    use Specify;
    use ProphecyTrait;

    protected ScopeQuery $scopeQuery;

    public function test()
    {
        $this->beforeSpecify(function () {
            $this->scopeQuery = new ScopeQuery();
        });

        $this->describe('->getIterator()', function () {
            $result = $this->scopeQuery->getIterator();

            verify($result->count())->equals(3);
            verify($result->last()->getName())->equals('test');
        });

        $this->describe('->whereName()', function () {
            $result = $this->scopeQuery->whereName('partner')
                ->getIterator();

            verify($result->count())->equals(1);
        });
    }
}
