<?php
/**
 * Created by PhpStorm.
 * User: Cantjie
 * Date: 2017-8-26
 * Time: 11:09
 */

return [

    //数据库配置
    'db_username'         => '15th-2017-09',
    'db_password'         => '',
    'db_port'             => '3306',
    'db_host'             => '127.0.0.1',
    'db_name'             => '15th-2017-09',
    'table_member_name'   => 'member',
    'table_member_header' => ['id', 'name', 'tel', 'email', 'join_year', 'department_in_eeyes', 'graduate_year', 'current_post', 'current_company', 'github', 'photo'],

    //涂鸦配置
    'scrawl_path'         => 'upload/scrawl',
    'scrawl_suffix'       => '.png',

    //信息文件
    'file'                => 'info.csv',
    'photo_path'          => 'upload/photo',
];