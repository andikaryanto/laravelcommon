<?php

namespace LaravelCommon\System\Database\Schema;

use Illuminate\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class Blueprint extends SchemaBlueprint
{
    public const STRING_25 = '25';
    public const STRING_50 = '50';
    public const STRING_100 = '100';
    public const STRING_200 = '200';
    public const STRING_255 = '255';
    public const STRING_SUPERSMALL = '5';
    public const STRING_EXTRASMALL = '10';
    public const STRING_SMALL = '25';
    public const STRING_MEDIUM = '50';
    public const STRING_LARGE = '100';
    public const STRING_EXTRALARGE = '255';
    public const STRING_SUPERLARGE = '1000';

    /**
     * created_by and updated_by column
     *
     * @return ColumnDefinition
     */
    public function auditable(): ColumnDefinition
    {
        $this->string('created_by')->nullable();
        return $this->string('updated_by')->nullable();
    }

    /**
     * created_by and updated_by column
     *
     * @return ColumnDefinition
     */
    public function softDelete(): ColumnDefinition
    {
        $this->boolean('is_deleted')->default(false);
        return $this->dateTime('deleted_at')->nullable();
    }
}
