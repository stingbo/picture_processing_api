DROP TABLE IF EXISTS `tiantian_position`;
CREATE TABLE `tiantian_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `province_id` bigint(20) unsigned NOT NULL COMMENT '省编码',
  `province_name` char(64) NOT NULL COMMENT '省名称',
  `city_id` bigint(20) unsigned NOT NULL COMMENT '市编码',
  `city_name` char(64) NOT NULL COMMENT '市名称',
  `county_id` bigint(20) unsigned NOT NULL COMMENT '区县编码',
  `county_name` char(64) NOT NULL COMMENT '区县名称',
  `town_id` bigint(20) unsigned NOT NULL COMMENT '乡镇编码',
  `town_name` char(64) NOT NULL COMMENT '乡镇名称',
  `village_id` bigint(20) unsigned NOT NULL COMMENT '村街道编码',
  `village_name` char(64) NOT NULL COMMENT '村街道名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `village_id` (`village_id`),
  KEY `city_id` (`city_id`),
  KEY `county_id` (`county_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='省市县镇村数据';
