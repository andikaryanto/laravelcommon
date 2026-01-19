<?php

namespace LaravelCommon\App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;

class EagerService
{
    public static function getEager(Model $owner, string $method = null): mixed
    {
        if(is_null($method)) {
            return null;
        }

        try {
            $eagerModel = $owner->$method;
            // throw error when no eager load happens, then return it as null
        } catch (Exception $e) {
            return null;
        }

        if ($eagerModel) {
            return $eagerModel;
        }

        return null;
    }
}
