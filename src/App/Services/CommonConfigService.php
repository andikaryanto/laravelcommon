<?php

namespace LaravelCommon\App\Services;

class CommonConfigService
{
    public function getValue(string $key): mixed
    {
        if (isset(config('common-config')['env'][env('APP_ENV')][$key])) {
            return config('common-config')['env'][env('APP_ENV')][$key];
        }

        return null;
    }

    public function getNonEnvValue(string $key): mixed
    {
        if (isset(config('common-config')[$key])) {
            return config('common-config')[$key];
        }

        return null;
    }
}
