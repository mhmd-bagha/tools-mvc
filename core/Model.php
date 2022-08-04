<?php

class Model
{

    public static $conn = null;
    private static $EncryptionMethod = "AES-256-CBC";
    private static $SecretHash = "SecretHash";
    private static $SecretKey = "SecretKey";
    private static $SecretIv = "SecretIv";

    function __construct()
    {
        $servername = SERVERDB;
        $username = USERNAMEDB;
        $password = PASSWORDDB;
        $dbname = DBNAMEDB;
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        try {
            self::$conn = new PDO('mysql:host=' . $servername . ';dbname=' . $dbname, $username, $password, $options);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $err) {
            PhpError($err->getCode(), $err->getMessage(), $err->getFile(), $err->getLine());
        }

    }

    function Select($sql, $values = [], $fetch = 'fetchAll', $fetchStyle = PDO::FETCH_OBJ, $rowCount = false)
    {

        $query = self::$conn->prepare($sql);
        foreach ($values as $key => $value)
            $query->bindValue($key + 1, $value);
        $query->execute();
        if ($fetch == 'fetchAll')
            if ($rowCount != true)
                $result = $query->fetchAll($fetchStyle);
            else
                $result = $query->rowCount();
        else
            if ($rowCount != true)
                $result = $query->fetch($fetchStyle);
            else
                $result = $query->rowCount();
        return $result;
    }

    function Query($sql, $values = [])
    {
        $query = self::$conn->prepare($sql);
        foreach ($values as $key => $value)
            $query->bindValue($key + 1, $value);
        $query->execute();
        return ($query) ? true : false;
    }

    function thumbnail($file, $pathToSave, $w, $h = '', $crop = false)
    {

        $new_height = $h;

        list($width, $height) = getimagesize($file);

        $r = $width / $height;

        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }

        $what = getimagesize($file);

        switch (strtolower($what['mime'])) {
            case 'image/png':
                $src = imagecreatefrompng($file);

                break;
            case 'image/jpeg':
                $src = imagecreatefromjpeg($file);
                break;
            case 'image/gif':
                $src = imagecreatefromgif($file);
                break;
            default:
                //die();
        }

        if ($new_height != '') {
            $newheight = $new_height;
        }

        $dst = imagecreatetruecolor($newwidth, $newheight);//the new image
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);//az function

        imagejpeg($dst, $pathToSave, 95);//pish farz in tabe 75 darsad quality ast

        return $dst;


    }

    function security($value)
    {
        $lev = trim($value);
        $lev2 = htmlentities($lev);
        $lev3 = htmlspecialchars($lev2);
        $lev4 = strip_tags($lev3);
        return $lev4;
    }

    public static function redirect($URI, $back = null)
    {
        if ($back === null) {
            @header("Location: " . $URI);
            echo "<meta http-equiv='refresh' content='0; url={$URI}' />";
            echo "<script>window.location.href = '{$URI}';</script>";
        } else {
            $UrIBack = $URI . '?' . $back;
            @header("Location: " . $UrIBack);
            echo "<meta http-equiv='refresh' content='0; url={$UrIBack}' />";
            echo "<script>window.location.href = '{$UrIBack}';</script>";
        }
    }

    public function buildNum($table_name, $columns_name, $build_num, $type_encrypt = "encrypt")
    {
        $model = new Model();
        switch ($type_encrypt) {
            case "encrypt":
                $build_num = $model->encrypt($build_num);
                break;
            case "md5":
                $build_num = md5($build_num);
                break;
        }
        $query = $model->Select("SELECT `{$columns_name}` FROM `{$table_name}` WHERE `{$columns_name}` = ?", [$build_num], 'fetch', PDO::FETCH_OBJ, true);
        if ($query > 0) {
            self::buildNum($table_name, $columns_name);
        } else {
            return $build_num;
        }
    }

    public function encrypt($value)
    {
        $key = hash('sha256', self::$SecretKey);
        $iv = substr(hash('sha256', self::$SecretIv), 0, 16);
        $output = base64_encode(openssl_encrypt($value, self::$EncryptionMethod, $key, 0, $iv));
        return $output;

    }

    public function decrypt($value)
    {
        $key = hash('sha256', self::$SecretKey);
        $iv = substr(hash('sha256', self::$SecretIv), 0, 16);
        $decrypt = openssl_decrypt(base64_decode($value), self::$EncryptionMethod, $key, 0, $iv);
        return $decrypt;
    }
}


class helper
{
    private $url;
    private $api_key;
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    /**
     * list of errors
     *
     * @var array
     */
    private $errors = array();

    /**
     * @param string $webserviceUrl
     * @param string $apiKey
     */
    public function __construct($webserviceUrl)
    {
        $this->url = $webserviceUrl;
        $this->api_key = 'F4960daa89D73A33332382fE661E7a18';
    }

    public function getPrices($des_city, $price, $weight, $buy_type, $delivery_type)
    {
        $params = array(
            'des_city' => $des_city,
            'price' => $price,
            'weight' => $weight,
            'buy_type' => $buy_type,
            'send_type' => $delivery_type
        );
        return $this->call('order/getPrices.json', $params);
    }


    private function call($url, $params, $methodType = helper::METHOD_POST)
    {
        // flush error list
        $this->errors = array();
        if (stripos($url, 'http://') === false)
            $url = $this->url . $url;
        $params['api'] = $this->api_key;
        $data = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, $methodType === helper::METHOD_POST);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        $result = json_decode($result, true);
        if (json_last_error() == JSON_ERROR_NONE)
            return $this->parseResponse($result);
        throw new FrotelResponseException('Failed to Parse Response (' . json_last_error() . ')');
    }

    /**
     * parse webservice response
     *
     * @param array $response
     * @return bool
     * @throws FrotelResponseException
     * @throws FrotelWebserviceException
     */
    private function parseResponse($response)
    {
        if (!isset($response['code'], $response['message'], $response['result']))
            throw new FrotelResponseException('پاسخ دریافتی از سرور معتبر نیست.');
        if ($response['code'] == 0)
            return $response['result'];
        $this->errors[] = $response['message'];
        throw new FrotelWebserviceException($response['message']);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

class FrotelResponseException extends Exception
{
}

class FrotelWebserviceException extends Exception
{
}


?>










