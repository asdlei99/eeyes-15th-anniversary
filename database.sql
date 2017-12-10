CREATE TABLE IF NOT EXISTS `member` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '编号',
  `name` VARCHAR(10) NOT NULL COMMENT '姓名',
  `tel` VARCHAR(15) COMMENT '手机号',
  `email` VARCHAR(40) COMMENT '邮箱',
  `join_year` VARCHAR(4) NOT NULL COMMENT '加入e曈的年份',
  `department_in_eeyes` VARCHAR(10) COMMENT '(曾)属e曈部门',
  `graduate_year` VARCHAR(4) NOT NULL COMMENT '毕业年份',
  `current_post` VARCHAR(100) COMMENT '现在的职务',
  `current_company` VARCHAR(100) COMMENT '现就职公司',
  `github` VARCHAR(80) COMMENT 'github地址',
  `photo` VARCHAR(100) COMMENT '存储照片的文件名'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;