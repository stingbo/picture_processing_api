# 系统简介
使用Slimframework,Eloquent,imagemagick+php imagick extension构建的一个用于处理图片的接口模块

--public 入口目录 <br>
----index.php 入口文件,采用openssl非对称加密算法进行验证<br>
--controller 控制器目录<br>
--models 对应控制器的models<br>
--common 公共model<br>
--logs 日志记录目录,可能需要修改权限<br>
--config 配置文件目录<br>
----database-example.php 数据库配置示例<br>
--assets 资源目录<br>
----cert openssl非对称加密验证的公钥存放目录,公钥文件名称与客户端请求时header头里传入的HTTP_CLIENT参数一致,验证时会根据名称到此目录下来查找对应的公钥<br>
----fonts 给图片加文字用到的字体<br>
----images 合成图片所用到的背景图<br>
----uploads 通过接口上传的图片<br>
--sql_structure 此系统用到的数据表结构(只适用于此模块)<br>
--vendor Slimframework和Eloquent等库<br>
--README.md 本说明<br>

注意:
服务器需要安装imagemagick图片处理程序;<br>
需要安装php扩展imagick;<br>
