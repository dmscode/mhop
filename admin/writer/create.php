<?php
	include "../config.php";
	include "functions.php";
	// 遍历文件
	$path = "../../".$contentDir;
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	// print_r($filesdir);
	// 读取文件
	foreach ($filesdir as $value) {
		$postconfig=read_file($value);
		// 分析文件
		$postInfo=array();
		$pcchange=0;
		foreach ($postInfoKeywords as $pkw) {
			$pvarr=get_post_info($postconfig,$pkw);
			array_push($postInfo,array($pkw,isset($pvarr[0])?$pvarr[0]:""));
			if((isset($pvarr[1])?$pvarr[1]:0)==1){
				$pcchange=1;
			}
		}
		unset($pkw);
		// 获取文章内容
		preg_match("/---\s+([\s\S]*)/i", $postconfig, $pcontent);
		$pcontentcut=explode("---\n", isset($pcontent[0])?$pcontent[0]:"");
		$postContent=isset($pcontentcut[1])?$pcontentcut[1]:"";
		if($pcchange==1){
			$content="";
			for($i=0;$i<count($postInfo);$i++){
				$content=$content.$postInfo[$i][0].":".$postInfo[$i][1]."\n";
			}
			$content=$content."---\n".$postContent;
			write_file($value,$content);
		}
		print_r($postInfo);
		echo $postContent;
	}
	unset($value); // 最后取消掉引用
?>