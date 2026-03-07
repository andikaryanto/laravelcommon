<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pricing extends BaseModel
{
    use HasFactory;

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     * @return Pricing
     */
    public function setName(string $name): Pricing
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param ?string $description
     * @return Pricing
     */
    public function setDescription(?string $description): Pricing
    {
        $this->description = $description;
        return $this;
    }
}
