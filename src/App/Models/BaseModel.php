<?php

namespace LaravelCommon\App\Models;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class BaseModel extends Model
{
    use UsesTenantConnection;
}
