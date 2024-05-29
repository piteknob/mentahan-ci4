<?php

namespace App\Filters;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use PhpParser\Node\Expr\ErrorSuppress;

class Auth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */

    protected $db;

    public function __construct()
    {
        $db = $this->db = \Config\Database::connect();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $db = db_connect();


        // Authorization Token
        $header = getallheaders();
        $token = $header['Token'];
        $getResult = "SELECT auth_user_token FROM auth_user WHERE auth_user_token = '{$token}'";
        $getResult = $db->query($getResult);
        $getResult = $getResult->getResultArray();

        // check token exist
        if (empty($token)) {
            return Services::response()
                ->setJSON([
                    'status' => ResponseInterface::HTTP_NETWORK_AUTHENTICATION_REQUIRED,
                    'message' => 'Token Required',
                    'error' => 'Token not inputed',
                    'inputed_token' => $token
                ]);
        }
        if (!$getResult) {
            return Services::response()
                ->setJSON([
                    'status' => ResponseInterface::HTTP_UNAUTHORIZED,
                    'message' => 'Invalid Token',
                    'error' => 'Token is not registered',
                    'inputed_token' => $token
                ]);
        } else {
            $user = "SELECT *
            FROM auth_user
            WHERE auth_user_token = '{$token}';
            ";
            $user = $db->query($user)->getFirstRow('array');

            $payload = [
                "expired" => $user['auth_user_date_expired']
            ];

            $expired = $payload["expired"];
            $tanggal = date('Y-m-d H:i:s');

            // Check Expired Token
            if ($expired < $tanggal) {
                return Services::response()
                    ->setJSON([
                        'status' => ResponseInterface::HTTP_REQUEST_TIMEOUT,
                        'message' => 'Token Expired',
                        'error' => 'Token date expired',
                        'inputed_token' => $token
                    ]);
            }
            // Add Active Time Expired Token (1 hour)
            $tanggal = date('Y-m-d H:i:s');
            $strtotime = strtotime($tanggal);
            $tambahBiling = $strtotime + (60 * 60);
            $biling = date('Y-m-d H:i:s', $tambahBiling);

            $updateExpired = "UPDATE auth_user 
            SET 
            auth_user_date_login = NOW(),
            auth_user_date_expired = '{$biling}'
            WHERE auth_user_token = '{$token}'";
            $db->query($updateExpired);

        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
