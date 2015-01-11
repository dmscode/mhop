<?php
	$t1 = microtime(true);
	// 引用外部文件
	include "../config.php";// 网站配置文件
	include "functions.php";// 网站函数文件
	include "Parsedown.php";// Markdown 解析类
	// 创建日志变量，并清空日志
	$create_log ="";
	$createpagenum=0;
	write_file("CreateLog.txt","");
	// 清空网站生成目录和缓存目录
	deldir("../../".$publicDir);
	if(!file_exists("temp/")){
		mkdirs("temp/");
	}else{
		deldir("temp/");
	}
	// 重置 AutoID 索引缓存
	write_file("temp/idindex.php","");
	//以上为工作环境初始化工作
	
	
	// 生成导航代码
	$navcode=read_file("../template/".$theme."/navitem.html");
	$navc="";
	for($i=0;$i<count($navItems);$i++){
		$navc=$navc.preg_replace('/<!-- template{navItem} -->/i',$navItems[$i],preg_replace('/<!-- template{navLink} -->/i',$navLinks[$i],$navcode));
	}
	unset($navcode);// 从模版读取的导航代码被释放
	// 准备网站信息数组
	$siteInfo=array(
		array("localhost",$localhost),
		array("siteName",$siteName),
		array("siteDesc",$siteDesc),
		array("siteKeywords",$siteKeywords),
		array("navCode",$navc)
	);
	// 遍历文章源文件（.md 文件）
	// 获取所有文章文件的名称（路径）
	$path = "../../".$contentDir;
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	// 遍历所有文章文件
	foreach ($filesdir as $value) {
		// 读取文件内容
		$postconfig=read_file($value);
		// 初始化数组，用于储存文章元数据
		$postInfo=array();
		// 设置变量，用以判断获取的元数据经过验证后是否更新，以便确定后面是否回写
		$pcchange=0;
		// 遍历所有元数据关键字，提取出对应赋值，加以验证，并写入数组
		foreach ($postInfoKeywords as $pkw) {
			$pvarr=get_post_info($postconfig,$pkw);
			array_push($postInfo,array($pkw,isset($pvarr[0])?$pvarr[0]:""));
			// 获取文章日期字段，用于生成文件目录等
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
		// 获取文章文件名
		preg_match("/[\w\.-]+\.md/i", $value, $filename);
		$filename=explode(".md", isset($filename[0])?$filename[0]:"");
		$filename=$filename[0];
		$posturl="../../".$publicDir."post/".$post_date_arr[0]."/".$post_date_arr[1]."/".$post_date_arr[2]."/".$filename."/index.html";
		$posturllink="post/".$post_date_arr[0]."/".$post_date_arr[1]."/".$post_date_arr[2]."/".$filename."/index.html";
		$postindex=$postindex."/".$posturllink."\n";
		write_add_file("temp/index/".$post_date_arr[0]."-".$post_date_arr[1].".txt",$postindex);
		// 转换文章内容到 Html 代码，并加入数组
		$Parsedown = new Parsedown();
		$pcontent = $Parsedown->text($pcontent);
		array_push($postInfo,array("content",$pcontent));
		// 生成文章代码
		$postcode=read_templete("post",$theme);
		for($i=0;$i<count($postInfo);$i++){
			$postcode= preg_replace("/<!-- template{".$postInfo[$i][0]."} -->/i",$postInfo[$i][1],$postcode);
			if($postInfo[$i][0]=="title"){
				$posttitle=$postInfo[$i][1];
			}
		}
		for($i=0;$i<count($siteInfo);$i++){
			$postcode= preg_replace("/<!-- template{".$siteInfo[$i][0]."} -->/i",$siteInfo[$i][1],$postcode);
		}
		$postcode= preg_replace("/<!-- template{page_title} -->/i",$posttitle."-".$siteName,$postcode);
		$postcode= preg_replace('/="files\//i','="/files/',$postcode);
		// 写入文件
		write_file($posturl,$postcode);
		write_add_file("CreateLog.txt","生成文件 ".$posturl."\n");
		$createpagenum++;
	}
	unset($value); // 最后取消掉引用
	// 从索引文件生成索引页面
	$indexa=array();
	if(!file_exists("temp/index/")){
		mkdirs("temp/index/");
	}
	$path = "temp/index/";
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	rsort($filesdir);
	// 设置一个数组，用来储存处理好，用来写入索引页面的信息
	$indexcontentarray=array();
	// 遍历索引文件
	$indexpagenum=1;
	$indexkw=$postInfoKeywords;
	array_push($indexkw,"posturl");
	$postitemcode=read_file("../template/".$theme."/indexitem.html");
	foreach ($filesdir as $value) {
		$indexcontent=read_file($value);
		//$indexcontent=preg_replace("/<-{M}->$/i","",$indexcontent);
		$indexarry = explode("\n", $indexcontent);
		// 设置一个数组，用来储存当前索引文件中的信息
		$indexthismonth=array();
		for($i=0;$i<count($indexarry);$i++){
			if(!empty($indexarry[$i])){
				$indexarr= explode("<-{M}->", $indexarry[$i]);
				array_splice($indexarr,count($indexkw));
				$indexa=array();
				for($k=0;$k<count($indexkw);$k++){
					$indexa[$indexkw[$k]]=$indexarr[$k];
				}
				array_push($indexthismonth,$indexa);
			}
		}
		// 对本月的索引数据进行日期上的排序
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
				write_index();
			}
		}
	}
	if(count($indexcontentarray)>0){
		write_index();
	}
	$index_nav_js='index_page_num='.$indexpagenum.';$(document).ready(function(){if(page_type=="home"){$("#next_page a").attr("href","/archives/page-1.html");}if(page_type=="index"){$("#next_page a").attr("href","page-"+page_num+".html");if(page_num>3){$("#prev_page a").attr("href","page-"+(page_num-2)+".html");}else{$("#prev_page a").attr("href","index.html");}}if(page_num==1){$("#prev_page").html("");}if(page_num==index_page_num){$("#next_page").html("");}});';
	$index_nav_js_url="../../".$publicDir."files/page_nav.js";
	write_file($index_nav_js_url,$index_nav_js);
	unset($value); // 最后取消掉引用
	// 遍历模版引用文件
	$path = "../template/".$theme."/files/";
	$dir = new RecursiveDirectoryIterator($path);
	$filesdir=get_files($dir);
	foreach ($filesdir as $value) {
		$thematch="/..\/template\/".preg_replace("/\//i","\\",$theme)."\//i";
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
	$t2 = microtime(true);
	echo '<div class="alert alert-info alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong>OK!</strong> 本次网站共生成页面:'.$createpagenum.'页，耗时：'.number_format(($t2-$t1),3).'秒</div>';
?>