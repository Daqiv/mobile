<?php
/**
 * 入口文件
 * Some rights reserved：www.thinkcmf.com
 */

//header('Location:http://g.7gu.cn/index.php?g=api&m=Mobile&a=phonemobile');
 header("html/text");
 echo file_get_contents("http://g.7gu.cn/index.php?g=api&m=Mobile&a=phonemobile");
 exit();

?>
