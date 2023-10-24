<?php

namespace LaravelCommon\App\Models\_Reserved;

class File
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    protected string $name;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected string $extension;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected string $mimeType;

    /**
     * Undocumented variable
     *
     * @var integer
     */
    protected int $size;

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $name  Undocumented variable
     *
     * @return  self
     */
    public function setName(string $name): File
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $extension  Undocumented variable
     *
     * @return  self
     */
    public function setExtension(string $extension): File
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $mimeType  Undocumented variable
     *
     * @return  self
     */
    public function setMimeType(string $mimeType): File
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  integer
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Set undocumented variable
     *
     * @param  integer  $size  Undocumented variable
     *
     * @return  self
     */
    public function setSize($size): File
    {
        $this->size = $size;

        return $this;
    }
}
