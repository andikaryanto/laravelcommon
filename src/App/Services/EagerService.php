<?php

namespace LaravelCommon\App\Services;

use Illuminate\Database\Eloquent\Model;

class EagerService
{
    public static function getEager(Model $owner, string $method): ?Model
    {
        $eagerModel = $owner->$method;
        if ($eagerModel) {
            return $eagerModel;
        }

        return null;
    }
}
