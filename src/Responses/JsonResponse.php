<?php

namespace LaravelCommon\Responses;

use Illuminate\Support\Facades\DB;
use LaravelCommon\ViewModels\AbstractViewModel;

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
        // DB::enableQueryLog();
        if (is_null($this->data)) {
            return null;
        }

        $newData = null;
        if ($this->data instanceof AbstractViewModel) {
            $newData = $this->data->loadRelation()->finalArray();
        } else {
            $newData = $this->data;
        }

        $this->setData($newData);

        // $quer = DB::getQueryLog();
        // dd($quer);
    }
}
