<?php

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class BaseController extends REST_Controller
{
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
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
        if (!$error) {
            if (isset(errorInfos()[$status])) {
                $error = errorInfos()[$status];
            }
        }
        if (!$error) {
            $error = $status;
        }
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
        $this->failure(ERROR_AT_LEAST_ONE_UPDATE);
        return true;
    }

    protected function checkIfObjectNotExists($object)
    {
        if ($object == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST);
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

    protected function toNumber($genericStringNumber)
    {
        return $genericStringNumber + 0;
    }

    protected function postParams($selectedKeys)
    {
        $toArray = array();
        foreach ($selectedKeys as $field) {
            $value = $this->post($field);
            if ($value !== null) {
                $toArray[$field] = $value;
            }
        }
        return $toArray;
    }

    // users
    protected function requestToken()
    {
        $token = $this->get(KEY_SESSION_TOKEN);
        if (!$token) {
            $token = $this->input->get_request_header(KEY_SESSION_HEADER, TRUE);
            if (!$token) {
                if (isset($_COOKIE[KEY_COOKIE_TOKEN])) {
                    $token = $_COOKIE[KEY_COOKIE_TOKEN];
                }
            }
        }
        return $token;
    }

    protected function checkAndGetSessionUser()
    {
        $user = $this->getSessionUser();
        if ($user == null) {
            $this->failure(ERROR_NOT_IN_SESSION);
            return null;
        } else {
            return $user;
        }
    }

    protected function getSessionUser()
    {
        $token = $this->requestToken();
        if ($token) {
            return $this->userDao->findUserBySessionToken($token);
        } else {
            return null;
        }
    }

    protected function checkIfNotAdmin()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return true;
        } else {
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            if ($user != 'admin' && $password != 't3P3iYqF') {
                $this->failure(ERROR_NOT_ADMIN);
                return true;
            } else {
                return false;
            }
        }
    }

}

