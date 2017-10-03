<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-27
 * Time: 21:07
 */

define('BASE_PATH', dirname(dirname(get_included_files()[0])));

require 'common.php';

switch ($_GET['name']) {
    case 'upload':
        /**
         * 图片上传
         */
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            http_response_code(405);
            echo __METHOD_NOT_ALLOWED__;
        } elseif (!isset($_POST['data'])) {
            echo __NO_BASE64_DATA_ERROR__;
        } else {
            echo scrawl_upload_base64($_POST['data']);
        }
        break;
    case 'download':
        /**
         * 获取图片列表
         */
        header('Content-Type: application/json; charset=utf-8');
        echo scrawl_download();
        break;
    case 'get_info':
        /**
         * 获取成员信息列表
         */
        header('Content-Type: application/json; charset=utf-8');
        echo get_info();
        break;
    case 'update_info':
        /**
         * 根据info.csv更新数据库中成员信息
         */
        echo update_info();
        break;
    default:
        echo __NO_NAME_ERROR__;
}

