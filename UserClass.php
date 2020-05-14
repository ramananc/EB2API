<?php
include_once 'config.php';
include_once "DatabaseClass.php";

class Users extends Database {


    function __construct() {
        parent::__construct();
    }
    
    function getUserId($userName) {
        $sSQL = "SELECT id FROM accounts WHERE username = '" . $userName . "' ";
        $row = $this->getData($sSQL);
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            return $row[0]['id'];
        }
        return -1;
    }

    function isMasterAccessKeyExists($accessKey) {
        $row = $this->getData("SELECT access_key, access_value FROM access_rights_master WHERE access_key = '" . $accessKey . "'");
        if (count($row) > 0) {
            return true;
        }
        return false;
    }
    
    public function getCompanyMasterData() {
        return json_encode($this->getData('SELECT company_id AS id, company_name AS name '
                . 'FROM company_master ORDER BY name'));
    } 
    
    public function getCompanyId($companyName) {
        $row = $this->getData('SELECT id FROM company_master WHERE company_name = "' . $companyName . '"');
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            return $row[0]['id'];
        }
        return -1;
    }

    // If a user makes more than 10 calls to API's within 2 minutes, then it will not allow the calls
    function canMakeAPICall($userId) {
        $sSQL = "SELECT call_count, start_time FROM api_calldata WHERE id = " . $userId;
        $row = $this->getData($sSQL);
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            if ($row[0]['call_count'] > 100) {
                $now = new DateTime();
                $compare = new DateTime($row[0]['start_time']);
                $diff = date_diff($now, $compare);
                if ($diff->format("%i") < 2) {
                    $this->setData("UPDATE api_calldata SET call_count = call_count + 1, start_time = now() WHERE id = " . $userId);
                    return false;
                } else {
                    $this->setData("UPDATE api_calldata SET call_count = 1, start_time = now() WHERE id = " . $userId);
                    return true;
                }
            } else {
                $this->setData("UPDATE api_calldata SET call_count = call_count + 1 WHERE id = " . $userId);
                return true;
            }
        } else if (!array_key_exists("Error", $row)) {
            $this->setData("INSERT INTO api_calldata (id, call_count, start_time) VALUES (" . $userId . ", 0, now())");
            return true;
        } else {
            return false;
        }
    }
    
    function apiCallValidation($userName, $hashKey) {
        $sSQL = "SELECT id, hash_created_on FROM accounts WHERE username = '" . $userName . "' "
                . "AND hash_key = '" . $hashKey . "' AND is_enabled = 1";
        $row = $this->getData($sSQL);
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            $diff = date_diff(date_create(), date_create($row[0]['hash_created_on']));
            if ($diff->days <= 1) {
                // 1 day validity
                return $row[0]['id'];
            } else {
                // Hash created more than 1 day ago - reset it
                $this->setData('UPDATE accounts SET hash_key = "", hash_created_on = "" WHERE id = ' . $row[0]['id']);
                return Config::ERROR_LOGIN_AGAIN;
            }
        }
        return Config::ERROR_INVALID_CREDENTIALS;
    }
    
    //Returns the array of access rights in key / value pair
    function getUserAccessRights($userId) {
        $sSQL = "SELECT access_key, access_value FROM user_permissions WHERE id = " . $userId;
        $row = $this->getData($sSQL);
        $rights = array();
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            for ($i = 0; $i < count($row); ++$i) {
                $rights[$row[$i]['access_key']] = $row[$i]['access_value'];
            }
        }
        return json_encode($rights);
    }
    
    //Returns access right if successfull else -1
    function getUserAccessRight($userId, $accessKey) {
        $row = (array)json_decode($this->getUserAccessRights($userId));
        $rights = array();
        if (count($row) > 0) {
            if (array_key_exists($accessKey, $row)) {
                $rights[$accessKey] = $row[$accessKey];
            } else {
                $rights[$accessKey] = "0";
            }
        } else {
            $rights[$accessKey] = "0";
        }
        return json_encode($rights);
    }

    //Returns the JSON encoded record on success, "" on failure
    function authenticateUser($userName, $password) {
        $sSQL = "SELECT id, password, company_name, display_name AS user_display_name, email AS user_email, "
                . "lam_default AS default_lammf_id "
                . "FROM accounts LEFT OUTER JOIN company_master ON accounts.company_id = company_master.company_id "
                ." WHERE is_enabled = 1 AND username = '" . $this->cleanData($userName) . "'";

        $retValue = "";
        $row = $this->getData($sSQL);
        
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            if (password_verify($password, $row[0]['password'])) {
                unset($row[0]['password']);
                $row[0]['hash'] = password_hash($userName . date("YmdHIs") . $password, PASSWORD_BCRYPT);
                $row[0]['username'] = $userName;
                $userRights = (array)json_decode($this->getUserAccessRights($row[0]['id']));
                if ($userRights[Config::USER_ACCESS_RIGHTS_SINGLE_ITEM_UPDATE] == "1") {
                    $row[0]['single_item_update'] = "true";
                } else {
                    $row[0]['single_item_update'] = "false";
                }
                if ($userRights[Config::USER_ACCESS_RIGHTS_ORDER_CREATION] == "1") {
                    $row[0]['create_order'] = "true";
                } else {
                    $row[0]['create_order'] = "false";
                }                
                $retValue = json_encode($row);
                
                $sSQL = 'UPDATE accounts SET hash_key = "' . $row[0]['hash'] . '", hash_created_on = now() '
                        . ' WHERE id = ' . $row[0]['id'];
                $this->setData($sSQL);
            }
        }
        return $retValue;
    }

    public function getNewsFlash($userId) {
        $newsSQL = 'SELECT content_header, content, username AS publish_by, company_name '
                . 'FROM ((news LEFT OUTER JOIN accounts ON publish_by = id) '
                . 'LEFT OUTER JOIN company_master ON accounts.company_id = company_master.company_id) '
                . 'WHERE disabled = 0 AND ';

        if ($userId != "") {
            $row = $this->getData('SELECT company_id FROM accounts WHERE id = ' . $userId);
            if (!array_key_exists("Error", $row) && count($row) > 0) {
                $company_id = $row[0]["company_id"];

                $newsSQL = $newsSQL .
                        '((target = "COMPANY" AND target_id = ' . $company_id . ') OR ' .
                        '(target = "GENERAL") OR (target = "USER" AND target_id = ' . $userId . ')) '
                        . 'AND published_on >= date_sub(now(), INTERVAL lifespan_hours hour)';
            } else {
                $newsSQL = $newsSQL .
                        '((target = "GENERAL") OR (target = "USER" AND target_id = ' . $userId . ')) '
                        . 'AND published_on >= date_sub(now(), INTERVAL lifespan_hours hour)';
            } 
        } else {
            $newsSQL = $newsSQL .
                    'target = "GENERAL" AND published_on >= date_sub(now(), INTERVAL lifespan_hours hour)';            
        }
        $row = $this->getData($newsSQL);
        if (!array_key_exists("Error", $row) && count($row) > 0) {
            return json_encode($row);
        } 
        return "";
    }
    
    public function changePassword($userName, $password, $newPassword) {
        $validUser = $this->authenticateUser($userName, $password);
        
        if ($validUser != "") {
            $cryptPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $retval = $this->setData('UPDATE accounts SET password = "' . $cryptPassword . '" WHERE username = "' . $userName . '"');
            if ($retval != "") {
                return '[{"error":"Change password failed. Please retry."}]';
            }
        } else {
            return '[{"error":"Current password do not match. Please retry"}]';
        }
    }
    
}
?>