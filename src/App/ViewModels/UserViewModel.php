<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\Groupuser;
use LaravelCommon\App\Models\User;
use LaravelCommon\ViewModels\AbstractViewModel;
use stdClass;

class UserViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var User $model
     */
    protected $model;

    public function link()
    {
        return '/user/' . $this->model->getId();
    }

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        /**
         * @var Groupuser $groupuser
         */
        $groupuser = $this->model->getGroupuser();
        if (!empty($groupuser)) {
            $this->embedResource('groupuser', new GroupuserViewModel($groupuser, $this->request));
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'username' => $this->model->getUsername(),
            "is_active" => (bool)$this->model->getIsActive(),
            "email" => $this->model->getEmail(),
            "is_deleted" => $this->model->getIsDeleted(),
            "deleted_at" => $this->model->getDeletedAt()
        ];
    }
}
