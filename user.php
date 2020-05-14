<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "../classes" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

include_once 'config.php';
include_once 'UserClass.php';

$userClass = new Users();
$retval = "";

$opt = $userClass->cleanData(filter_input(INPUT_GET, "opt", FILTER_SANITIZE_STRING));
$inputtype = INPUT_POST;

$p1 = $userClass->cleanData(filter_input($inputtype, "p1", FILTER_SANITIZE_STRING)); //Always username - A MUST
$p2 = $userClass->cleanData(filter_input($inputtype, "p2", FILTER_SANITIZE_STRING)); //Always hash_key except for authenticateUser where it will be password
$p3 = $userClass->cleanData(filter_input($inputtype, "p3", FILTER_SANITIZE_STRING));
$p4 = $userClass->cleanData(filter_input($inputtype, "p4", FILTER_SANITIZE_STRING));

$httpResponseCode = 200;

// Check the API Calls being made

$userId = $userClass->getUserId($p1);
if ($userId > 0) {
    if ($userClass->canMakeAPICall($userId) == false) {
        $httpResponseCode = 403;
        $opt = "";
     } else {
        if ($opt != "authuser") {
            $userId = $userClass->apiCallValidation($p1, $p2);
            switch ($userId) {
               case Config::ERROR_LOGIN_AGAIN:
                   $retval = '{"ERROR":"LOGIN-AGAIN"}';
                   $opt = "";
                   $httpResponseCode = 401;
                   break;
               case Config::ERROR_INVALID_CREDENTIALS:
                   $retval = "";
                   $opt = "";
                   $httpResponseCode = 401;
                   break;
            }
        }
     }
} else {
    $httpResponseCode = 401;
    $opt = "";
}

switch (filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_STRING)) {
    case "POST":
        switch ($opt) {
            case "authuser": $retval = $userClass->authenticateUser($p1, $p2);
                if ($retval == "") $httpResponseCode = 401;
                break;
            case "news": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_NEWS_FLASH));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_NEWS_FLASH] == "1")
                    $retval = $userClass->getNewsFlash($userId);
                break;
            case "chgpwd": $retval = $userClass->changePassword($p1, $p2, $p3);
                break;
            case "userrights": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_READ_ACCESS_RIGHTS));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_READ_ACCESS_RIGHTS] == "1")
                    $retval = $userClass->getUserAccessRights($userId);
                break;
            case "useraccess": $jsonData = (array)json_decode($userClass->getUserAccessRight($userId, Config::USER_ACCESS_RIGHTS_READ_ACCESS_RIGHTS));
                if ($jsonData[Config::USER_ACCESS_RIGHTS_READ_ACCESS_RIGHTS] == "1")
                    $retval = $userClass->getUserAccessRight($userId, $p3);
                break;
        }
        break;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
http_response_code($httpResponseCode);
echo $retval;
?>