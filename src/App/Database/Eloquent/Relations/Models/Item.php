<?php

namespace LaravelCommon\App\Database\Eloquent\Relations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelCommon\App\Database\Eloquent\Relations\HasManyRelation;
use LaravelCommon\App\Models\BaseModel;

class Item extends BaseModel
{
    use HasFactory;

    public const ITEM_MARGIN_PERCENT_ID = 1;
    public const ITEM_MARGIN_FLAT_ID = 2;

    protected $casts = [
        'expired_date' => 'datetime'
    ];

    protected HasManyRelation $itemStocks;

    public function __construct(array $attributes = [])
    {
        $this->itemStocks = new HasManyRelation($this, ItemStock::class, 'item_id');
    }
}
