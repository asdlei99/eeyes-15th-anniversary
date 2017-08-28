<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-27
 * Time: 21:07
 */
require 'common.php';

$name = isset($_GET['name'])?$_GET['name']:$_POST['name'];
if($name == 'upload'){
    if(!isset($_POST['data'])){
        echo __NO_BASE64_DATA_ERROR__;
        die;
    }else{
//        exit(scrawl_upload_base64($_POST['data']));
        echo scrawl_upload_base64($_POST['data']);
        die;
    }
}elseif($name == 'download'){
//    exit(scrawl_download());
    echo scrawl_download();
    die;
}elseif($name == 'submit'){
    $info =[];
    foreach (config('person_t_header') as $key){
        $info[] = $_POST[$key];
    }
//    exit(submit($info));
    echo submit($info);
    die;
}elseif($name == 'get_info'){
//    exit(get_info());
    echo get_info();
    die;
}else{
//    exit(__NO_NAME_ERROR__);
    echo __NO_NAME_ERROR__;
    die;
}

