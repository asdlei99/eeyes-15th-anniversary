-- 姓名、照片、本科毕业年份、现就职公司及职务、曾就职公司、 （曾）属e瞳部门、手机号、邮箱、寄语……
-- @link:http://blog.csdn.net/shelldawn/article/details/71170543
CREATE TABLE IF NOT EXISTS `person_t` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '编号',
    `name` VARCHAR(10) NOT NULL COMMENT '姓名',
    `photo` VARCHAR(100) COMMENT '存储照片的文件名',
    `graduate_year` VARCHAR(4) NOT NULL COMMENT '毕业年份',
    `current_company` VARCHAR(100) COMMENT '现就职公司及职务',
    `past_company` VARCHAR(100) COMMENT '曾就职公司(及职务)',
    `department_in_eeyes` VARCHAR(10) COMMENT '(曾)属e曈部门',
    `tel` VARCHAR(13) COMMENT '手机号',
    `email` VARCHAR(40) COMMENT '邮箱',
    `word` VARCHAR(300) COMMENT '寄语',
    `github` VARCHAR(80) COMMENT 'github地址')CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;