<?php

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class BaseController extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    protected function responseResult($status, $result = null, $error = null, $total = null)
    {
        if ($result === null) {
            $result = new stdClass;
        }
        if ($error === null) {
            $error = "";
        }
        if (!is_string($error)) {
            // 确保一定是字符串,避免客户端解析崩溃
            $error = json_encode($error);
        }
        $arr = array(
            'status' => $status,
            'result' => $result
        );
        if ($total !== null) {
            $arr['total'] = $total;
        }
        $arr['error'] = $error;
        $this->response($arr, REST_Controller::HTTP_OK);
        //$this->responseJSON($arr);
    }

    protected function responseJSON($obj)
    {
        $this->output->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($obj));
    }

    protected function succeed($resultData = null, $total = null)
    {
        $this->responseResult(REQ_OK, $resultData, null, $total);
    }

    protected function failure($status, $error = null)
    {
        $this->responseResult($status, null, $error);
    }

    protected function checkIfParamsNotExist($request, $params, $checkEmpty = true)
    {
        foreach ($params as $param) {
            if (isset($request[$param]) == false) {
                $this->failureOfParam($param);
                return true;
            }
            if ($checkEmpty) {
                $trim = trim($request[$param]);
                if ($trim === '') {
                    $this->failureOfParam($param);
                    return true;
                }
            }
        }
        return false;
    }

    private function checkIfParamNotExists($param, $value)
    {
        if ($value == null || trim($value) === '') {
            $this->failureOfParam($param);
            return true;
        }
        return false;
    }

    protected function checkIfNotAtLeastOneParam($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param])) {
                return false;
            }
        }
        $this->failure(ERROR_AT_LEAST_ONE_UPDATE, "请至少提供一个可以修改的信息");
        return true;
    }

    protected function checkIfObjectNotExists($object)
    {
        if ($object == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "object with that id not exits");
            return true;
        } else {
            return false;
        }
    }

    protected function failureOfParam($param)
    {
        $this->failure(ERROR_MISS_PARAMETERS, "必须提供以下参数且不为空: " . $param);
    }

    protected function checkIfNotInArray($value, $array)
    {
        foreach ($array as $obj) {
            if ($obj === $value) {
                return false;
            }
        }
        $json = json_encode($array);
        $this->failure(ERROR_PARAMETER_ILLEGAL, "$value 不在 $json 之中");
        return true;
    }

    protected function skip()
    {
        $skip = 0;
        if (isset($_GET[KEY_SKIP])) {
            $skip = (int)$_GET[KEY_SKIP];
        }
        return $skip;
    }

    protected function limit()
    {
        $limit = 100;
        if (isset($_GET[KEY_LIMIT])) {
            $limit = (int)$_GET[KEY_LIMIT];
        }
        if ($limit > 1000) {
            $limit = 1000;
        }
        return $limit;
    }

    protected function castToNumber($genericStringNumber)
    {
        return $genericStringNumber + 0;
    }

    protected function patchParams($selectedKeys)
    {
        $toArray = array();
        foreach ($selectedKeys as $field) {
            $value = $this->patch($field);
            if ($value !== null) {
                $toArray[$field] = $value;
            }
        }
        return $toArray;
    }
}

