<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Mhop -静态网站生成器 Beta 1</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link rel="stylesheet" href="template/default/files/bootstrap.min.css">
		<link rel="stylesheet" href="template/default/files/css.css">
		<script type="text/javascript" src="template/default/files/jquery.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#create_post").click(function(){
					$(".admin-post-form").toggle();
				});
				$("#post_name").keydown(function(){
					var reg = new RegExp("^[0-9A-Za-z\-]+$");
					post_name=$("#post_name").val();
					if (!reg.test(post_name)){
						$("#post_name_div").addClass("has-error");
						post_name_error=1;
					}else{
						$("#post_name_div").removeClass("has-error");
						post_name_error=0;
					}
				});
				$("#create_post_subimt").click(function(){
					if($("#post_name").val()==""){
						$("#post_name_div").addClass("has-error");
						post_name_error=1;
					}else{
						$("#alert_info").load("writer/create-post.php?postname="+$("#post_name").val());
						$(".admin-post-form").hide();
					}
				});
				$("#create_site").click(function(){
					$("#alert_info").load("writer/index.php");
				});
			});
		</script>
		<style>
			.admin-button{
				border-radius:0;
				width:100%;
			}
			.admin-button-group{
				margin-bottom:30px;
			}
			.admin-post-form{
				padding:30px 0;
				background:#FFF;
				box-shadow:0 1px 4px #CCC;
				display:none;
			}
		</style>
	</head>

	<body>
		<div  id="header">
			<nav class="navbar navbar-default navbar-inverse" role="navigation">
				<div class="container">
					<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="#">Mhop</a>
					</div>

					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav navbar-right">
							<li><a href="http://zji.me/">作者博客</a></li>
							<li><a href="https://github.com/dmscode/mhop">Github</a></li>
						</ul>
					</div><!-- /.navbar-collapse -->
				</div>
			</nav>
			<div class="text-center" id="header-title">
				<h1>Mhop</h1>
				<h2><small>这是一个飞速生成纯静态的网站的工具</small></h2>
			</div>
		</div>
		<div class="container">
			<div class="row">
				<!-- 主体内容 -->
				<div class="col-md-8 col-md-offset-2">
					<div id="alert_info">
					</div>
					<div class="row admin-button-group">
						<div class="col-md-6">
							<div class="text-center">
								<button type="button" class="btn btn-primary btn-lg admin-button" id="create_post">创建文章</button>
							</div>
						</div>
						<div class="col-md-6">
							<div class="text-center">
								<button type="button" class="btn btn-default btn-lg admin-button" id="create_site">生成网站</button>
							</div>
						</div>
					</div>
					<div class="row admin-post-form">
						<div class="col-md-8">
							<div class="text-center" id="post_name_div">
								<input type="text" class="form-control input-lg" id="post_name" placeholder="请输入只包含数字、字幕、短划线（-）的文章别名">
							</div>
						</div>
						<div class="col-md-4">
							<div class="text-center">
								<button type="button" class="btn btn-default btn-lg admin-button" id="create_post_subimt">创建文章文件</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- 主体内容结束 -->
		</div>
			
			<div id="footer" class="text-center">
				Power by <a href="http://www.zji.me/">自己</a>
			</div>
		</div>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="template/default/files/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="template/default/files/bootstrap.min.js"></script>
	</body>
</html>