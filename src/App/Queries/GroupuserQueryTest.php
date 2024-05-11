<?php

namespace LaravelCommon\App\Queries;

use Carbon\Carbon;
use Codeception\Specify;
use LaravelCommon\Tests\IntegrationTest;
use Prophecy\PhpUnit\ProphecyTrait;

class GroupuserQueryTest extends IntegrationTest
{
    use Specify;
    use ProphecyTrait;

    protected GroupuserQuery $groupuserQuery;

    public function test()
    {
        $this->beforeSpecify(function () {
            $this->groupuserQuery = new GroupuserQuery();
        });

        $this->describe('->getIterator()', function () {
            $result = $this->groupuserQuery->getIterator();

            verify($result->count())->equals(2);
        });

        $this->describe('->whereUserCreatedBefore()', function () {
            $result = $this->groupuserQuery
                ->whereUserCreatedBefore(Carbon::create('2023-11-10 10:10:10'))
                ->getIterator();

            verify($result->count())->equals(1);
        });

        $this->describe('->whereUserCreatedAfter()', function () {
            $result = $this->groupuserQuery
                ->whereUserCreatedAfter(Carbon::create('2023-10-09 10:10:10'))
                ->getIterator();

            verify($result->count())->equals(2);
        });

        $this->describe('->whereGroupName()', function () {
            $result = $this->groupuserQuery->whereGroupName('superadmin')
                ->getIterator();

            verify($result->count())->equals(1);
        });
    }
}
