<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\QueryException;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Exceptions\ResponsableException;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\Responses\BaseResponse;
use LaravelCommon\System\Http\Request;
use LaravelCommon\Utilities\Database\UnitOfWork as DatabaseUnitOfWork;

class UnitOfWorkMiddleware
{
    public const NAME = 'common.app.middlware.unit-of-work-middleware';

    /**
     *
     * @var DatabaseUnitOfWork
     */
    protected DatabaseUnitOfWork $unitOfWork;

    /**
     *
     * @param DatabaseUnitOfWork $unitOfWork
     */
    public function __construct(
        DatabaseUnitOfWork $unitOfWork
    ) {
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $method)
    {
        $response = $next($request);

        try {
            if ($response instanceof BaseResponse) {
                if ($response->getCode() < 300) {
                    $this->$method($request, $response);
                }
            }
        } catch (Exception $e) {
            if ($e instanceof ResponsableException) {
                $response = $e->getResponse();
                return $response;
            }

            throw $e;
        }

        return $response;
    }

    private function persist($request)
    {
        try {
            $resource = $request->getResource();
            $this->unitOfWork->persist($resource);
            $this->unitOfWork->flush();
        } catch (QueryException $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::DATA_EXIST));
        }
    }

    private function remove($request)
    {
        try {
            $resource = $request->getResource();
            $this->unitOfWork->remove($resource);
            $this->unitOfWork->flush();
        } catch (Exception $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::DATA_EXIST));
        }
    }

    private function commit($request)
    {
        try {
            $this->unitOfWork->flush();
        } catch (Exception $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::DATA_EXIST));
        }
    }
}
