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
    protected bool $downloadAble;

    public function __construct(string $message, int $code, $reponseCode, $data = null, $additionalData = [], bool $downloadAble = false)
    {
        $this->message = $message;
        $this->data = $data;
        $this->additionalData = $additionalData;
        $this->code = $code;
        $this->reponseCode = $reponseCode;
        $this->downloadAble = $downloadAble;
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

        if (!$this->downloadAble) {
            $data = [BaseResponse::RESOURCES_KEY => $this->data];
            $data = array_merge($data, $this->additionalData);

            $json['message'] = $this->message;
            $json['data'] = $data;
            $json['response'] = $this->reponseCode;

            return response()->json($json, $this->code);
        } else {
            return response()->download($this->data)->deleteFileAfterSend(true);
        }
    }

    /**
     * Get response message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code) {
        $this->code = $code;
    }
}
