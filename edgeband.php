<?php

//GET Parameters: $1/opt = What method to call, $2/p1, $3/p2, $4/p3, $5/p4 - parameters
// $1/opt -> 1 => getLaminateMfData
// $1/opt -> 2 => userAuthAndLaminatePermission - mainly for the App user. $2 -> Username, $3 -> Password
// $1/opt -> 3 => Gets the item list upto max 25 records. $2 is the min 3 character name
// $1/opt -> 4 => Get the sizes of the edgeband based on $2 which is the material

set_include_path(get_include_path() . PATH_SEPARATOR . "../classes" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

include_once 'config.php';
include_once 'ProductClass.php';
include_once 'UserClass.php';

$userClass = new Users();
$edgeband = new Products(0);
$retval = "";

$opt = $edgeband->cleanData(filter_input(INPUT_GET, "opt", FILTER_SANITIZE_STRING));

$inputtype = INPUT_POST;

$p1 = $edgeband->cleanData(filter_input($inputtype, "p1", FILTER_SANITIZE_STRING)); //Always userName
$p2 = $edgeband->cleanData(filter_input($inputtype, "p2", FILTER_SANITIZE_STRING)); // Always hashKey
if ($opt == "createorder") {
    $p3 = filter_input($inputtype, "p3", FILTER_UNSAFE_RAW);    
} else {
    $p3 = $edgeband->cleanData(filter_input($inputtype, "p3", FILTER_SANITIZE_STRING));
}
$p4 = $edgeband->cleanData(filter_input($inputtype, "p4", FILTER_SANITIZE_STRING));
$httpResponseCode = 200;

// Check the API Calls being made
/*
$userId = $userClass->getUserId($p1);
if ($userId > 0) {
    if ($userClass->canMakeAPICall($userId) == false) {
        $httpResponseCode = 403;
        $opt = "";
     } else {
        $userId = $userClass->apiCallValidation($p1, $p2);
        switch ($userId) {
           case Config::ERROR_LOGIN_AGAIN:
               $retval = '{"ERROR":"LOGIN-AGAIN"}';
               $opt = "";
               $httpResponseCode = 401;
               break;
           case Config::ERROR_INVALID_CREDENTIALS:
               $opt = "";
               $httpResponseCode = 401;
               break;
        }
     }
} else {
    $httpResponseCode = 401;
    $opt = "";
}
 */
$userId = $userClass->apiCallValidation($p1, $p2);
switch ($userId) {
   case Config::ERROR_LOGIN_AGAIN:
       $retval = '{"ERROR":"LOGIN-AGAIN"}';
       $opt = "";
       $httpResponseCode = 401;
       break;
   case Config::ERROR_INVALID_CREDENTIALS:
       $opt = "";
       $httpResponseCode = 401;
       break;
   default: 
       if ($userId > 0) {
            if ($userClass->canMakeAPICall($userId) == false) {
                $httpResponseCode = 403;
                $opt = "";
            }
        } else {
            $httpResponseCode = 401;
            $opt = "";
        }
        break;
}

/*
echo "Opt: " . $opt . "\n";
echo "P1: " . $p1 . "\n";
echo "P2: " . $p2 . "\n";
echo "P3: " . $p3 . "\n";
echo "P4: " . $p4 . "\n";
*/
switch (filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_STRING)) {
    case "POST":
        switch ($opt) {
            case "lammf": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_READ_LAMINATE_MANUFACTURER_LIST));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_READ_LAMINATE_MANUFACTURER_LIST] == "1") {
                    $retval = $edgeband->getLaminateMfData();
                }
                break;
            case "itemmst": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_READ_ITEM_MASTER));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_READ_ITEM_MASTER] == "1") {
                    $retval = $edgeband->getItemMasterList($p3, 25);
                }
                break;
            case "sizes": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_READ_SIZES));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_READ_SIZES] == "1") {
                    $retval = $edgeband->getEdgebandSizeList($p3);
                }
                break;
            case "companion": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_GET_COMPANION_CODE));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_GET_COMPANION_CODE] == "1") {
                    $retval = $edgeband->getCompanionCode($p3);
                }
                break;
            case "lammatch": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_LAMINATE_SEARCH));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_LAMINATE_SEARCH] == "1") {
                    $retval = $edgeband->searchLaminateMatch($p1, $p3, $p4);
                }
                break;
            case "createorder": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_ORDER_CREATION));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_ORDER_CREATION] == "1") {
                    $retval = $edgeband->createOrder($userId, $p3);
                }
                break;
            case "orderH": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_ORDER_REPORTING));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_ORDER_REPORTING] == "1") {
                    $retval = $edgeband->getOrderHeader($userId);
                }
                break;
        }
        break;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
http_response_code(200);
echo $retval;
?>