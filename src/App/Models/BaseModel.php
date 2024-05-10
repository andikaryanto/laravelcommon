<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use AuditableModel;

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
