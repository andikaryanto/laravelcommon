<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;
use LaravelCommon\App\Models\BaseModel;

class Branch extends BaseModel
{
    use HasFactory;

    protected BelongsToRelation $pricing;
    protected BelongsToRelation $selectedPricing;

    public function __construct(array $attributes = [])
    {
        $this->pricing = new BelongsToRelation($this, Pricing::class, 'pricing_id');
        $this->selectedPricing = new BelongsToRelation($this, Pricing::class, 'selected_pricing_id');
        parent::__construct($attributes);
    }

    /**
     *
     * @return ?Pricing
     */
    public function getPricing(): ?Pricing
    {
        return $this->pricing->get();
    }

    /**
     *
     * @param Pricing $pricing
     * @return Branch
     */
    public function setPricing(Pricing $pricing): Branch
    {
        $this->pricing->set($pricing);
        return $this;
    }

    /**
     *
     * @return ?Pricing
     */
    public function getSelectedPricing(): ?Pricing
    {
        return $this->selectedPricing->get();
    }

    /**
     *
     * @param Pricing $pricing
     * @return Branch
     */
    public function setSelectedPricing(Pricing $pricing): Branch
    {
        $this->selectedPricing->set($pricing);
        return $this;
    }

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
        $this->address = $address;
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

    public function getRegency(): ?string
    {
        return $this->regency;
    }

    /**
     *
     * @param ?string $name
     * @return Branch
     */
    public function setRegency(?string $regency): Branch
    {
        $this->regency = $regency;
        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo_url;
    }

    /**
     *
     * @param ?string $logoUrl
     * @return Branch
     */
    public function setLogoUrl(?string $logoUrl): Branch
    {
        $this->logo_url = $logoUrl;
        return $this;
    }

    public function getConfirmationPhone(): ?string
    {
        return $this->confirmation_phone;
    }

    public function setConfirmationPhone(?string $confirmationPhone): Branch
    {
        $this->confirmation_phone = $confirmationPhone;
        return $this;
    }
}
