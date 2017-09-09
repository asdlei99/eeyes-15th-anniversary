<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-27
 * Time: 20:04
 */

//这里的定义还是很不合理的
define('__DB_QUERY_ERROR__',-17);
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
 * 提交信息表单（这个函数已经没有用了，因为使用腾讯问卷收集的信息,改用从csv读取并插入数据库）
 * @param $info array 包含 config.php中header的所有字段
 * @return int
 */
//function submit($info){
//    $header = config('table_member_header');
//    $values = '';
//    foreach ($info as $value) {
//        $values .= '"'.$value.'",';
//    }
//    $values = rtrim($values,',');
//    $headers = '';
//    foreach ($header as $value) {
//        $headers .= '`'.$value.'`,';
//    }
//    $headers = rtrim($values,',');
//    $sql ="INSERT INTO ".config('table_member_name')." (".$headers.") VALUES (".$values.");";
//
//    $mysqli = new mysqli(config('db_host'),config('db_username'),config('db_password'),config('db_name'),config('db_port'));
//    if(mysqli_connect_errno()){
//        return __DB_CONNECT_ERROR__;
//    }
//    if (!$mysqli->$mysqli->set_charset("utf-8mb4")) {
//        return __SET_CHARSET_ERROR__;
//    }
//    if(!$mysqli->query($sql)){
//        $mysqli->close();
//        return __SUBMIT_ERROR__;
//    }else{
//        $mysqli->close();
//        return __SUBMIT_SUCCESS__;
//    }
//}

/**
 * 从数据库得到所有人的信息
 * @return int|string 失败返回错误代码，成功返回json格式的string，前端应当做JSON obj处理
 */
function get_info(){
    $sql = "SELECT * FROM ".config('table_member_name').";";
    $mysqli = new mysqli(config('db_host'),config('db_username'),config('db_password'),config('db_name'),config('db_port'));
    if(mysqli_connect_errno()){
        return __DB_CONNECT_ERROR__;
    }
    if (!$mysqli->set_charset("utf8mb4")) {
        return __SET_CHARSET_ERROR__;
    }
    if (($result = $mysqli->query($sql)) === false){
        return __DB_QUERY_ERROR__;
    }
    $result = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($result as $key => $value) {
        $result[$key]["photo"] = config('photo_path').'/'.$result[$key]["photo"];
    }
    $mysqli->close();
    return json_encode($result,JSON_UNESCAPED_UNICODE);
}

/**
 * after upload info.csv onto ftp, run this function and add info into database
 * @return int errno
 */
function update_info(){
    if(($handle = fopen(config('file'),'r')) !== false) {
        $select = "SELECT count(*) FROM `".config('table_member_name')."` WHERE `tel`=";
        $mysqli = new mysqli(config('db_host'),config('db_username'),config('db_password'),config('db_name'),config('db_port'));
        if ($mysqli->connect_error) {
            return __DB_CONNECT_ERROR__;
        }
        if (!$mysqli->set_charset("utf8mb4")) {
            $mysqli->close();
            return __SET_CHARSET_ERROR__;
        }
        $flag = true;
        while (($data = fgetcsv($handle)) !== false) {
            if ($flag === true) {//skip the first row
                $flag = false;
                continue;
            }
            if ($mysqli->query($select.'"'.$data[5].'"')->fetch_all()[0][0]){//通过手机号查重
//                echo "id:【".$data[0]."】,【".$data[4]."】已被添加<br><br>";
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
            $values = '';
            foreach ($data as $value) {
                $values .= "'".$value."',";
            }
            $values = rtrim($values,',');
            $sql = "INSERT INTO ".config('table_member_name')." VALUES (".$values.");";
            if(!$mysqli->query($sql)) {
                $mysqli->close();
                return __DB_QUERY_ERROR__;
            }
//            var_dump($data);
//            echo "<br>添加成功<br><br>";
        }
        $mysqli->close();
    }
}