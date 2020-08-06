<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header("Access-Control-Allow-Origin: *");

include 'constants.php';

/*FOR EXCEL UPLOAD*/
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

// Include Spout library
require_once 'spout/src/Spout/Autoloader/autoload.php';

class MySQLManager
{
    var $conn;

    public function __construct()
    {
        $this->conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
        if (!$this->conn) {
            die('Could not connect: ' . mysqli_error());
        }
        mysqli_select_db($this->conn, DB_NAME);

    }

    function select($tableName,$conditions)
    {
        $tableName = mysqli_real_escape_string($this->conn,$tableName);
        $query = "SELECT * FROM $tableName";
        if(is_array($conditions))
        {
            $query .= " WHERE";
            foreach($conditions as $key => $value)
            {
                $key = mysqli_real_escape_string($this->conn,$key);
                $query .= " $key = ";
                if(is_int($value))
                    $query .= "$value AND";
                else
                {
                    $value = mysqli_real_escape_string($this->conn,$value);
                    $query .= "'$value' AND";
                }
            }
        }

        $query = rtrim($query, "AND");
                $result = mysqli_query($this->conn,$query);

        $record = array();
        if($result == false)
        {
            return false;
        }
        else
        {
            if (mysqli_num_rows($result) > 0)
            {
                $i = 0;
                while($row = mysqli_fetch_assoc($result))
                {
                    $record[$i] = $row;
                    $i++;
                }
            }

            return $record;
        }
    }

    function insert($tableName,$insertParameters)
    {
        $tableName = mysqli_real_escape_string($this->conn,$tableName);
        $query = "INSERT INTO $tableName (";
        if(is_array($insertParameters))
        {
            foreach ($insertParameters as $key => $value)
            {
                $key = mysqli_real_escape_string($this->conn, $key);
                $query .= "$key ,";
            }
            $query = rtrim($query, ",");
            $query .= ") VALUES (";

            foreach ($insertParameters as $value)
            {
                if (is_int($value))
                    $query .= "$value ,";
                else
                {
                    $value = mysqli_real_escape_string($this->conn, $value);
                    $query .= "'$value' ,";
                }
            }
            $query = rtrim($query, ",");
            $query .= ")";
        }

        $result = mysqli_query($this->conn,$query);
        $result = mysqli_query($this->conn, "SELECT LAST_INSERT_ID()");
        if($result == false)
            return false;
        else
            return mysqli_fetch_assoc($result)["LAST_INSERT_ID()"];
    }

    function update($tableName,$updateParameters,$conditions)
    {
        $tableName = mysqli_real_escape_string($this->conn,$tableName);
        $query = "UPDATE $tableName SET";
        if(is_array($updateParameters))
        {
            foreach ($updateParameters as $key => $value)
            {
                $key = mysqli_real_escape_string($this->conn,$key);
                $query .= " $key = ";
                if(is_int($value))
                    $query .= "$value ,";
                else
                {
                    $value = mysqli_real_escape_string($this->conn,$value);
                    $query .= "'$value' ,";
                }
            }
            $query = rtrim($query, ",");
            $query .= " WHERE";

            foreach($conditions as $key => $value)
            {
                $key = mysqli_real_escape_string($this->conn,$key);
                $query .= " $key = ";
                if(is_int($value))
                    $query .= "$value AND";
                else
                {
                    $value = mysqli_real_escape_string($this->conn,$value);
                    $query .= "'$value' AND";
                }
            }
            $query = rtrim($query, "AND");
        }

        $result = mysqli_query($this->conn,$query);

        if($result == false)
            return false;
        else
            return true;

    }

    function delete($tableName,$conditions)
    {
        $tableName = mysqli_real_escape_string($this->conn,$tableName);
        $query = "DELETE FROM $tableName";
        if(is_array($conditions))
        {
            $query .= " WHERE";
            foreach($conditions as $key => $value)
            {
                $key = mysqli_real_escape_string($this->conn,$key);
                $query .= " $key = ";
                if(is_int($value))
                    $query .= "$value AND";
                else
                {
                    $value = mysqli_real_escape_string($this->conn,$value);
                    $query .= "'$value' AND";
                }
            }
        }

        $query = rtrim($query, "AND");

        $result = mysqli_query($this->conn,$query);

        if($result == false)
            return false;
        else
            return true;
    }

    function customQuery($customQuery)
    {
        $result = mysqli_query($this->conn, $customQuery);

        $record = array();
        if($result == false)
            echo "false";
        else
        {
            if (mysqli_num_rows($result) > 0)
            {
                $i = 0;
                while($row = mysqli_fetch_assoc($result))
                {
                    $record[$i] = $row;
                    $i++;
                }
            }

            return $record;
        }
    }

    function getLastRecordId($tableName)
    {
        $tableName = mysqli_real_escape_string($this->conn, $tableName);
        $query = "SELECT * FROM $tableName";

        $query = rtrim($query, "AND");

        $result = mysqli_query($this->conn, $query);

        $record = array();
        if ($result == false) {
            return false;
        } else {
            if (mysqli_num_rows($result) > 0) {
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $record[$i] = $row;
                    $i++;
                }
            }

            return $record;
        }
    }

    function characterToHTMLEntity($str)
    {
        $search  = array('&amp;', '&lt;', '&gt;', '&euro;', '&lsquo;', '&rsquo;', '&ldquo;','&rdquo;', '&ndash;', '&mdash;', '&iexcl;','&cent;', '&pound;', '&curren;', '&yen;', '&brvbar;', '&sect;', '&uml;', '&copy;', '&ordf;', '&laquo;', '&not;', '&reg;', '&macr;', '&deg;', '&plusmn;', '&sup2;', '&sup3;', '&acute;', '&micro;', '&para;', '&middot;', '&cedil;', '&sup1;', '&ordm;', '&raquo;', '&frac14;', '&frac12;', '&frac34;', '&iquest;', '&Agrave;', '&Aacute;', '&Acirc;', '&Atilde;', '&Auml;', '&Aring;', '&AElig;', '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;', '&Euml;', '&Igrave;', '&Iacute;', '&Icirc;', '&Iuml;', '&ETH;', '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;', '&Otilde;', '&Ouml;', '&times;', '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;', '&Uuml;', '&Yacute;', '&THORN;', '&szlig;', '&agrave;', '&aacute;', '&acirc;', '&atilde;', '&auml;', '&aring;', '&aelig;', '&ccedil;', '&egrave;', '&eacute;','&ecirc;', '&euml;', '&igrave;', '&iacute;', '&icirc;', '&iuml;', '&eth;', '&ntilde;', '&ograve;', '&oacute;', '&ocirc;', '&otilde;', '&ouml;', '&divide;','&oslash;', '&ugrave;', '&uacute;', '&ucirc;', '&uuml;', '&yacute;', '&thorn;', '&yuml;', '&OElig;', '&oelig;', '&sbquo;', '&bdquo;', '&hellip;', '&trade;', '&bull;', '&asymp;');
        $replace = array('&', '<', '>', '€', '‘', '’', '“', '”', '–', '—', '¡', '¢','£', '¤', '¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '®', '¯', '°', '±', '²', '³', '´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', '×', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã','ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', '÷', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'þ', 'ÿ','Œ', 'œ', '‚', '„', '…', '™', '•', '˜');

        //REPLACE VALUES
        $str = str_replace($search, $replace, $str);

        //RETURN FORMATED STRING
        return $str;
    }

    function error_log( $error , $file_name )
    {
        file_put_contents( $file_name, $error, FILE_APPEND);
    }

    function upload_file($files, $target) {
        global $res;
        $file_name = $files['name'];
        $target = $target . basename($file_name);
        if (!move_uploaded_file($files['tmp_name'], $target)) {
            $res = array(['res_code' =>0]);
        } else {
            $res = array(['res_code' =>1, 'file_name' => $file_name]);
        }

        return $res;
    }

    function send_mail($send_to, $subject, $message)
    {
//        require_once('Mailer/PHPMailerAutoload.php');
        require_once('Mailer/class.phpmailer.php');
        require_once('Mailer/class.smtp.php');
        global $res;
        $mail = new PHPMailer(true);
        try {
//            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->SMTPDebug = 1;
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->Port = MAIL_PORT;

            //Recipients
            $mail->setFrom(SET_FROM_EMAIL, SET_FROM_NAME);
            $mail->AddAddress($send_to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            $res = array(['res_code' => 1, 'message' => 'Message sent Successfully.']);
        } catch (phpmailerException $e) {
            $res = array(['res_code' => 0, 'message' => $e->errorMessage()]);
        }
        return $res;
    }

    function excel_upload($files){
        global $headers, $result, $data;

        $file_name = $files['name'];
        echo $files;
        

        /*$reader = ReaderFactory::create(Type::XLSX);
        $reader->open('List of Projects.xlsx');
        $count = 1;

        foreach ($reader->getSheetIterator() as $sheet) {

            // Number of Rows in Excel sheet
            foreach ($sheet->getRowIterator() as $row) {

                // It reads data after header. In the my excel sheet,
                // header is in the first row.
                if ($count > 1) {

                    // Data of excel sheet
                    $i=0;
                    foreach ($headers as $singleHeader) {
                        $data[$singleHeader] = $row[$i];
                        $i++;
                    }
                    $result[] = $data;

                }
                else {
                    $headers = $row;

                }
                $count++;
            }
        }

        echo json_encode($result);
    }*/
}

