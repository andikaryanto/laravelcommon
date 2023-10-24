<?php

namespace LaravelCommon\App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;
use LaravelCommon\App\Models\TraitModel;
use LaravelCommon\App\Models\User;

class Token extends Model
{
    use HasFactory;
    use TraitModel;


    protected $table = 'user_tokens';

    protected $casts = [
        'expired_at' => 'datetime'
    ];

    protected BelongsToRelation $user;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->user = new BelongsToRelation($this, User::class, 'user_id');
    }

    /**
     *
     * @return
     */
    public function getUser()
    {
        return $this->user->get();
    }

    /**
     *
     * @param User $user
     * @return void
     */
    public function setUser(User $user): Token
    {
        $this->user->set($user);
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     *
     * @param string $token
     * @return Token
     */
    public function setToken(string $token): Token
    {
        $this->token = $token;
        return $this;
    }

    /**
     *
     * @return Carbon
     */
    public function getExpiredAt(): Carbon
    {
        return $this->expired_at;
    }

    /**
     *
     * @param Carbon $expiredAt
     * @return Token
     */
    public function setExpiredAt(Carbon $expiredAt): Token
    {
        $this->expired_at = $expiredAt;
        return $this;
    }
}
