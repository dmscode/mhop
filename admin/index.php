<?php include "congif.php";?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Mhop 静态网站生成器</title>
		<meta name="description" content="只是梦，为了飞翔" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link rel="stylesheet" href="writer/files/bootstrap.min.css">
		<link rel="stylesheet" href="writer/files/css.css">
		<script type="text/javascript" src="writer/files/jquery.min.1.7.2.js"></script>
	</head>

	<body>
		<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">Mhop</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">所有工具 <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="popup.php">写文章</a></li>
								<li class="divider"></li>
								<li><a href="post.php">网站生成</a></li>
								<li><a href="slide.php">宽屏轮播</a></li>
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="#">帮助</a></li>
						<li><a href="#">关于</a></li>
					</ul>
				</div><!-- /.navbar-collapse -->
			</div>
		</nav>
		<div class="container">
			<div id="header" class="text-center">
				<h1>自己</h1>
				<h2><small>那些梦</small></h2>
			</div>
			<div id="content" class="text-center">
				<button>生成网站</butto>
			</div>
			<div id="footer" class="text-center">
				Power by <a href="http://www.zji.me/">自己</a>
			</div>
		</div>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="writer/files/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="writer/files/bootstrap.min.js"></script>
	</body>
</html>