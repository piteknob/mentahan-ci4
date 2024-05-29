<?php

namespace App\Controllers;

use App\Controllers\Core\DataController;
use CodeIgniter\HTTP\ResponseInterface;

class Category extends DataController
{
    public function index()
    {
        $query['data'] = ['category'];

        $query['select'] = [
            'category_id' => 'id',
            'category_name' => 'category',
        ];
        
        $query['where_detail'] = [
            'WHERE category_deleted_at IS NULL'
        ];

        $query['pagination'] = [
            'pagination' => false
        ];

        // $query['limit'] = [
        //     'limit' => 1,
        // ];

        $data = generateListData($this->request->getVar(), $query, $this->db);

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'list Category', $data);
    }

    public function insert()
    {
        $post = $this->request->getPost();
        $db = db_connect();

        $category = htmlspecialchars($post['category']);

        $sql = "INSERT INTO category VALUES('', '{$category}', NULL)";
        $sql = $db->query($sql);

        $id = $db->insertID();
        $data = "SELECT category_id AS id, category_name AS category FROM category WHERE category_id = '{$id}'";
        $data = $db->query($data);
        $data = $data->getResultArray();
        
        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfully Added', $data);
    }

    public function update()
    {
        $db = db_connect();
        $post = $this->request->getPost();

        $id = $_GET;
        $id = $id['id'];
        
        $category = $post['category'];

        $sql = "UPDATE category SET category_name = '{$category}' WHERE category_id = {$id}";
        $db->query($sql);
        $data = "SELECT category_id AS id, category_name AS category FROM category WHERE category_id = {$id}";
        $data = $this->db->query($data)->getResultArray();

        $updateProduct = "UPDATE product SET product_category_name = (SELECT category_name FROM category WHERE category_id = {$id})";
        $db->query($updateProduct);

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfully Updated', $data);
    }

    public function softDelete()
    {
        $db = db_connect();

        $id = $this->request->getVar();
        foreach ($id as $key => $value) {
            $id = $value;
        }

        $query['data'] = ['category'];

        $query['select'] = [
            'category_id' => 'id',
            'category_name' => 'category',
        ];
        
        $query['where'] = [
            'category_id' => $id
        ];

        $data = generateDetailData($this->request->getVar(), $query, $this->db);

        $sql = "UPDATE category SET category_deleted_at = NOW() WHERE category_id = {$id}";

        $db->query($sql);

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfull Deleted', $data);
    }

    public function restore()
    {
        $db = db_connect();

        $id = $this->request->getVar();
        foreach ($id as $key => $value) {
            $id = $value;
        }

        $query['data'] = ['category'];

        $query['select'] = [
            'category_id' => 'id',
            'category_name' => 'category',
        ];
        
        $query['where'] = [
            'category_id' => $id
        ];

        $data = generateDetailData($this->request->getVar(), $query, $this->db);

        $sql = "UPDATE category SET category_deleted_at = NULL WHERE category_id = {$id}";

        $db->query($sql);

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfull Restored', $data);
    }

    public function delete($id = null)
    {
        $db = db_connect();
        $id = $this->request->getVar();
        foreach ($id as $key => $value) {
            $id = $value;
        }

        $query['data'] = ['category'];

        $query['select'] = [
            'category_id' => 'id',
            'category_name' => 'category',
        ];

        $query['where_detail'] = [
            "WHERE category_id = {$id}"
        ];

        $data = generateDetailData($this->request->getVar(), $query, $db);


        $sql = "DELETE FROM category WHERE category_id = {$id}";
        $sql = $db->query($sql);
        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfull Permanently Deleted', $data);
    }
}
