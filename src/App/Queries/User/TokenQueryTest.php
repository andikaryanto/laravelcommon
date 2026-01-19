<?php

namespace LaravelCommon\App\Queries\User;

use Codeception\Specify;
use LaravelCommon\Tests\IntegrationTest;
use Prophecy\PhpUnit\ProphecyTrait;

class TokenQueryTest extends IntegrationTest
{
    use Specify;
    use ProphecyTrait;

    protected TokenQuery $tokenQuery;

    public function test()
    {
        $this->beforeSpecify(function () {
            $this->tokenQuery = new TokenQuery();
        });

        $this->describe('->getIterator()', function () {
            $result = $this->tokenQuery->getIterator();

            verify($result->count())->equals(2);
        });

        $this->describe('->whereToken()', function () {
            $result = $this->tokenQuery->whereToken('ABC')
                ->getIterator();

            verify($result->count())->equals(1);
        });
    }
}
