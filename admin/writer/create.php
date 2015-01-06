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
		foreach ($postInfoKeywords as $pkw) {
			array_push($postInfo,array($pkw,get_post_info($postconfig,$pkw)));
		}
		preg_match("/---\s+([\s\S]*)/i", $postconfig, $pcontent);
		$pcontentcut=explode("---\n", isset($pcontent[0])?$pcontent[0]:"");
		$postContent=isset($pcontentcut[1])?$pcontentcut[1]:"";
		unset($pkw);

		$content="";
		for($i=0;$i<count($postInfo);$i++){
			$content=$content.$postInfo[$i][0].":".$postInfo[$i][1]."\n";
		}
		$content=$content."---\n".$postContent;
		write_file($value,$content);

		print_r($postInfo);
		echo $postContent;
	}
	// $arr is now array(2, 4, 6, 8)
	unset($value); // 最后取消掉引用
?>