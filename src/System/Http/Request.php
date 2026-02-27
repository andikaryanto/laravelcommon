<?php

namespace LaravelCommon\System\Http;

use LaravelCommon\App\Repositories\User\TokenRepository;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Http\Middleware\HydratorMiddleware;
use LaravelCommon\App\Models\Branch;

class Request extends CommonRequest
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
     * @var HydratorMiddleware
     */
    protected HydratorMiddleware $hydrator;

    /**
     *
     *
     * @var mixed
     */
    protected $resource;
    /**
     *
     *
     * @var mixed
     */
    protected $prevousResource;

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

    public function getBranch(): ?Branch
    {
        return $this->userToken->getUser()->getBranch();
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
     * Undocumented function
     *
     * @param mixed $entity
     * @return Request
     */
    public function setPreviousResource($entity)
    {
        $this->prevousResource = $entity;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param mixed $entity
     * @return Request
     */
    public function getPreviousResource()
    {
        return $this->prevousResource;
    }

    /**
     * Set hydrator
     *
     * @param HydratorMiddleware $hydrator
     * @return self
     */
    public function setHydratorMiddleware(HydratorMiddleware $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get hydrator
     *
     * @return HydratorMiddleware
     */
    public function getHydratorMiddleware()
    {
        return $this->hydrator;
    }
}
