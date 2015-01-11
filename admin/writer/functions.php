<?php
	/**
	 * 读取源文件目录，默认 admin/content/
	 * 使用RecursiveDirectoryIterator遍历文件，列出所有文件路径
	 * @param RecursiveDirectoryIterator $dir 指定了目录的RecursiveDirectoryIterator实例
	 * @return array $files 文件列表
	 */
	 // 遍历文件
	function get_files($dir) {
		$files = array();
		for (; $dir->valid(); $dir->next()) {
			if ($dir->isDir() && !$dir->isDot()) {
				if ($dir->haschildren()) {
					$files = array_merge($files, get_files($dir->getChildren()));
				};
			}else if($dir->isFile()){
				$files[] = $dir->getPathName();
			}
		}
		return $files;
	}
	 // 遍历目录
	function get_dirs($dir) {
		$dirs = array();
		for (; $dir->valid(); $dir->next()) {
			if ($dir->isDir() && !$dir->isDot()) {
				if ($dir->haschildren()) {
					$dirs[] = $dir->getPathName();
					$dirs = array_merge($dirs, get_dirs($dir->getChildren()));
				}else{
					$dirs[] = $dir->getPathName();
				};
			}
		}
		return $dirs;
	}
	// 读取文件
	function read_file($file_name) {
		if(file_exists($file_name) and filesize($file_name)>0){
			$myfile = fopen($file_name, "r");
			$file_content=fread($myfile,filesize($file_name));
			fclose($myfile);
		}else{
			//global $create_log;
			//$create_log =$create_log.$file_name."不存在，或者文件为空.\n";
			$file_content="";
		}
		return $file_content;
	}
	//创建目录函数
	function mkdirs($dir){
		if(!is_dir($dir)){
			if(!mkdirs(dirname($dir))){
				exit('不能创建目录');
			}
			if(!mkdir($dir,0777)){
				exit('不能创建目录2');
			}
		}
		return true;
	}
	// 删除目录函数
	function deldir($path) {
		$dir = new RecursiveDirectoryIterator($path);
		$filesarray=get_files($dir);
		for($i=0;$i<count($filesarray);$i++){
			unlink($filesarray[$i]);
			write_add_file("CreateLog.txt","删除文件".$filesarray[$i]."\n");
		}
		$dir = new RecursiveDirectoryIterator($path);
		$dirarray=get_dirs($dir);
		rsort($dirarray);
		for($i=0;$i<count($dirarray);$i++){
			rmdir($dirarray[$i]);
			write_add_file("CreateLog.txt","删除目录".$dirarray[$i]."\n");
		}
	}
	// 写入文件(追加)
	function write_add_file($file_name,$content) {
		$dir_name=dirname($file_name);
		//目录不存在就创建
		if(!file_exists($dir_name)){
			mkdirs($dir_name);
		}
		$myfile = fopen($file_name, "a");
		fwrite($myfile,$content);
		fclose($myfile);
	}
	// 写入文件(覆写)
	function write_file($file_name,$content) {
		$dir_name=dirname($file_name);
		//目录不存在就创建
		if(!file_exists($dir_name)){
			mkdirs($dir_name);
		}
		$myfile = fopen($file_name, "w");
		fwrite($myfile,$content);
		fclose($myfile);
	}
	// 生成 AutoID
	function generate_autoid(){  
		$length = 6;
		// 密码字符集，可任意添加你需要的字符  
		$chars ='abcdefghigklmnopqrstuvwxyz0123456789';  
		$autoid ='';  
		$onlyId=1;
		$indexIdFile="temp/idindex.php";
		$idIndex=read_file($indexIdFile);
		do{
			for ( $i = 0; $i < $length; $i++ )  
			{  
				// 这里提供两种字符获取方式  
				// 第一种是使用 substr 截取$chars中的任意一位字符；  
				// 第二种是取字符数组 $chars 的任意元素  
				// $autoid .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);  
				$autoid .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
			}
			if(!preg_match("/".$autoid."/i",$idIndex)){
				$onlyId=0;
				write_add_file($indexIdFile,$autoid."\n");
			}
		}while($onlyId);
		return $autoid;  
	} 
	// 文章元数据验证
	function post_info_ver($postInfoKey,$postInfoVal){
		$isChange=0;
		switch ($postInfoKey)
			{
			case "title":
				if(empty($postInfoVal)){
					$postInfoVal="无标题文章";
					$isChange=1;
				}
				break;
			case "date":
				if(empty($postInfoVal)){
					date_default_timezone_set("Asia/Chongqing");
					$postInfoVal=date('Y-m-d H:i:s',time());
					$isChange=1;
				}
				break;
			case "price":
				if(preg_match("/\s+/i",$postInfoVal) or !isset($postInfoVal)){
					$postInfoVal="0";
					$isChange=1;
				}
				break;
			case "autoid":
				if(empty($postInfoVal) or !preg_match("/^\w{6}$/i",$postInfoVal)){
					$postInfoVal=generate_autoid();
					$isChange=1;
				}else{
					$indexIdFile="temp/idindex.php";
					$idIndex=read_file($indexIdFile);
					if(!preg_match("/".$postInfoVal."/i",$idIndex)){
						write_add_file($indexIdFile,$postInfoVal."\n");
						$isChange=0;
					}else{
						$postInfoVal=generate_autoid();
						$isChange=1;
					}
				}
				break;
			default:
				$isChange=0;
			}
		return array($postInfoVal,$isChange);
	}
	// 获取文章元数据
	function get_post_info($postinfo,$postInfoKey){
		$matchWord="/".$postInfoKey.":.+/i";
		preg_match($matchWord, $postinfo, $keyvalarr);
		$keyvalarr=explode($postInfoKey.':', isset($keyvalarr[0])?$keyvalarr[0]:"");
		$keyval=isset($keyvalarr[1])?$keyvalarr[1]:"";
		return post_info_ver($postInfoKey,$keyval);
	}
	// 读取模版代码函数，$templete_type 的值为 post index page 等，模版中没有对应的页面则读取 post
	function read_templete($templete_type,$theme){
		$headercode=read_file("../template/".$theme."/header.html");
		$temp_body_url="../template/".$theme."/".$templete_type.".html";
		if(!file_exists($temp_body_url) or filesize($temp_body_url)==0){
			$templete_type="post";
		}
		$contentcode=read_file("../template/".$theme."/".$templete_type.".html");
		$footercode=read_file("../template/".$theme."/footer.html");
		$postcode=$headercode.$contentcode.$footercode;
		return $postcode;
	}
	// 写入索引函数
	function write_index(){
		global $theme,$indexcontentarray,$indexkw,$siteInfo,$postitemcode,$siteName,$siteDesc,$siteKeywords,$indexpagenum,$publicDir,$createpagenum,$contentDir;
		$postcodecontent="";
		for($k=0;$k<count($indexcontentarray);$k++){
			$postcodec=$postitemcode;
			for($j=0;$j<count($indexkw);$j++){
				$postcodec=preg_replace("/<!-- template{".$indexkw[$j]."} -->/i",$indexcontentarray[$k][$indexkw[$j]],$postcodec);
				if($indexkw[$j]=="posturl"){
					preg_match("/[\w\.-]+\/[\w\.-]+\.html/i", $indexcontentarray[$k][$indexkw[$j]], $filename);
					$filename=explode("/", isset($filename[0])?$filename[0]:"");
					$filename=$filename[0];
					$postconfig=read_file("../../".$contentDir.$filename.".md");
					preg_match("/---\s+([\s\S]*)/i", $postconfig, $pcontent);
					$pcontent=substr(isset($pcontent[0])?$pcontent[0]:"",4,strlen(isset($pcontent[0])?$pcontent[0]:""));
					// 转换文章内容到 Html 代码
					$Parsedown = new Parsedown();
					$pcontent = $Parsedown->text($pcontent);
					$postcodec=preg_replace("/<!-- template{content} -->/i",$pcontent,$postcodec);
				}
			}
			$postcodecontent=$postcodecontent.$postcodec;
		}
		$postcode=read_templete("index",$theme);
		for($n=0;$n<count($siteInfo);$n++){
			$postcode= preg_replace("/<!-- template{".$siteInfo[$n][0]."} -->/i",$siteInfo[$n][1],$postcode);
		}
		$postcode= preg_replace('/="files\//i','="/files/',$postcode);
		$postcode= preg_replace("/<!-- template{desc} -->/i",$siteDesc,$postcode);
		$postcode= preg_replace("/<!-- template{tags} -->/i",$siteKeywords,$postcode);
		$postcode= preg_replace("/<!-- template{content} -->/i",$postcodecontent,$postcode);
		if(!file_exists("../template/".$theme."/home.html") and $indexpagenum==1){
			$homecode=$postcode;
			$homecode= preg_replace("/<!-- template{page_title} -->/i",$siteName,$homecode);
			$homecode= preg_replace("/<!-- template{page_js} -->/i",'<script type="text/javascript">page_type="home";page_num=1;</script><script type="text/javascript" src="/files/page_nav.js"></script>',$homecode);
			write_file("../../".$publicDir."index.html",$homecode);
			write_add_file("CreateLog.txt","生成网站首页 ../../".$publicDir."index.html\n");
			$createpagenum++;
		}
		$postcode= preg_replace("/<!-- template{page_title} -->/i",$siteName." | page-".$indexpagenum,$postcode);
		$postcode= preg_replace("/<!-- template{page_js} -->/i",'<script type="text/javascript">page_type="index";page_num='.$indexpagenum.';</script><script type="text/javascript" src="/files/page_nav.js"></script>',$postcode);
		// 写入文件
		if($indexpagenum==1){
			write_file("../../".$publicDir."archives/index.html",$postcode);
			write_add_file("CreateLog.txt","生成索引文件 ../../".$publicDir."archives/index.html\n");
		}else{
			write_file("../../".$publicDir."archives/page-".($indexpagenum-1).".html",$postcode);
			write_add_file("CreateLog.txt","生成索引文件 ../../".$publicDir."archives/page-".$indexpagenum.".html\n");
		}
		$createpagenum++;
		$indexpagenum++;
		$indexcontentarray=array();
	}

?>