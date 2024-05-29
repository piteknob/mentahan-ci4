<?php

namespace App\Controllers;

use App\Controllers\Core\DataController;
use CodeIgniter\HTTP\ResponseInterface;

class Product extends DataController
{

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $query['data'] = ['product'];

        $query['select'] = [
            'product_id' => 'id',
            'product_stock_id' => 'stock_id',
            'product_name' => 'product',
            'product_category_name' => 'category',
            'product_stock_unit_name' => 'unit',
            'product_stock_price_sell' => 'price_sell',
            'product_stock_price_buy' => 'price_buy',
            'product_stock_value' => 'stock',
            'product_stock_in' => 'stock_in',
            'product_stock_out' => 'stock_out',
            'product_created_at' => 'created',
            'product_updated_at' => 'updated',
        ];

        $query['join'] = [
            'product_stock' => 'product_stock.product_stock_product_id = product.product_id'
        ];

        $query['pagination'] = [
            'pagination' => true
        ];

        $query['limit'] = [
            'limit' => 1
        ];

        $data = generateListData($this->request->getVar(), $query, $this->db);
        
        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'List Product', $data);
    }

    public function insert()
    {
        $db = db_connect();
        $post = $this->request->getPost();

        $product = htmlspecialchars($post['product']);
        $category = htmlspecialchars($post['category']);
        $unit = htmlspecialchars($post['unit']);
        $stock = htmlspecialchars($post['stock']);
        $buy = htmlspecialchars($post['buy']);
        $sell = htmlspecialchars($post['sell']);

        $db->transStart();

        $queryProduct = "INSERT INTO product (product_name, product_category_id, product_category_name, product_created_at)
        SELECT '{$product}', category_id, category_name, NOW()
        FROM category
        WHERE category_id = '{$category}'";

        $queryProduct = $db->query($queryProduct);

        // Get lastest Id inserted 
        $idProduct = $db->insertID();

        $queryStock = "INSERT INTO product_stock (product_stock_product_id, product_stock_product_name, product_stock_unit_id, product_stock_unit_name, product_stock_value, product_stock_price_buy, product_stock_price_sell)
            SELECT '{$idProduct}', '{$product}', '{$unit}', unit_name, '{$stock}', '{$buy}', '{$sell}'
            FROM unit
            WHERE unit_id = '{$unit}'";

        $db->query($queryStock);

        $db->transComplete();

        $getProduct = "SELECT  product.product_name, product.product_category_id, product_stock.product_stock_unit_id, product_stock.product_stock_value, product_stock.product_stock_price_buy, product_stock.product_stock_price_sell, product.product_created_at
           FROM product
           LEFT JOIN product_stock
           ON product_stock.product_stock_product_id = product.product_id
           WHERE product.product_id = '{$idProduct}'";

        $data = $db->query($getProduct);
        $data = $data->getResultArray();

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfully Added', $data);
    }

    public function update($id = null)
    {
        $post = $this->request->getPost();
        $db = db_connect();


        $product = htmlspecialchars($post['product']);
        $category = htmlspecialchars($post['category']);
        $unit = htmlspecialchars($post['unit']);
        $stock = htmlspecialchars($post['stock']);
        $buy = htmlspecialchars($post['buy']);
        $sell = htmlspecialchars($post['sell']);

        $db->transStart();

        $queryUpdate = "UPDATE product
        SET product_name = '{$product}',
            product_category_id = '{$category}',
            product_category_name = (SELECT category_name FROM category WHERE category_id = '{$category}'),
            product_updated_at = NOW()
        WHERE product_id = '{$id}'";

        $db->query($queryUpdate);

        $queryProductStock = "UPDATE product_stock 
        SET product_stock_product_name = '{$product}',
            product_stock_unit_id = '{$unit}',
            product_stock_unit_name = (SELECT unit_name FROM unit WHERE unit_id = '{$unit}'),
            product_stock_value = '{$stock}',
            product_stock_price_buy = '{$buy}',
            product_stock_price_sell = '{$sell}'
        WHERE product_stock_product_id = '{$id}'";

        $db->query($queryProductStock);

        $db->transComplete();

        $data = [
            'product' => $product,
            'category' => $category,
            'unit' => $unit,
            'stock' => $stock,
            'buy' => $buy,
            'sell' => $sell
        ];

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfully Updated', $data);
    }

    public function delete($id = null)
    {
        $db = db_connect();
        $id = $this->request->getVar();
        foreach ($id as $key => $value) {
            $id = $value;
        }

    
        $query['data'] = [
            'product'
        ];

        $query['select'] = [
            'product_name' => 'product',
            'product_category_name' => 'category',
            'product_stock_unit_name' => 'unit',
            'product_stock_price_sell' => 'price_sell',
            'product_stock_price_buy' => 'price_buy',
            'product_stock_value' => 'stock',
            'product_stock_in' => 'stock_in',
            'product_stock_out' => 'stock_out',
            'product_created_at' => 'created',
            'product_updated_at' => 'updated'
        ];

        $query['join'] = [
            'product_stock' => 'product_stock.product_stock_product_id = product.product_id'
        ];

        // $query['where_detail'] = [
        //     'product_stock_product_id' => $id
        // ];

        $query['pagination'] = [
            'pagination' => false
        ];

        $db->transStart();

        $data = generateDetailData($this->request->getVar(), $query, $this->db);

        
        $queryDeleteStock = "DELETE FROM product_stock 
        WHERE product_stock_product_id = {$id}";

        $queryDeleteProduct = "DELETE FROM product
        WHERE product_id = {$id}";

        $db->query($queryDeleteStock);
        $db->query($queryDeleteProduct);

        $db->transComplete();

        return $this->responseSuccess(ResponseInterface::HTTP_OK, 'Data Successfully Deleted', $data);
    }
}
