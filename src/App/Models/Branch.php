<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelCommon\App\Models\BaseModel;

class Branch extends BaseModel
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
     * @return Branch
     */
    public function setName(string $name): Branch
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     *
     * @param string $address
     * @return Branch
     */
    public function setAddress(string $address): Branch
    {
        $this->name = $address;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     *
     * @param string $address
     * @return Branch
     */
    public function setPhone(string $phone): Branch
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     *
     * @param string $address
     * @return Branch
     */
    public function setFax(string $fax): Branch
    {
        $this->fax = $fax;
        return $this;
    }



    /**
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * @param string $address
     * @return Branch
     */
    public function setEmail(string $email): Branch
    {
        $this->email = $email;
        return $this;
    }
}
