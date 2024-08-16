<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use LaravelCommon\App\Consts\ResponseConst;
use LaravelCommon\Responses\JsonResponse;
use LaravelCommon\System\Http\Request;

class RequestValidatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$methods)
    {
        $method = $methods[0];
        $rules = $this->$method();


        $messageValue = [];
        if (count($methods) == 2) {
            $messageMethod = $methods[1];
            $messageValue = $this->$messageMethod();
        }

        $validator = Validator::make($request->all(), $rules, $messageValue);

        if ($validator->fails()) {
            $message = null;

            foreach ($validator->errors()->getMessages() as $key => $messageData) {
                if (is_array($messageData[0])) {
                    foreach ($messageData[0] as $messageValue) {
                        $message = $messageValue;
                        break;
                    }
                } else {
                    $message = $messageData[0];
                }

                if (!is_null($message)) {
                    break;
                }
            }

            return new JsonResponse(
                $message,
                Response::HTTP_BAD_REQUEST,
                ResponseConst::INVALID_DATA,
                $validator->errors()->getMessages()
            );
        }

        return $next($request);
    }
}
