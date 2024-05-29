<?php

namespace App\Controllers\Core;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

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
     * @var list<string>
     */
    protected $helpers = ['query'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    
    public function responseErrorValidation($statusCode = ResponseInterface::HTTP_PRECONDITION_FAILED, $errorCode = 'error validation', $data = [])
    {
        $message = '';
        foreach ($data as $key => $value) {
            $message .= "{$value}\n ";
        }

        $response = [
            'status' => $statusCode,
            'message' => $message,
            'error' => $errorCode,
            'result' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function responseSuccess($statusCode = ResponseInterface::HTTP_OK, $message = '', $data = [], $error = '')
    {
        $response = [
            'status' => $statusCode,
            'message' => $message,
            'error' => $error,
            'result' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function responsePagination($statusCode = ResponseInterface::HTTP_OK, $message = '', $data = [],$pagination = [], $error = '')
    {
        $response = [
            'status' => $statusCode,
            'message' => $message,
            'error' => $error,
            'result' => $data,
            'pagination' => $pagination
        ];
        return $this->response->setJSON($response);
    }

    public function responseFail($statusCode = ResponseInterface::HTTP_NOT_FOUND, $message = '', $error = '', $data = [])
    {
        $response = [
            'status' => $statusCode,
            'message' => $message,
            'error' => $error,
            'result' => $data,
        ];
        return $this->response->setJSON($response);
    }
}


