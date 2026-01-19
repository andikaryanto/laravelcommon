<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scope extends BaseModel
{
    use HasFactory;
    
    /**
     *
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     * @return Scope
     */
    public function setName(string $name): Scope
    {
        $this->name = $name;
        return $this;
    }
}
