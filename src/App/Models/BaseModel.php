<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToManyRelation;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;
use LaravelCommon\App\Database\Eloquent\Relations\HasManyRelation;
use LaravelCommon\App\Database\Eloquent\Relations\HasOneRelation;
use ReflectionClass;
use ReflectionProperty;

class BaseModel extends Model
{
    use AuditableModel;

    public function __construct(array $attributes = [])
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if (
                $property->getType() &&
                (
                    $property->getType()->getName() == BelongsToRelation::class ||
                    $property->getType()->getName() == HasOneRelation::class ||
                    $property->getType()->getName() == HasManyRelation::class ||
                    $property->getType()->getName() == BelongsToManyRelation::class
                ) 
            ) {
                $propName = $property->getName();                
                $this->$propName->setName($propName);
            }
        }

        parent::__construct($attributes);
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function __call($method, $parameters)
    {
        $isRelationFound = $this->callRelationFound($method);
        if ($isRelationFound) {
            return $this->$method->getRelation();
        } else {
            return parent::__call($method, $parameters);
        }
    }
}
