<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Response;
use Psr\Log\LoggerInterface;
use App\Models\UserDao;



/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * User DAO
     */
    protected $userDao;

    /**
     * HTTP status codes
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->userDao = new UserDao();
    }

    /**
     * Response helpers
     */
    protected function responseResult($status, $result = null, $error = null, $total = null)
    {
        if ($result === null) {
            $result = new \stdClass();
        }
        if ($error === null) {
            $error = "";
        }
        if (!is_string($error)) {
            $error = json_encode($error);
        }
        $arr = [
            'status' => $status,
            'result' => $result,
            'error' => $error
        ];
        if ($total !== null) {
            $arr['total'] = $total;
        }

        $response = $this->response
            ->setStatusCode(self::HTTP_OK)
            ->setContentType('application/json')
            ->setJSON($arr);

        // CI3 compat: controllers call succeed()/failure() without return.
        // Echo the body so CI4 output buffering captures it.
        echo $response->getBody();

        return $response;
    }

    protected function responseJSON($obj)
    {
        $response = $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON($obj);

        echo $response->getBody();

        return $response;
    }

    protected function succeed($resultData = null, $total = null)
    {
        return $this->responseResult(REQ_OK, $resultData, null, $total);
    }

    protected function failureWithExtraMsg($status, $extraMsg)
    {
        return $this->failure($status, null, $extraMsg);
    }

    protected function failure($status, $error = null, $extraMsg = null)
    {
        if (!$error) {
            if (function_exists('errorInfos') && isset(errorInfos()[$status])) {
                $error = errorInfos()[$status];
            }
        }
        if (!$error) {
            $error = $status;
        }
        if ($extraMsg) {
            $error .= ' ' . $extraMsg;
        }
        if ($error) {
            if (defined('ERROR_NOT_IN_SESSION') && $status != ERROR_NOT_IN_SESSION) {
                $user = $this->getSessionUser();
                if ($user) {
                    if (function_exists('logInfo')) {
                        logInfo("server status: " . $status . " error:" . $error .
                            " userId:" . $user->userId . " name:" . $user->username);
                    }
                } else {
                    if (function_exists('logInfo')) {
                        logInfo("server status: " . $status . " error:" . $error);
                    }
                }
            }
        }
        return $this->responseResult($status, null, $error);
    }

    /**
     * Input helpers - CI4 style
     */
    protected function post($key = null)
    {
        if ($key === null) {
            return $this->request->getPost();
        }
        return $this->request->getPost($key);
    }

    protected function get($key = null)
    {
        if ($key === null) {
            return $this->request->getGet();
        }
        return $this->request->getGet($key);
    }

    /**
     * Parameter validation
     */
    protected function checkIfParamsNotExist($request, $params, $checkEmpty = true)
    {
        foreach ($params as $param) {
            if (!isset($request[$param])) {
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

    protected function checkIfNotAtLeastOneParam($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param])) {
                return false;
            }
        }
        if (defined('ERROR_AT_LEAST_ONE_UPDATE')) {
            $this->failure(ERROR_AT_LEAST_ONE_UPDATE);
        }
        return true;
    }

    protected function checkIfObjectNotExists($object)
    {
        if ($object == null) {
            if (defined('ERROR_OBJECT_NOT_EXIST')) {
                $this->failure(ERROR_OBJECT_NOT_EXIST);
            }
            return true;
        }
        return false;
    }

    protected function failureOfParam($param)
    {
        if (defined('ERROR_MISS_PARAMETERS')) {
            $this->failure(ERROR_MISS_PARAMETERS, "必须提供以下参数且不为空: " . $param);
        }
    }

    protected function checkIfNotInArray($value, $array)
    {
        if (!in_array($value, $array)) {
            $json = json_encode($array);
            if (defined('ERROR_PARAMETER_ILLEGAL')) {
                $this->failure(ERROR_PARAMETER_ILLEGAL, "$value 不在 $json 之中");
            }
            return true;
        }
        return false;
    }

    /**
     * Pagination helpers
     */
    protected function skip()
    {
        $skip = 0;
        if ($this->request->getGet(KEY_SKIP ?? 'skip')) {
            $skip = (int)$this->request->getGet(KEY_SKIP ?? 'skip');
        }
        return $skip;
    }

    protected function limit()
    {
        $limit = 100;
        if ($this->request->getGet(KEY_LIMIT ?? 'limit')) {
            $limit = (int)$this->request->getGet(KEY_LIMIT ?? 'limit');
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
        $toArray = [];
        foreach ($selectedKeys as $field) {
            $value = $this->post($field);
            if ($value !== null) {
                $toArray[$field] = $value;
            }
        }
        return $toArray;
    }

    /**
     * Session/Authentication helpers
     */
    protected function requestToken()
    {
        $token = $this->get(KEY_SESSION_TOKEN ?? 'sessionToken');
        if (!$token) {
            $token = $this->request->getHeaderLine(KEY_SESSION_HEADER ?? 'Session-Token');
            if (!$token) {
                $cookies = $this->request->getCookie();
                if (isset($cookies[KEY_COOKIE_TOKEN ?? 'token'])) {
                    $token = $cookies[KEY_COOKIE_TOKEN ?? 'token'];
                }
            }
        }
        return $token;
    }

    protected function checkAndGetSessionUser()
    {
        $user = $this->getSessionUser();
        if ($user == null) {
            if (defined('ERROR_NOT_IN_SESSION')) {
                $this->failure(ERROR_NOT_IN_SESSION);
            }
            return null;
        }
        return $user;
    }

    protected function getSessionUser()
    {
        $token = $this->requestToken();
        if ($token) {
            return $this->userDao->findUserBySessionToken($token);
        }
        return null;
    }

    protected function checkIfNotAdmin()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            if (defined('ERROR_NOT_ALLOW_DO_IT')) {
                $this->failure(ERROR_NOT_ALLOW_DO_IT);
            }
            return true;
        }
        $user = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        if ($user != 'admin' && $password != 't3P3iYqF') {
            if (defined('ERROR_NOT_ADMIN')) {
                $this->failure(ERROR_NOT_ADMIN);
            }
            return true;
        }
        return false;
    }
}
