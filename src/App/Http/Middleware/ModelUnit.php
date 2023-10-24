<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Exception;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Exceptions\ResponsableException;
use LaravelCommon\Responses\BadRequestResponse;
use LaravelCommon\System\Http\Request;
use LaravelCommon\Utilities\Database\ModelUnit as DatabaseModelUnit;

class ModelUnit
{
    public const NAME = 'common.app.middlware.model-unit';

    /**
     *
     * @var DatabaseModelUnit
     */
    protected DatabaseModelUnit $modelUnit;

    /**
     *
     * @param DatabaseModelUnit $modelUnit
     */
    public function __construct(
        DatabaseModelUnit $modelUnit
    ) {
        $this->modelUnit = $modelUnit;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$method)
    {
        $response = $next($request);
        $resource = $request->getResource();

        try {
            if (strtoupper($request->method()) == 'DELETE') {
                $this->modelUnit->remove($resource);
            } else {
                $this->modelUnit->persist($resource);
            }

            $this->modelUnit->flush();
        } catch (Exception $e) {
            throw new ResponsableException($e->getMessage(), new BadRequestResponse($e->getMessage(), ResponseConst::DATA_EXIST));
        }

        return $response;
    }
}
