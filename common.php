<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-27
 * Time: 20:04
 */

// 这里的定义还是很不合理的
define('__FILE_NOT_FOUND__', -18);
define('__METHOD_NOT_ALLOWED__', -18);
define('__DB_QUERY_ERROR__', -17);
define('__DB_CONNECT_ERROR__', -16);
define('__SUBMIT_ERROR__', -15);
define('__SET_CHARSET_ERROR__', -14);
define('__NO_BASE64_DATA_ERROR__', -13);
define('__NO_NAME_ERROR__', -12);
define('__UPLOAD_ERROR__', -11);

define('__UPLOAD_SUCCESS__', -1);
define('__SUBMIT_SUCCESS__', -2);
define('__UPDATE_SUCCESS__', -3);

/**
 * 获取配置文件
 *
 * @param string $key
 *
 * @return mixed
 */
function config($key)
{
    static $config = null;
    if (is_null($config)) {
        $config = include __DIR__ . '/config.php';
    }
    return $config[$key];
}

/**
 * 判断$haystack是否以$needle开头
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function str_starts_with($haystack, $needle)
{
    return 0 === strpos($haystack, $needle);
}

/**
 * @param $files array $_FILES
 *
 * @return string >0上传失败 -1上传成功
 */
function scrawl_upload($files)
{
    $img_path = config('scrawl_path');
    $upload_path = BASE_PATH . DIRECTORY_SEPARATOR . $img_path;
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    if ($files['imgFile']['error'] > 0) {
        return $files['imgFile']['error']; // 上传失败
    }
    if (!move_uploaded_file($files['imgFile']['tmp_name'], $upload_path . DIRECTORY_SEPARATOR . uniqid(mt_rand(), true) . config('scrawl_suffix'))) {
        return '0'; // 上传失败
    } else {
        return __UPLOAD_SUCCESS__; // 上传成功
    }
}

/**
 * @return string json格式的string，前端应当做JSON obj处理
 */
function scrawl_download()
{
    $img_path = config('scrawl_path');
    $upload_path = BASE_PATH . DIRECTORY_SEPARATOR . $img_path;
    $files = glob($upload_path . DIRECTORY_SEPARATOR . '*' . config('scrawl_suffix'));
    $result = [];
    foreach ($files as $file) {
        $result[] = $img_path . DIRECTORY_SEPARATOR . basename($file);
    }
    return json_encode($result);
}

/**
 * 通过base64，存成一个文件。
 *
 * @param $data
 *
 * @return int
 */
function scrawl_upload_base64($data)
{
    if (str_starts_with($data, 'data')) {
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
    }
    $data = base64_decode($data);
    $img_path = config('scrawl_path');
    $upload_path = BASE_PATH . DIRECTORY_SEPARATOR . $img_path;
    if (!file_put_contents($upload_path . DIRECTORY_SEPARATOR . $_SERVER['REQUEST_TIME'] . uniqid(rand()) . config('scrawl_suffix'), $data)) {
        return __UPLOAD_ERROR__;
    } else {
        return __UPLOAD_SUCCESS__;
    }
}

/**
 * 从数据库得到所有人的信息
 *
 * @return int|string 失败返回错误代码，成功返回json格式的string，前端应当做JSON obj处理
 */
function get_info()
{
    $sql = "SELECT * FROM " . config('table_member_name') . ";";
    $mysqli = new mysqli(config('db_host'), config('db_username'), config('db_password'), config('db_name'), config('db_port'));
    if (mysqli_connect_errno()) {
        return __DB_CONNECT_ERROR__;
    }
    if (!$mysqli->set_charset("utf8mb4")) {
        return __SET_CHARSET_ERROR__;
    }
    if (($result = $mysqli->query($sql)) === false) {
        $result->close();
        $mysqli->close();
        return __DB_QUERY_ERROR__;
    }
    $records = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($records as $key => $value) {
        $records[$key]["photo"] = config('photo_path') . DIRECTORY_SEPARATOR . $records[$key]["photo"];
    }
    $result->close();
    $mysqli->close();
    return json_encode($records);
}

/**
 * after upload info.csv onto ftp, run this function and add info into database
 *
 * @return int errno
 */
function update_info()
{
    if (($handle = fopen(config('file'), 'r')) !== false) {
        $select = "SELECT count(*) FROM `" . config('table_member_name') . "` WHERE `tel`=";
        $mysqli = new mysqli(config('db_host'), config('db_username'), config('db_password'), config('db_name'), config('db_port'));
        if ($mysqli->connect_error) {
            return __DB_CONNECT_ERROR__;
        }
        if (!$mysqli->set_charset("utf8mb4")) {
            $mysqli->close();
            return __SET_CHARSET_ERROR__;
        }
        $flag = true;
        while (($data = fgetcsv($handle)) !== false) {
            if ($flag === true) { // skip the first row
                $flag = false;
                continue;
            }
            if ($mysqli->query($select . '"' . $data[5] . '"')->fetch_all()[0][0]) { // 通过手机号查重
                continue;
            }

            $useless = [1, 2, 3, 14, 15];
            foreach ($useless as $index) {
                unset($data[$index]);
            }
            if ($data[13] !== '') {
                preg_match('#&file_name=(.*?)&#', $data[13], $matches);
                $data[13] = $matches[1];
            } else {
                $data[13] = 'default.jpg';
            }
            $data = array_values($data);
            $values = "'" . implode("','", $data) . "'";
            $sql = "INSERT INTO " . config('table_member_name') . " VALUES (" . $values . ");";
            if (!$mysqli->query($sql)) {
                $mysqli->close();
                return __DB_QUERY_ERROR__;
            }
        }
        $mysqli->close();
        return __UPDATE_SUCCESS__;
    } else {
        return __FILE_NOT_FOUND__;
    }
}