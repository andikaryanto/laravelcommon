<?php

namespace LaravelCommon\App\ViewModels\User;

use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\ViewModels\UserViewModel;
use LaravelCommon\ViewModels\AbstractViewModel;
use stdClass;

class TokenViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var Token $model
     */
    protected $model;

    public static function loadWith(array $embeds = [])
    {
        return [
            'user' => UserViewModel::loadWith($embeds)
        ];
    }

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        $user = $this->model->user;
        if ($user) {
            $this->embedResource('user', new UserViewModel($user));
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'id' => $this->model->getId(),
            'token' => $this->model->getToken(),
            'expired_at' => $this->model->getExpiredAt()
        ];
    }
}
