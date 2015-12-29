DROP TABLE IF EXISTS `tiantian_idcard`;
CREATE TABLE `tiantian_idcard` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` INT(11) UNSIGNED NOT NULL COMMENT '账号日志表(account_log)对应的id',
  `identify_id` INT(11) UNSIGNED NOT NULL COMMENT '订单日志表(pay_log)对应的id',
  `openid` VARCHAR(50) DEFAULT '0' COMMENT '微信用户openid',
  `out_trade_no` VARCHAR(50) DEFAULT '0' COMMENT '商户订单号',
  `total_fee` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  `trade_type` VARCHAR(32) DEFAULT '' COMMENT '交易类型,取值如下：JSAPI，NATIVE，APP',
  `bank_type` VARCHAR(32) DEFAULT '' COMMENT '支付类型,信用卡还是银行卡',
  `is_subscribe` TINYINT(1) DEFAULT '-1' COMMENT '是否关注, 0未关注，1已关注',
  `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信支付日志表';
