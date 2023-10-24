<?php

namespace LaravelCommon\Responses;

use Illuminate\Http\Response as HttpResponse;

class BaseResponse extends HttpResponse implements ResponseInterface
{
    public const RESOURCES_KEY = '_resources';

    /**
     * @var string
     */
    protected string $message;

    /**
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $additionalData;

    /**
     * @var integer
     */
    protected int $code;

    /**
     * @var array
     */
    protected array $reponseCode;

    public function __construct(string $message, int $code, $reponseCode, $data = [], $additionalData = [])
    {
        $this->message = $message;
        $this->data = $data;
        $this->additionalData = $additionalData;
        $this->code = $code;
        $this->reponseCode = $reponseCode;
        parent::__construct(json_encode($data), $code);
    }

    public function setData($data = null)
    {
        $this->data = $data;
    }

    public function setAdditional($additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * Get data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function sendJson()
    {

        $data = [BaseResponse::RESOURCES_KEY => $this->data];
        $data = array_merge($data, $this->additionalData);

        $json['message'] = $this->message;
        $json['data'] = $data;
        $json['response'] = $this->reponseCode;

        return response()->json($json, $this->code);
    }

    /**
     * Get response message
     *
     * @return void
     */
    public function getMessage()
    {
        return $this->message;
    }
}
