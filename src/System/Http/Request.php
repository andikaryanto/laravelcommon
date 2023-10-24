<?php

namespace LaravelCommon\System\Http;

use LaravelCommon\App\Repositories\User\TokenRepository;
use Illuminate\Http\Request as HttpRequest;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Http\Middleware\Hydrator;

class Request extends HttpRequest
{
    /**
     * @var TokenRepository $tokenRepository
     */
    protected TokenRepository $tokenRepository;

    /**
     * Undocumented variable
     *
     * @var ?Token
     */
    protected ?Token $userToken = null;

    /**
     * Undocumented variable
     *
     * @var Hydrator
     */
    protected Hydrator $hydrator;

    /**
     *
     *
     * @var mixed
     */
    protected $resource;

    /**
     * Undocumented function
     *
     * @param [type] $userToken
     * @return void
     */
    public function setUserToken(Token $userToken)
    {
        $this->userToken = $userToken;
    }

    /**
     * Undocumented function
     *
     * @return
     */
    public function getUserToken()
    {
        return $this->userToken;
    }

    /**
     * Undocumented function
     *
     * @param mixed $entity
     * @return Request
     */
    public function setResource($entity)
    {
        $this->resource = $entity;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set hydrator
     *
     * @param Hydrator $hydrator
     * @return self
     */
    public function setHydrator(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get hydrator
     *
     * @return Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
}
