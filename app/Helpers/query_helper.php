<?php

if (!function_exists('generateDetailData')) {
    function generateDetailData($params, $query, $db)
    {
        $dataQuery = isset($query['data']) ? $query['data'] : '';
        $selectQuery = isset($query['select']) ? $query['select'] : '';
        $joinQuery = isset($query['join']) ? $query['join'] : '';
        $leftJoinQuery = isset($query['left_join']) ? $query['left_join'] : '';
        $rightJoinQuery = isset($query['right_join']) ? $query['right_join'] : '';
        $whereQuery = isset($query['where']) ? $query['where'] : '';
        $whereDetailQuery = isset($query['where_detail']) ? $query['where_detail'] : '';
        $id = isset($params['id']) ? $params['id'] : '';

        $data = (object) [];

        $sql = '';

        if (!empty($selectQuery)) {
            $sql .= selectData($selectQuery);
        }

        if (!empty($dataQuery)) {
            $sql .= dataFrom($dataQuery);
        }

        if (!empty($joinQuery)) {
            $sql .= joinTable($joinQuery);
        }

        if (!empty($leftJoinQuery)) {
            $sql .= leftJoin($leftJoinQuery);
        }

        if (!empty($rightJoinQuery)) {
            $sql .= rightJoin($rightJoinQuery);
        }

        if (!empty($whereQuery)) {
            $sql .= whereData($whereQuery);
        }

        if (!empty($whereDetailQuery)) {
            $sql .= whereDataDetail($whereDetailQuery);
        }


        // print_r($sql);
        // die;

        $sql = $db->query($sql)->getResultArray();
        $data->data = $sql;

        return $data;
    }
}

if (!function_exists('generateListData')) {
    function generateListData($params, $query, $db)
    {
        // --------------- set query --------------- //
        $dataQuery = isset($query['data']) ? $query['data'] : '';
        $selectQuery = isset($query['select']) ? $query['select'] : '';
        $searchQuery = isset($query['search_data']) ? $query['search_data'] : '';
        $joinQuery = isset($query['join']) ? $query['join'] : '';
        $leftJoinQuery = isset($query['left_join']) ? $query['left_join'] : '';
        $rightJoinQuery = isset($query['right_join']) ? $query['right_join'] : '';
        $whereQuery = isset($query['where']) ? $query['where'] : '';
        $whereDetailQuery = isset($query['where_detail']) ? $query['where_detail'] : '';
        $filterQuery = isset($query['filter']) ? $query['filter'] : '';
        $filterBetweenQuery = isset($query['filter_between']) ? $query['filter_between'] : '';
        $groupByQuery = isset($query['group_by']) ? $query['group_by'] : '';
        $orderByQuery = isset($query['order_by']) ? $query['order_by'] : '';
        $paginationResult = isset($query['pagination']) ? $query['pagination'] : '';


        // --------------- set params --------------- //
        $paginationPage = isset($params['page']) ? $params['page'] : 1;
        $search = isset($params['search']) ? $params['search'] : '';
        $category = isset($params['category']) ? $params['category'] : '';
        $start = isset($params['start']) ? $params['start'] : '';
        $end = isset($params['end']) ? $params['end'] : '';
        

        $data = (object) [];

        $sql = '';

        if (!empty($selectQuery)) {
            $sql .= selectData($selectQuery);
        }

        if (!empty($dataQuery)) {
            $sql .= dataFrom($dataQuery);
        }

        if (!empty($joinQuery)) {
            $sql .= joinTable($joinQuery);
        }

        if (!empty($leftJoinQuery)) {
            $sql .= leftJoin($leftJoinQuery);
        }

        if (!empty($rightJoinQuery)) {
            $sql .= rightJoin($rightJoinQuery);
        }

        if (!empty($whereQuery)) {
            $sql .= whereData($whereQuery);
            $where = true;
        } else {
            $where = false;
        }

        if (!empty($whereDetailQuery)) {
            $sql .= whereDataDetail($whereDetailQuery);
            $where = true;
        } else {
            $where = false;
        }

        if (!empty($searchQuery)) {
            $sql .= searchData($searchQuery, $search, $where);
            $where = true;
        } else {
            $where = false;
        }

        if (!empty($filterQuery)) {
            $sql .= filterData($category, $filterQuery, $where);
            $where = true;
        } else {
            $where = false;
        }

        if (!empty($filterBetweenQuery)) {
            $sql .= filterBetween($filterBetweenQuery, $start, $end, $where);
        }

        if (!empty($groupByQuery)) {
            $sql .= groupBy($groupByQuery);
        }

        if (!empty($orderByQuery)) {
            $sql .= orderBy($orderByQuery);
        }



        // Set Pagination from Params 

        $pagination = true;
        if (!empty($paginationResult)) {
            $pagination = paginationValue($paginationResult);
        }

        if (!empty($pagination)) {

            $countQuery = $sql;
            $countResult = $db->query($countQuery)->getResultArray();
            $countData = count($countResult);

            $limit = isset($query['limit']['limit']) ? $query['limit']['limit'] : 5;
            $offset = ($paginationPage - 1) * $limit;

            $sql .= " LIMIT {$offset}, {$limit}";

            $result = $db->query($sql)->getResultArray();
            $jumlahPage = ceil($countData / $limit);
            $pageSebelumnya = ($paginationPage - 1 > 0) ? ($paginationPage - 1) : null;
            $pageSelanjutnya = ($paginationPage + 1 <= $jumlahPage) ? ($paginationPage + 1) : null;

            // Data
            $data->data = $result;
            $data->pagination = [
                'jumlah_data' => $countData,
                'jumlah_page' => $jumlahPage,
                'page' => $paginationPage,
                'page_sebelumnya' => $pageSebelumnya,
                'page_selanjutnya' => $pageSelanjutnya
            ];
            return $data;
        }

        if (empty($pagination)) {
            return $db->query($sql)->getResultArray();
        }
    }
}





// ------------------------------------------- PRINTILAN ------------------------------------------- //

// Generate result Array to JSON
if (!function_exists('setToJSON')) {
    function setToJSON($data)
    {
        $data = json_encode($data);
        return $data;
    }
}


// Generate result JSON to Array
if (!function_exists('setToArray')) {
    function setToArray($data)
    {
        $data = json_decode($data);
        return $data;
    }
}


// Generate query data FROM ...
if (!function_exists('dataFrom')) {
    function dataFrom($data)
    {
        $query = " FROM ";
        foreach ($data as $key => $value) {
            $query .= "{$value}, ";
        }
        $query = rtrim($query, ', ');
        return $query;
    }
}


// Generate query select data
if (!function_exists('selectData')) {
    function selectData($data)
    {
        $query = 'SELECT ';
        foreach ($data as $key => $row) {
            $query .= "{$key} AS {$row}, ";
        }
        $query = rtrim($query, ', ');
        return $query;
    }
}


// Generate query limit 
if (!function_exists('limitData')) {
    function limitData($data)
    {
        $query = " LIMIT 0, {$data['limit']}";
        return $query;
    }
}


// Generate query search
if (!function_exists('searchData')) {
    function searchData($data, $search, $where)
    {
        $sql = '';
        if (empty($where)) {
            $sql .= " WHERE (";
            foreach ($data as $key => $row) {
                $sql .= "{$row} LIKE '%{$search}%' OR ";
            }
            $sql = rtrim($sql, ' OR ');
            $sql = "$sql)";
            return $sql;
        } else {
            $sql .= " AND (";
            foreach ($data as $key => $row) {
                $sql .= "{$row} LIKE '%{$search}%' OR ";
            }
            $sql = rtrim($sql, ' OR ');
            $sql = "$sql)";
            return $sql;
        }
    }
}


// Generate query filter
if (!function_exists('filterData')) {
    function filterData($data, $selectedField, $where)
    {
        if (!empty($where)) {
            $sql = ' AND (';
            foreach ($selectedField as $key => $value) {
                $sql .= "{$value} = '{$data}' OR ";
            }
            $sql = rtrim($sql, ' OR ');
            $sql = "$sql)";
            return $sql;
        } else {
            $sql = ' WHERE (';
            foreach ($selectedField as $key => $value) {
                $sql .= "{$value} = '{$data}' OR ";
            }
            $sql = rtrim($sql, ' OR ');
            $sql = "$sql)";
            return $sql;
        }
    }
}


// Generate query filter between
if (!function_exists('filterBetween')) {
    function filterBetween($data, $start, $end, $where)
    {
        if (empty($where)) {

            foreach ($data as $key => $value) {
                $sql = " WHERE {$value} BETWEEN {$start} AND {$end} AND ";
            }
            $sql = rtrim($sql, ' AND ');
            return $sql;
        } else {
            foreach ($data as $key => $value) {
                $sql = " AND {$value} BETWEEN {$start} AND {$end} AND ";
            }
            $sql = rtrim($sql, ' AND ');
            return $sql;
        }
    }
}


// Generate query inner join 
if (!function_exists('joinTable')) {
    function joinTable($data)
    {
        foreach ($data as $key => $row) {
            $join = " JOIN {$key} ON {$row}";
        }
        return $join;
    }
}


// Generate query left join
if (!function_exists('leftJoin')) {
    function leftJoin($data)
    {
        foreach ($data as $key => $row) {
            $join = " LEFT JOIN {$key} ON {$row}";
        }
        return $join;
    }
}


// Generate query right join 
if (!function_exists('rightJoin')) {
    function rightJoin($data)
    {
        foreach ($data as $key => $row) {
            $join = " RIGHT JOIN {$key} ON {$row}";
        }
        return $join;
    }
}


// Generate query where clause
if (!function_exists('whereData')) {
    function whereData($data)
    {
        $where = " WHERE ";
        foreach ($data as $key => $row) {
            $where .= "{$key} = {$row} AND ";
        }
        $where = rtrim($where, ' AND ');
        return $where;
    }
}


// Generate Where detail query
if (!function_exists('whereDataDetail')){
    function whereDataDetail($data)
    {
        foreach ($data as $key => $value) {
            $where = ' ';
            $where .= $value;
            return $where;
        }
    }
}


// Generate pagination value 
if (!function_exists('paginationValue')) {
    function paginationValue($data)
    {
        foreach ($data as $key => $value) {
            $pagination = $value;
            return $pagination;
        }
    }
}


// Generate query group by
if (!function_exists('groupBy')) {
    function groupBy($data)
    {
        foreach ($data as $row) {
            $query = " GROUP BY {$row},";
            $query = rtrim($query, ', ');
            return $query;
        }
    }
}


// Generate query order by
if (!function_exists('orderBy')) {
    function orderBy($data)
    {
        foreach ($data as $key => $row) {
            $query = " ORDER BY {$row},";
        }
        $query = rtrim($query, ', ');
        return $query;
    }
}
