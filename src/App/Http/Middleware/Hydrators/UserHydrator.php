<?php

namespace LaravelCommon\App\Http\Middleware\Hydrators;

use LaravelCommon\App\Http\Middleware\Hydrator;
use LaravelCommon\App\Repositories\GroupuserRepository;
use LaravelCommon\App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserHydrator extends Hydrator
{
    public const NAME = 'common.app.middelware.hydrator.user';

    /**
     * @var GroupuserRepository
     */
    protected GroupuserRepository $groupuserRepository;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     *
     * @param GroupuserRepository $groupuserRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        GroupuserRepository $groupuserRepository,
        UserRepository $userRepository
    ) {
        parent::__construct('user', $userRepository);
        $this->groupuserRepository = $groupuserRepository;
    }

    /**
     * @inheritDoc
     */
    public function hydrate()
    {
        $this->when(
            'groupuser.id',
            [$this->model, 'setGroupuser'],
            [$this->groupuserRepository, 'find']
        )->when(
            'username',
            [$this->model, 'setUsername']
        )->when(
            'email',
            [$this->model, 'setEmail']
        );
    }

    /**
     *
     * @inheritdoc
     */
    public function afterHydrate()
    {
        if (strtoupper($this->request->method()) == 'POST' && isset($this->request->password)) {
            $this->model->setPassword(Hash::make($this->request->password));
        }

        return $this;
    }
}
