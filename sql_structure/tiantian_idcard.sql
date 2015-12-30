DROP TABLE IF EXISTS `tiantian_idcard`;
CREATE TABLE `tiantian_idcard` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(8) NOT NULL COMMENT '姓名',
  `idcard_no` VARCHAR(32) NOT NULL COMMENT '身份证号码',
  `province` VARCHAR(12) DEFAULT '' COMMENT '省',
  `city` VARCHAR(12) DEFAULT '' COMMENT '城市',
  `country` VARCHAR(12) DEFAULT '' COMMENT '区县',
  `detail_address` VARCHAR(64) DEFAULT '' COMMENT '区县下的详细地址',
  `idcard_front_img` VARCHAR(256) DEFAULT '' COMMENT '身份证正面图片',
  `idcard_back_img` VARCHAR(256) DEFAULT '' COMMENT '身份证反面图片',
  `idcard_both_img` VARCHAR(256) DEFAULT '' COMMENT '身份证正反合一图片',
  `is_trully` TINYINT(1) DEFAULT '-1' COMMENT '是否真实, 0非真实，1真实',
  `is_validate` TINYINT(1) DEFAULT '-1' COMMENT '是否验证, 0未验证，1已验证',
  `is_softdel` TINYINT(1) DEFAULT '-1' COMMENT '是否逻辑删除, 0未删除，1已删除',
  `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信支付日志表';


INSERT INTO `tiantian_idcard` (`id`, `name`, `idcard_no`, `province`, `city`, `country`, `detail_address`, `idcard_front_img`, `idcard_back_img`, `idcard_both_img`, `is_trully`, `is_validate`, `is_softdel`, `created_at`) VALUES
    (1, '万连波', '420923199105214914', '湖北', '孝感', '云梦','周万7组', 'alskdfjlaksfjl', 'alskdfjlaksfjl', 'alskdfjlaksfjl', '1', '1', '0', '2015-12-02 08:39:13');
