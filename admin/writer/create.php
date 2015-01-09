<?php
	include "../config.php";
	include "functions.php";
	include "markdown.class.php";
	$create_log ="";
	deldir("../../".$publicDir);
	deldir("temp/");
	// 遍历文件
	$path = "../../".$contentDir;
	$dir = new RecursiveDirectoryIterator($path);
	// 准备网站信息数组
	// 生成导航代码
	$navcode=read_file("../template/".$theme."/nav.html");
	$navc="";
	for($i=0;$i<count($navItems);$i++){
		$navc=$navc.preg_replace('/<!-- template{navItem} -->/i',$navItems[$i],preg_replace('/<!-- template{navLink} -->/i',$navLinks[$i],$navcode));
	}
	 $siteInfo=array(
		array("localhost",$localhost),
		array("siteName",$siteName),
		array("siteDesc",$siteDesc),
		array("siteKeywords",$siteKeywords),
		array("navCode",$navc)
	 );
	// 获取所有文章文件的名称（路径）
	$filesdir=get_files($dir);
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
		$pcontent=substr(isset($pcontent[0])?$pcontent[0]:"",4,strlen(isset($pcontent[0])?$pcontent[0]:""));
		// 如果元数据发生改变则回写
		if($pcchange==1){
			$content="";
			for($i=0;$i<count($postInfo);$i++){
				$content=$content.$postInfo[$i][0].":".$postInfo[$i][1]."\n";
			}
			$content=$content."---\n".$pcontent;
			write_file($value,$content);
		}
		// 写入按日期设定的索引文件
		// 从日期建立目录地址
		preg_match("/\d{4}-\d{2}-\d{2}/", $post_date, $post_date_arr);
		$post_date_arr=explode("-", $post_date_arr[0]);
		$postindex="";
		for($i=0;$i<count($postInfo);$i++){
			$postindex=$postindex.$postInfo[$i][1]."<-{M}->";
		}
		$postindex=$postindex."\n";
		write_add_file("temp/index/".$post_date_arr[0]."-".$post_date_arr[1].".txt",$postindex);
		// 转换文章内容到 Html 代码，并加入数组
		$obj = new Markdown();
		$pcontent = $obj->parseMarkdown($pcontent);
		array_push($postInfo,array("content",$pcontent));
		// 数据处理，暂时延后，比如 Markdown 的转换，页面导航的生成
		// 生成文章代码
		$postcode=read_templete("post",$theme);
		for($i=0;$i<count($postInfo);$i++){
			$postcode= preg_replace("/<!-- template{".$postInfo[$i][0]."} -->/i",$postInfo[$i][1],$postcode);
		}
		for($i=0;$i<count($siteInfo);$i++){
			$postcode= preg_replace("/<!-- template{".$siteInfo[$i][0]."} -->/i",$siteInfo[$i][1],$postcode);
		}
		$postcode= preg_replace('/="files\//i','="/files/',$postcode);
		// 获取文章文件名
		preg_match("/[\w\.-]+\.md/i", $value, $filename);
		$filename=explode(".md", isset($filename[0])?$filename[0]:"");
		$filename=$filename[0];
		// 写入文件
		write_file("../../".$publicDir."post/".$post_date_arr[0]."/".$post_date_arr[1]."/".$post_date_arr[2]."/".$filename."/index.html",$postcode);
		echo "生成文件 ../../".$publicDir."post/".$filename."/index.html<br />";
	}
	unset($value); // 最后取消掉引用
	// 从索引文件生成索引页面
	$indexa=array();
	$path = "temp/index/";
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	rsort($filesdir);
	// 设置一个数组，用来储存处理好，用来写入索引页面的信息
	$indexcontentarray=array();
	// 遍历索引文件
	$indexpagenum=1;
	foreach ($filesdir as $value) {
		$indexcontent=read_file($value);
		$indexcontent=preg_replace("/<-{M}->$/i","",$indexcontent);
		$indexarry = explode("\n", $indexcontent);
		// 设置一个数组，用来储存当前索引文件中的信息
		$indexthismonth=array();
		for($i=0;$i<count($indexarry);$i++){
			if(!empty($indexarry[$i])){
				$indexarr= explode("<-{M}->", $indexarry[$i]);
				array_splice($indexarr,count($postInfoKeywords));
				$indexa=array();
				for($k=0;$k<count($postInfoKeywords);$k++){
					$indexa[$postInfoKeywords[$k]]=$indexarr[$k];
				}
				array_push($indexthismonth,$indexa);
			}
		}
		if(count($indexthismonth)>1){
			unset($tmp);
			foreach ($indexthismonth as $key => $row) {  
				$tmp[$key] = $row['date'];  
			} 
			array_multisort($tmp,SORT_DESC,$indexthismonth);
		}
		for($i=0;$i<count($indexthismonth);$i++){
			array_push($indexcontentarray,$indexthismonth[$i]);
			if(count($indexcontentarray)==$postnum){
				$postitemcode=read_file("../template/".$theme."/indexitem.html");
				$postcodecontent="";
				for($k=0;$k<count($indexcontentarray);$k++){
					$postcodec=$postitemcode;
					for($j=0;$j<count($postInfoKeywords);$j++){
						$postcodec=preg_replace("/<!-- template{".$postInfoKeywords[$j]."} -->/i",$indexcontentarray[$k][$postInfoKeywords[$j]],$postcodec);
					}
					$postcodecontent=$postcodecontent.$postcodec;
				}
				$postcode=read_templete("index",$theme);
				for($n=0;$n<count($siteInfo);$n++){
					$postcode= preg_replace("/<!-- template{".$siteInfo[$n][0]."} -->/i",$siteInfo[$n][1],$postcode);
				}
				$postcode= preg_replace('/="files\//i','="/files/',$postcode);
				for($m=0;$m<count($siteInfo);$m++){
					$postcode= preg_replace("/<!-- template{".$siteInfo[$m][0]."} -->/i",$siteInfo[$m][1],$postcode);
				}
				$postcode= preg_replace('/="files\//i','="/files/',$postcode);
				$postcode= preg_replace("/<!-- template{content} -->/i",$postcodecontent,$postcode);
				// 写入文件
				write_file("../../".$publicDir."archives/page-".$indexpagenum.".html",$postcode);
				$indexpagenum++;
				// 生成文章代码

				echo "生成索引文件 ../../".$publicDir."archives/page-".$indexpagenum.".html<br />";
				$indexcontentarray=array();
			}
		}
	}
	unset($value); // 最后取消掉引用
	// 遍历模版引用文件
	$path = "../template/".$theme."/files/";
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	foreach ($filesdir as $value) {
		$thematch="/..\/template\/".preg_replace("/\//i","\\\/",$theme)."/i";
		$tovalue=preg_replace($thematch,"../../".$publicDir,$value);
		$dir_name=dirname($tovalue);
		//目录不存在就创建
		if(!file_exists($dir_name)){
			mkdirs($dir_name);
		}
		// 复制模版引用文件
		copy($value,$tovalue);
	}
	unset($value); // 最后取消掉引用
?>