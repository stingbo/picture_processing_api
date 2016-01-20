DROP TABLE IF EXISTS `tiantian_basic_idcard`;
CREATE TABLE `tiantian_basic_idcard` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(8) NOT NULL COMMENT '姓名',
  `idcard_no` VARCHAR(20) NOT NULL COMMENT '身份证号码',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='身份证系统基础表';
