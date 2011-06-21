<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Slave Narratives Explorer (Prototype)</title>
	<script src="src/js/heatmap.js" type="text/javascript"></script>
	<script src="src/js/util.js" type="text/javascript"></script>
	<script src="src/js/user.js" type="text/javascript"></script>
	<script src="src/js/params.js" type="text/javascript"></script>
	<script src="lib/raphael.js" type="text/javascript"></script>
	<script src="lib/json2.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.scrollTo.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.url.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.safeenter.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.hoverintent.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.ui.js" type="text/javascript"></script>
	<script src="lib/wordtree.js" type="text/javascript"></script>
	<script src="lib/word-tree-layout.js" type="text/javascript"></script>
	
	<link rel='stylesheet' href="style/fonts.css">
	<link rel='stylesheet' href="style/jquery-ui-smoothness.css">
	<link rel='stylesheet' href="style/searchresults.css">
	<link rel='stylesheet' href="style/bubbles.css">
	<link rel='stylesheet' href="style/heatmap.css">
	<script type="text/javascript">
	$(window).load(function(){
		windowLoaded = true;
	})
	$('document').ready(init); // in src/heatmap.js
	</script>
</head>
<body>
	<div id="wrapper">
	<nav>
		<ul>
		<li class="menu"><a href="http://eecs.berkeley.edu/~aditi/projects/wordseer.html">About</a></li>
		<li class="menu"><a href="index.php">Search</a></li>
		<li class="menu"><a href="view.php">Read and Annotate</li>
		<li class="menu"><a href="#">Heat Maps</a></li>
		<li id="examples-menu" class="menu"> Examples </li>
		<ul class="examples">
			<li class="submenu"><a href='heatmap.php?words=mother%20father;'>Mother or father</a></li>
			<li class="submenu"><a href='heatmap.php?words=+mother%20+father;'>Mother and father</a></li>
			<li class="submenu"><a href='heatmap.php?words="I%20was%20born";'>"I was born"</a></li>
			<li class="submenu"><a href="heatmap.php?words=+(mother%20father)%20+(separated%20sold%20sell);">Separation from mother or father</a></li>
			<li class="submenu"><a href="heatmap.php?words=+(punish*%20whip*%20beat*)%20+(cruel%20harsh);">Cruel punishments</a></li>
		</ul></li>
		<li class="menu" id="user"> </li>
	</ul>
	</nav>
	<div id="debug"></div>
	<div id="header">
		<a href="index.php"><img class="logo" src="img/wordseer.png"></a>
		<h1 class="title"> Visualize Word Distributions </h1>
	</div>
	<article>
		<table class="heatmap">
			<tr>
				<td colspan="2">
					<fieldset>
					<label>Enter words to visualize</label>
					<input type="text" id="words"></input> 
					<label>Filter documents by tag</label>
					<select class="tag select" id="tag"></select>
					<input type="radio" id="sentences-unit" name="unit" value="sentences" checked= "checked"></input>
					<label>Sentences</label>
					<input type="radio" id="paragraphs-unit" name="unit" value="paragraphs"></input>
					<label>Paragraphs</label>
					<input id="add-heat-map" type="button" value="Go"></input>
					<input id="reset-heat-map" type="button" value="Reset"></input>
					</fieldset>
				</td>
			</tr>
			<tr id="suggestions">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>
					<ul id="words-list">
					</ul>
				</td>
				<td>
					<div id="heatmap"></div>
				</td>
			</tr>
		</table>
			<div id="info-popup" class="triangle-border popup">
				<img type="close" class="close button" src="img/close.png">
				<div class="content">
				</div>
			</div>
			<div id="sentence-popup" class="triangle-border left popup">
				<img type="close" class="close button" src="img/close.png">
				<table id="sentences"></table>
			</div>
			<div id = "concordance">
				<ul id="tabs-list">
				</ul>
			</div>
			<div id= "gramamtical-context">
			</div>
		
	</article>
	</div id="wrapper">
</body>