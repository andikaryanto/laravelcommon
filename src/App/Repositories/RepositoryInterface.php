<?php

namespace LaravelCommon\App\Repositories;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Class to map collection model
     *
     * @return string
     */
    public function collectionClass();

    /**
     * Class to map view model
     *
     * @return string
     */
    public function viewModelClass();

    /**
     * Undocumented function
     *
     * @param IEntity $entity
     * @return void
     */
    public function validateModel(Model $entity);
}
