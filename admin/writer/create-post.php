<?php
	include "../config.php";// 网站配置文件
	include "functions.php";// 网站函数文件
	$postname=strtolower($_GET["postname"]);
	$postname = "../../".$contentDir.$postname.".md";
	$i=1;
	while(file_exists($postname)){
		$postname = "../../".$contentDir.$_GET["postname"]."-".$i.".md";
		$i++;
	}
	$content="";
	foreach ($postInfoKeywords as $pkw) {
		if($pkw=="date"){
			date_default_timezone_set("Asia/Chongqing");
			$content=$content.$pkw.":".date('Y-m-d H:i:s',time())."\n";
		}else{
			$content=$content.$pkw.":\n";
		}
	}
	$content=$content."---\n";
	write_file($postname ,$content);
	echo '<div class="alert alert-info alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong>OK!</strong> 文章文件（'.$postname.'）创建成功，现在您可以开始填写文章内容了</div>';
?>