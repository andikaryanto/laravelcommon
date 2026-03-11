<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Support\Collection;
use LaravelCommon\App\Models\Groupuser;
use LaravelCommon\App\Models\User;
use LaravelCommon\ViewModels\AbstractViewModel;

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

    public static function loadWith(array $embeds = [])
    {
        return [
            'groupuser' => GroupuserViewModel::loadWith($embeds),
            'scopes' => ScopeViewModel::loadWith($embeds),
            'branch' => BranchViewModel::loadWith($embeds)
        ];
    }

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
        $groupuser = $this->model->groupuser;
        if (!empty($groupuser)) {
            $this->embedResource('groupuser', new GroupuserViewModel($groupuser, $this->request));
        }

        if (
            $this->request &&
            $this->request->get('embed') &&
            in_array('scope', $this->request->get('embed'))
        ) {
            $scopes = $this->model->scopes;
            if ($scopes->count() > 0) {
                $scopeViewModels = new Collection();
                foreach ($scopes as $scope) {
                    $scopeViewModels->add(new ScopeViewModel($scope, $this->request));
                }

                $this->embedResource('scopes', $scopeViewModels);
            }
        }

        $branch = $this->model->branch;
        if (!empty($branch)) {
            $this->embedResource('branch', new BranchViewModel($branch, $this->request));
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
            'username' => $this->model->getUsername(),
            "is_active" => (bool)$this->model->getIsActive(),
            "email" => $this->model->getEmail(),
            "is_deleted" => $this->model->getIsDeleted(),
            "deleted_at" => $this->model->getDeletedAt()
        ];
    }
}
