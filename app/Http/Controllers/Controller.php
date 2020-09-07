<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $request = null;
    protected $input = null;

    public function __construct() {
        $this->request = request();
        $this->input = $this->request->all();
    }

    /**
     * api接口通用返回
     * @param int    $code
     * @param string $msg
     * @param array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function apiReturn($code = HTTP_RESPONSE_SUCCESS_CODE, $msg = HTTP_RESPONSE_SUCCESS_MSG, $data = []) {
        return response()->json(apiTemplate($code, $msg, $data));
    }

    /**
     * api接口成功返回
     * @param array  $data
     * @param string $msg
     * @param int    $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function succReturn($data = [], $msg = HTTP_RESPONSE_SUCCESS_MSG, $code = HTTP_RESPONSE_SUCCESS_CODE) {
        return $this->apiReturn($code, $msg, $data);
    }

    /**
     * api接口失败返回
     * @param string $msg
     * @param int    $code
     * @param array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failReturn($msg = HTTP_RESPONSE_FAIL_MSG, $code = HTTP_RESPONSE_FAIL_CODE, $data = []) {
        return $this->apiReturn($code, $msg, $data);
    }
}
