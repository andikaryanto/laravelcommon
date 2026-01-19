<?php

namespace LaravelCommon\App\Queries;

use App\Models\User;
use Codeception\Specify;
use LaravelCommon\Tests\IntegrationTest;
use Prophecy\PhpUnit\ProphecyTrait;

class UserQueryTest extends IntegrationTest
{
    use Specify;
    use ProphecyTrait;

    protected UserQuery $userQuery;

    public function test()
    {
        $this->beforeSpecify(function () {
            $this->userQuery = new UserQuery();
        });

        $this->describe('->getIterator()', function () {
            $result = $this->userQuery->getIterator();

            verify($result->count())->equals(2);
        });

        $this->describe('->whereUsername()', function () {
            $result = $this->userQuery->whereUsername('superadmin')
                ->getIterator();

            verify($result->count())->equals(1);
        });

        $this->describe('->whereEmail()', function () {
            $result = $this->userQuery->whereEmail('johnaspartner@mail.com')
                ->getIterator();

            verify($result->count())->equals(1);
        });
    }
}
