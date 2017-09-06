<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-27
 * Time: 20:04
 */

//这里的定义还是很不合理的
define('__DB_CONNECT_ERROR__',-16);
define('__SUBMIT_ERROR__',-15);
define('__SET_CHARSET_ERROR__',-14);
define('__NO_BASE64_DATA_ERROR__',-13);
define('__NO_NAME_ERROR__',-12);
define('__UPLOAD_ERROR__',-11);
define('__UPLOAD_SUCCESS__',-1);
define('__SUBMIT_SUCCESS__',-2);

/**
 * @param $key
 * @return mixed
 */
function config($key){
    $config = include 'config.php';
    return $config[$key];
}

/**
 * return weather string $tar starts with $query
 * @param $tar string
 * @param $query string
 * @return bool
 */
function str_starts_with($tar, $query){
    return substr($tar,0,strlen($query)) === $query;
}

/**
 * @param $files array $_FILES
 * @return string >0上传失败 -1上传成功
 */
function scrawl_upload($files){
    $img_path = config('scrawl_path');
    $upload_path = dirname(dirname(__FILE__)).'\\'.$img_path;
    if(!is_dir($upload_path)){
        mkdir($upload_path);
    }
    if($files['imgFile']['error'] > 0){
        return $files['imgFile']['error'];//上传失败
    }
    if(!move_uploaded_file($files['imgFile']['tmp_name'],$upload_path.'\\'.uniqid(rand(),true).config('scrawl_suffix'))){
        return '0';//上传失败
    }else{
        return __UPLOAD_SUCCESS__;//上传成功
    }
}

/**
 * @return string json格式的string，前端应当做JSON obj处理
 */
function scrawl_download(){
    $img_path = config('scrawl_path');
    $upload_path = dirname(dirname(__FILE__)).'\\'.config('scrawl_path');
    $files = glob($upload_path.'\\*'.config('scrawl_suffix'));
    for($i = 0;$i<count($files);$i++ ){
        $files[$i] =$img_path.'/'.basename($files[$i]);
    }
    return json_encode($files);
}

/**
 * 通过base64，存成一个文件。
 * @param $data
 * @return int
 */
function scrawl_upload_base64($data){
    if(str_starts_with($data,'data')){
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
    }
    $data = base64_decode($data);
    $upload_path = dirname(dirname(__FILE__)).'\\'.config('scrawl_path');
    if(!file_put_contents($upload_path.'\\'.time().uniqid(rand()).config('scrawl_suffix'),$data)){
        return __UPLOAD_ERROR__;
    }else{
        return __UPLOAD_SUCCESS__;
    }
}

/**
 * 提交信息表单
 * @param $info array 包含 config.php中header的所有字段
 * @return int
 */
function submit($info){
    $header = config('person_t_header');
    $values = '';
    foreach ($info as $value) {
        $values .= '"'.$value.'",';
    }
    $values = rtrim($values,',');
    $headers = '';
    foreach ($header as $value) {
        $headers .= '`'.$value.'`,';
    }
    $headers = rtrim($values,',');
    $sql ="INSERT INTO ".config('db_person_t')." ".$headers.' VALUES ('.$values.');';

    $mysqli = new mysqli(config('db_host'),config('db_username'),config('db_password'),config('db_name'),config('db_port'));
    if(mysqli_connect_errno()){
        return __DB_CONNECT_ERROR__;
    }
    if (!$mysqli->$mysqli->set_charset("utf-8mb4")) {
        return __SET_CHARSET_ERROR__;
    }
    if(!$mysqli->query($sql)){
        $mysqli->close();
        return __SUBMIT_ERROR__;
    }else{
        $mysqli->close();
        return __SUBMIT_SUCCESS__;
    }
}

/**
 * 从数据库得到所有人的信息
 * @return int|string 失败返回错误代码，成功返回json格式的string，前端应当做JSON obj处理
 */
function get_info(){
    $sql = "SELECT * FROM ".config('db_name').';';
    $mysqli = new mysqli(config('db_host'),config('db_username'),config('db_password'),config('db_name'),config('db_port'));
    if(mysqli_connect_errno()){
        return __DB_CONNECT_ERROR__;
    }
    if (!$mysqli->$mysqli->set_charset("utf-8mb4")) {
        return __SET_CHARSET_ERROR__;
    }
    $result = $mysqli->query($sql);
    $result->fetch_all(MYSQLI_ASSOC);
    $mysqli->close();
    return json_encode($result);
}