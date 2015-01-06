<?php
	/**
	 * 读取源文件目录，默认 admin/content/
	 * 使用RecursiveDirectoryIterator遍历文件，列出所有文件路径
	 * @param RecursiveDirectoryIterator $dir 指定了目录的RecursiveDirectoryIterator实例
	 * @return array $files 文件列表
	 */
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
	// 读取文件
	function read_file($file_name) {
		if(file_exists($file_name) and filesize($file_name)>0){
			$myfile = fopen($file_name, "r") or die("Unable to open file!");
			$filecontent=fread($myfile,filesize($file_name));
			fclose($myfile);
		}else{
			$filecontent="";
		}
		return $filecontent;
	}
	// 写入文件(追加)
	function write_add_file($file_name,$content) {
		$myfile = fopen($file_name, "a") or die("Unable to open file!");
		fwrite($myfile,$content);
		fclose($myfile);
	}
	// 写入文件(覆写)
	function write_file($file_name,$content) {
		$myfile = fopen($file_name, "w") or die("Unable to open file!");
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
		if(file_exists($indexIdFile)){
			$idIndex=read_file($indexIdFile);
		}else{
			$idIndex="";
		}
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
				if(preg_match("/\s+/i",$postInfoVal) or !isset($postInfoVal) or empty($postInfoVal)){
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
				if(preg_match("/\s+/i",$postInfoVal) or !isset($postInfoVal) or empty($postInfoVal)){
					$postInfoVal=generate_autoid();
					$isChange=1;
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
?>