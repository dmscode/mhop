<?php
	include "../config.php";
	include "functions.php";
	$create_log ="";
	deldir("../../public/");
	// 遍历文件
	$path = "../../".$contentDir;
	$dir = new RecursiveDirectoryIterator($path);
	// 获取所有文章文件的名称（路径）
	$filesdir=get_files($dir);
	// print_r($filesdir);
	// 遍历所有文章文件
	// 清空 AutoID 索引缓存
	write_file("temp/idindex.php","");
	foreach ($filesdir as $value) {
		$postconfig=read_file($value);
		// 分析文件
		// 初始化数组，用于储存文章元数据
		$postInfo=array();
		// 设置变量，用以判断获取的元数据经过验证后是否更新，以便确定后面是否回写
		$pcchange=0;
		// 遍历所有元数据关键字，提取出对应赋值，加以验证，并写入数组
		foreach ($postInfoKeywords as $pkw) {
			$pvarr=get_post_info($postconfig,$pkw);
			array_push($postInfo,array($pkw,isset($pvarr[0])?$pvarr[0]:""));
			if($pkw=="date"){
				$post_date=$pvarr[0];
			}
			// 元数据判断是否发生改变
			if((isset($pvarr[1])?$pvarr[1]:0)==1){
				$pcchange=1;
			}
		}
		unset($pkw);
		// 获取文章内容
		preg_match("/---\s+([\s\S]*)/i", $postconfig, $pcontent);
		$pcontentcut=explode("---\n", isset($pcontent[0])?$pcontent[0]:"");
		$postContent=isset($pcontentcut[1])?$pcontentcut[1]:"";
		// 如果元数据发生改变则回写
		if($pcchange==1){
			$content="";
			for($i=0;$i<count($postInfo);$i++){
				$content=$content.$postInfo[$i][0].":".$postInfo[$i][1]."\n";
			}
			$content=$content."---\n".$postContent;
			write_file($value,$content);
		}
		// 数据处理，暂时延后，比如 Markdown 的转换，页面导航的生成
		// 生成文章代码
		$postcode=read_templete("post",$theme);
		for($i=0;$i<count($postInfo);$i++){
			$postcode= preg_replace("/<!-- template{".$postInfo[$i][0]."} -->/i",$postInfo[$i][1],$postcode);
		}
		// 获取文章文件名
		preg_match("/[\w\.-]+\.md/i", $value, $filename);
		$filename=explode(".md", isset($filename[0])?$filename[0]:"");
		$filename=$filename[0];
		// 从日期建立目录地址
		preg_match("/\d{4}-\d{2}-\d{2}/", $post_date, $post_date_arr);
		$post_date_arr=explode("-", $post_date_arr[0]);
		write_file("../../public/post/".$post_date_arr[0]."/".$post_date_arr[1]."/".$post_date_arr[2]."/".$filename.".html",$postcode);
		echo "生成文件 ../../public/post/".$filename.".html<br />";
	}
	unset($value); // 最后取消掉引用
?>