<?php

class Utils {
    //Data should be in json encoded format, with the format "id" => , "data" =>
    function buildSelectList($data, $id_col, $name_col, $default) {
        $result = json_decode($data, TRUE);
        
        $list = "";
        foreach($result as $val) {
            $list = $list . "<OPTION VALUE='" . intval($val[$id_col]) . "' ";
            if (intval($default) === intval($val[$id_col])) {
                $list = $list . "SELECTED";
            }
            $list = $list . ">" . $val[$name_col] . "</OPTION>";
        }
        return $list;
    }
    
    function sendEmail($emailTo, $subjectText, $messageBody) {
        $headers = "From: Edgeband Search\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html;charset=UTF-8\r\n";

        mail($emailTo, $subjectText, $messageBody, $headers);
    }
}
?>
