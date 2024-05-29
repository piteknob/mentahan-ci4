<?php

namespace App\Controllers;


use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Controllers\Core\DataController;

class User extends DataController
{
    protected $db;
    public function register()
    {
        $post = $this->request->getPost();
        $db = db_connect();

        $rules = [
            'email' => 'required|valid_email|is_unique[user.user_email]',
            'password' => 'required|min_length[5]',
            'confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return $this->responseErrorValidation(ResponseInterface::HTTP_PRECONDITION_FAILED, 'error validation', $this->validator->getErrors());
        }

        $email = htmlspecialchars($post['email']);
        $password = password_hash($post['password'], PASSWORD_BCRYPT);

        $insert = "INSERT INTO user VALUES('', '{$email}', '{$password}')";

        $this->db->query($insert);
        $user = $db->insertID();
        $user = "SELECT user_email,user_password FROM user WHERE user_id = '{$user}'";
        $user = $db->query($user)->getResultArray();

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'account successfully registered', $user);
    }


    public function login()
    {
        $post = $this->request->getPost();
        $db = db_connect();

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->responseErrorValidation(ResponseInterface::HTTP_PRECONDITION_FAILED, 'error validation', $this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $user = "SELECT *
        FROM user
        WHERE user_email = '{$email}'
        ";
        $user = $db->query($user)->getFirstRow('array');

        if (!$user) {
            return $this->responseFail(ResponseInterface::HTTP_NOT_FOUND, 'Email Not Found', 'Email not registered', $user);
        }

        if (!password_verify($password, $user['user_password'])) {
            return $this->responseFail(ResponseInterface::HTTP_NOT_FOUND, 'Password Not Match', 'Wrong Password', $user);
        }

        $key = getenv('TOKEN_SECRET');
        $payload = [
            'iat' => 1356999524,
            'nbf' => 1357000000,
            "uid" => $user['user_id'],
            "email" => $user['user_email']
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        $idUser = $payload['uid'];
        $email = $post['email'];
        $password = $post['password'];

        // find expired token + 1 hour
        $date = date("Y-m-d H:i:s");
        $currentDate = strtotime($date);
        $futureDate = $currentDate + (60 * 60);
        $formatDate = date("Y-m-d H:i:s", $futureDate);


        $getID = "SELECT auth_user_user_id FROM auth_user WHERE auth_user_email = '{$email}'";
        $resultID = $db->query($getID);
        $id = $resultID->getResultArray();

        if (!$id) {
            $insertAuth = "INSERT INTO auth_user (auth_user_user_id, auth_user_email, auth_user_password, auth_user_token, auth_user_date_login, auth_user_date_expired) 
                    SELECT user_id, '{$email}', '{$password}', '{$token}', NOW(), '{$formatDate}'
                    FROM user
                    WHERE user_email = '{$email}'";
            $db->query($insertAuth);
            return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Login Success', $token);
        }
        if ($id) {
            $updateAuth = "UPDATE auth_user SET
                    auth_user_date_login = NOW(),
                    auth_user_date_expired = '{$formatDate}' 
                    WHERE auth_user_user_id = '{$idUser}'";
            $db->query($updateAuth);
            return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Login Success', $token);
        }
    }
}
