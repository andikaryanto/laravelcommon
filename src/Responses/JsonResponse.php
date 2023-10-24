<?php

namespace LaravelCommon\Responses;

use LaravelCommon\ViewModels\AbstractViewModel;
use LaravelCommon\ViewModels\PaggedCollection;

class JsonResponse extends BaseResponse
{
    protected $data = null;
    public function __construct(string $message, int $code = 200, $responseCode = [], $data = null)
    {
        parent::__construct($message, $code, $responseCode);
        $this->data = $data;
    }

    /**
     * Undocumented function
     *
     * @return mixed
     */
    public function buildData()
    {
        if (is_null($this->data)) {
            return null;
        }

        $newData = null;
        if ($this->data instanceof AbstractViewModel) {
            $newData = $this->data->finalArray();
        } else {
            $newData = $this->data;
        }

        $this->setData($newData);
    }
}
