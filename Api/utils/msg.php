<?php

/**
 * 封装消息返回类
 */

namespace core;


class msg
{
    private $code;
    private $msg;
    private $data;
    private $count;

    public function __construct($code = 0, $msg = "", $data = array(), $count = 0)
    {
        $this->code = $code;
        $this->data = $data;
        $this->msg = $msg;
        $this->count = $count;
    }

    /**
     * @return int|mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int|mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed|string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed|string $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int|mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int|mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getJson()
    {
        $message = [
            'code' => $this->getCode(),
            'msg' => $this->getMsg(),
            'count' => $this->getCount(),
            'data' => $this->getData()

        ];
        return $message;
    }
}