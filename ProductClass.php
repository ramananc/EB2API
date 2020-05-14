<?php

include_once "UtilsClass.php";
include_once "DatabaseClass.php";

class Products extends Database {

    function __construct() {
        parent::__construct();
    }

    public function getLaminateMfData() {
        return json_encode($this->getData('SELECT lam_mf_id AS id, lam_manufacturer AS name FROM laminate_master'));
    }
   
    public function getItemMasterList($partialMatch, $numRecords) {
        return json_encode($this->getData('SELECT eb_id AS itemid, eb_name AS itemname, material FROM eb_master '
                . 'WHERE eb_name LIKE "%' . $partialMatch . '%" ORDER BY eb_name LIMIT ' . $numRecords));
    }
    
    public function getEdgebandSizeList($mat) {
        return json_encode($this->getData('SELECT size FROM eb_sizes WHERE material = "' . $mat . '"'));
    }
    
    public function getCompanionCode($id) {
        $row = $this->getData('SELECT eb_id AS id, eb_name AS name, material FROM eb_master WHERE eb_id IN (SELECT companion_code FROM eb_master WHERE eb_id = "' . $id . '")');
        if (count($row) > 0) {
            return json_encode($row);
        } else {
            return "";
        }
    }
    
    public function searchLaminateMatch($userName, $manufacturerId, $laminateNumber) {
        $sSQL = 'SELECT a.lam_id, b.lam_manufacturer, a.lam_name, a.eb_id, c.eb_name, a.match_level, c.material '
            . 'FROM lam_mapping AS a, laminate_master AS b, eb_master AS c '
            . 'WHERE a.lam_mf_id = ' . $manufacturerId . ' AND lam_id = "' . $laminateNumber 
            . '" AND b.lam_mf_id = a.lam_mf_id AND a.eb_id = c.eb_id ORDER BY match_perc DESC';
        $result = $this->getData($sSQL);

        $products_arr = array();
        $products_arr["header"] = array();
        $products_arr["records"] = array();

        if (count($result) > 0) {
            $icount = 0;
            foreach($result as $row) {
                if ($icount == 0) {
                    $product_header = array("manufacturer" => $row['lam_manufacturer'], "laminate_number" => $row['lam_id'], "laminate_name" => $row['lam_name']);
                    array_push($products_arr["header"], $product_header);
                    $manufacturerName = $row['lam_manufacturer'];
                }
                $product_item = array("edgeband_id" => $row['eb_id'], "edgeband_name" => $row['eb_name'], "edgeband_match" => $row['match_level'], "material" => $row['material']);
                array_push($products_arr["records"], $product_item);
                $icount = $icount + 1;
            }
        } else {
            //$this->http_return_code = 204;
            $result = $this->getData("SELECT lam_manufacturer AS mf_name FROM laminate_master WHERE lam_mf_id = " . $manufacturerId);
            if (count($result) > 0) {
                $manufacturerName = $result[0]["mf_name"];
            } else {
                $manufacturerName = "";
            }
        }
        
        $utilsClass = new Utils();
        $utilsClass->sendEmail('laminatematch@aadhyaexperiences.com', 'Search - ' . $userName . ' for ' . $manufacturerName . '\\' . $laminateNumber, "");
        
        if (count($products_arr) > 0) {
            return json_encode($products_arr);
        } else {
            return "";
        }
    }
    
    public function createOrder($userId, $jsonOrderData) {
        $row = (array)json_decode($jsonOrderData, true);
        $headerDetails = (array)($row['header']);

        $sSQL = "INSERT INTO order_header (order_number, customer_id, order_date, tracking_number) VALUES ('"
                . $headerDetails[0]['order_num'] . "', " . $userId . ", '" . $headerDetails[0]['order_date'] . "', '"
                . $headerDetails[0]['tracking'] . "')" ;
        
        $dbResult = $this->setData($sSQL);
        if ($dbResult != "") {
            return json_encode($dbResult);
        }
        $orderId = $this->getLastInsertId();
        $itemDetails = (array)$row['records'];
        foreach ($itemDetails as $rec) {
            $sSQL = "SELECT eb_name, material FROM eb_master WHERE eb_id = '" . $rec['item_code'] . "'";
            $result = $this->getData($sSQL);
            if (count($result) > 0) {
               $sSQL = "INSERT INTO order_items (order_id, item_code, item_desc, item_material, item_qty, item_size) VALUES ("
                    . $orderId . ", '" . $rec['item_code'] . "', '" . $result[0]['eb_name'] . "', '" . $result[0]['material'] 
                    . "', " . $rec['qty'] . ", '" . $rec['size'] . "')";
            } else {
               $sSQL = "INSERT INTO order_items (order_id, item_code, item_qty, item_size) VALUES ("
                    . $orderId . ", '" . $rec['item_code'] . "', " . $rec['qty'] . ", '" . $rec['size'] . "')";
            }            
            $dbResult = $this->setData($sSQL);
            if ($dbResult != "") {
                $sSQL = "DELETE FROM order_items WHERE order_id = " . $orderId;
                $this->deleteData($sSQL);
                $this->deleteData("DELETE FROM order_header WHERE id = " . $orderId);
                return json_encode($dbResult);
            }
        }
        return "";
    }

    public function getOrderHeader($userId) {
        return json_encode($this->getData('SELECT id, order_number, order_date, tracking_number AS project, status '
                . ' FROM order_header WHERE customer_id = ' . $userId . ' ORDER BY order_date DESC LIMIT 15'));
    }
}
?>