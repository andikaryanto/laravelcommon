<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoggingConfig extends Model
{
    use HasFactory;
    use TraitModel;

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
     * @return LoggingConfig
     */
    public function setName(string $name): LoggingConfig
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     *
     * @param string $isEnabled
     * @return LoggingConfig
     */
    public function setIsEnabled(bool $isEnabled): LoggingConfig
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }
}
