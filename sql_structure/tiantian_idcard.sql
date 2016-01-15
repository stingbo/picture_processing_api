DROP TABLE IF EXISTS `tiantian_idcard`;
CREATE TABLE `tiantian_idcard` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(8) NOT NULL COMMENT '姓名',
  `gender` TINYINT(1) DEFAULT '-1' COMMENT '性别, 0女，1男',
  `nation` VARCHAR(8) NOT NULL COMMENT '民族',
  `birthday` VARCHAR(12) NOT NULL COMMENT '生日',
  `idcard_no` VARCHAR(20) NOT NULL COMMENT '身份证号码',
  `province` VARCHAR(12) DEFAULT '' COMMENT '省',
  `city` VARCHAR(12) DEFAULT '' COMMENT '城市',
  `country` VARCHAR(12) DEFAULT '' COMMENT '区县',
  `detail_address` VARCHAR(64) DEFAULT '' COMMENT '区县下的详细地址',
  `full_address` VARCHAR(128) DEFAULT '' COMMENT '地址全名',
  `issuing_authority` VARCHAR(24) DEFAULT '' COMMENT '签发机关',
  `expired_start` VARCHAR(12) DEFAULT '' COMMENT '身份证有效期开始日期',
  `expired_end` VARCHAR(12) DEFAULT '' COMMENT '身份证有效期结束日期',
  `idcard_front_img` VARCHAR(256) DEFAULT '' COMMENT '用户上传的身份证正面图片',
  `idcard_back_img` VARCHAR(256) DEFAULT '' COMMENT '用户上传的身份证反面图片',
  `idcard_both_img` VARCHAR(256) DEFAULT '' COMMENT '身份证正反合一图片',
  `idcard_front_img_false` VARCHAR(256) DEFAULT '' COMMENT '制作的身份证正面图片',
  `idcard_back_img_false` VARCHAR(256) DEFAULT '' COMMENT '制作的身份证反面图片',
  `idcard_both_img_false` VARCHAR(256) DEFAULT '' COMMENT '制作的身份证正反合一图片',
  `is_portion_validate` TINYINT(1) DEFAULT '0' COMMENT '是否部分验证(姓名和身份证号), 0未验证，1已验证',
  `is_whole_validate` TINYINT(1) DEFAULT '0' COMMENT '是否全部验证, 0未验证，1已验证',
  `is_softdel` TINYINT(1) DEFAULT '0' COMMENT '是否逻辑删除, 0未删除，1已删除',
  `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='身份证系统表';


INSERT INTO `tiantian_idcard` (`id`, `name`, `idcard_no`, `province`, `city`, `country`, `detail_address`, `idcard_front_img`, `idcard_back_img`, `idcard_both_img`, `is_trully`, `is_validate`, `is_softdel`, `created_at`) VALUES
    (1, '万连波', '420923199105214914', '湖北', '孝感', '云梦','周万7组', 'alskdfjlaksfjl', 'alskdfjlaksfjl', 'alskdfjlaksfjl', '1', '1', '0', '2015-12-02 08:39:13');
