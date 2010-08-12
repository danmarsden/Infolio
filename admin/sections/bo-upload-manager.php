<style>

ul{
list-style:none;
margin:0;
padding:0;
}

#container2{
position:relative
}

#overlay{
display: none;
position: fixed;
top: 0%;
left: 0%;
width: 100%;
height: 100%;
background-color: black;
z-index:1001;
-moz-opacity: 0.8;
opacity:.80;
filter: alpha(opacity=80)
}

#info{
display: none;
position: fixed;
top: 25%;
left: 25%;
width: 50%;
height: 18em;
padding: 16px;
border: 1px solid black;
background-color: white;
z-index:1002;
overflow: auto
}

#info div{
border: solid #F2F2F2 5px;
-moz-border-radius: 0 10px 10px 0;
background:#F2F2F2;
clear:both;
margin-bottom:10px
}

#info h2{
padding-bottom:0;
}

#info h3{
padding-bottom:.5em;
}

#info h2, #info h3{
margin-top:0;
margin-bottom:0;
}

#info label{
display:block;
width:100px;
margin-right:10px;
text-align: right;
float:left
}

#info h3#infoText{
padding: .2em;
margin-bottom: .3em;
margin-top: .3em;
}

#info h3#infoText.warning{
border:1px solid red;
color:red;
}

#info input{
display:block;
margin-right:10px;
float:left
}

#info #exit{
position:absolute;
top:0;
right:20px;
margin-top:1em;
background:#f2f2f2;
border:1px solid black;
padding:5px;
}


#batchcontainer{
position:absolute;
right:0;
width:300px;
top:0;
padding:10px;
height:600px;
}

#batchitems{
height:580px;
overflow:scroll;
}

#batchcontainer h2{
float:left;
margin-right: 10px;
margin-top:0
}

#batchcontainer p{
margin-top:0
}

#batchitems li{
position:relative;
}

#batchitems img{
width:100px;
height:100px
}

#batchitems a{
position:absolute;
top:0;
right:0
}

#container{
margin-right:330px;
height:600px;
overflow:scroll;
}

.item{
clear:both;
border: solid #F2F2F2 5px;
margin:10px;
padding:10px
}

.item:after{content:".";clear:both;display:block;visibility:hidden;height:1px}

#container p.image{
float:left;
width:100px;
margin:0

}

#container .image img{
width:100px;
height:100px;
border:1px solid black;
}

#container ul{
float:left;
margin-right:10px;
width:370px
}

#container ul textarea{
width:250px
}

#container li{
margin-bottom:10px
}

#container label{
display:block;
float:left;
margin-right:10px;
width:100px;
text-align:right;
}

#bulkactions{
clear:both;
margin-right: 340px;
padding-top:10px
}

.tags{
display:block;
width:200px;
float:left;
overflow: scroll;
height:230px;
display:none;
position:absolute;
margin-left:10px;
background:#fff;
border:1px solid #000;
padding:10px
}

.tags a{
display:block;
}

.tags li.selected a{
color:#ccc;
}

.tags li.selected a:hover{
cursor: text;
text-decoration:none
}

#container .batch{
border: solid #F2F2F2 5px;
background:#F2F2F2;
}


.button input{
border:1px solid black;
padding: 5px;
background: #F2F2F2;
}

.fr{
float:right
}

#container .selectbatch, #container .picktags{
border:1px solid black;
padding: 5px;
background: #F2F2F2;
}

#container .batch .selectbatch{
border:1px solid #F2F2F2;
padding: 5px;
background: #F2F2F2;
}

#container .batch .selectbatch:hover{
text-decoration:none;
cursor:text;
}

#batchactions{
display:none;
margin-top:1em
}

#batchactions li#edit a{
float:right;
padding: 5px;
background: #F2F2F2;
border:1px solid black;
}

#batchcontainer #clearBatch{
display:none
}

#container .closeTags{
text-align:right
}

#viewOptions{
float:right;
margin-right:350px
}
#viewOptions li{
float:left;
margin-right:10px;
}
#viewOptions li.thumbs{
border:none
}
#viewOptions li a{
padding: 5px;
background: #F2F2F2;
border:1px solid black;
}

#viewOptions li.selected a{
background: none;
border: none;
}

#viewOptions li.selected a:hover{
text-decoration:none;
cursor:text
}

#container.thumbs ul{
	display:none
}

#container.thumbs p{
	display:none
}

#container.thumbs p.image{
display:block;
}

#container.thumbs .item{
width: 105px;
float:left;
clear:none;
}

#selectall{
text-align:right;
margin-right:360px;
}

</style>

<div id="overlay"></div>
<div id="info"></div>
<div>

	<div id="testingasync"></div>

	<ul id="viewOptions">
		<li id="list" class="selected"><a href="#">View as list</a></li>
		<li id="thumbs"><a href="#">View as thumbnails</a></li>
	</ul>
	
	<h1>Edit photo titles &amp; tags</h1>
	


		<div id="container2">
			<div id="batchcontainer">

				<h2 id="batchinfo">No files selected</h2>
				
				<p id="clearBatch"><a href="#">Clear list</a></p>

				<ul id="batchactions">
					<li id="edit"><a href="#">Batch edit</a></li>
				</ul>

			</div>
			
			<p id="selectall"><a href="#">Select all</a></p>
							
			
			
			<div id="container" class="list">
			
				<!-- For the JS to work each of the items need a unique ID -->

				<!-- Fragment start -->
				
<?php

if($fh = opendir($adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD)){
	$files = Array();
    while(false !== ($entry = readdir($fh))){
    	if(is_file($adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.$entry)){
			if($entry != '.' && $entry != '..' && $entry != 'Thumbs.db' ){
				$files[] = $entry;
			}
		}
    }
    closedir($fh);
}
$i=1;

if(!isset($institution)) {
	$institution = $adminUser->getInstitution();
}

print "<span class='tags'>";
print "<span class='closeTags'><a href='#'>Hide tags</a></span>";
print "<ul>";
$tags = Tag::RetrieveByInstitution($institution);
foreach($tags as $tag) {
print "<li><a href='#'>{$tag->getName()}</a></li>";
}
print "</ul>";
print "</span>";
print "</span>";
?>
				<p><input type="hidden" id="numofitems" value="<?php echo count($files); ?>" /></p>
<?php

foreach($files as $filename){
	$asset = Asset::CreateNew($filename, $adminUser);
	// TODO - deal with videos and audio - framegrabs and placeholder images?
	switch($asset->getType()){
		case Asset::IMAGE:
			$path = DIR_WS_DATA.$adminUser->getInstitution()->getUrl().'-asset/'.DIR_WS_DATA_UPLOAD . $asset->imageResizeForPreset('size_thumbnail', $adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD);
			break;
		case Asset::VIDEO:
			$path = str_replace(DIR_FS_ROOT, '/', $asset->generateThumbnail(Array('x' => 100, 'y' => 100), $adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD));
			//$path = DIR_WS_DATA.$adminUser->getInstitution()->getUrl().'-asset/'.DIR_WS_DATA_UPLOAD . $asset->generateThumbnail(Array('x' => 100, 'y' => 100), $adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD);
			break;
		case Asset::AUDIO:
			$path = DIR_WS_IMAGES . 'audio-placeholder.png';
			break;
		default:
			break;
	}
	
	
	
	
?>		
				<div class="item" id="item<?php echo $i ?>">

					<p class="image" id="image<?php echo $i ?>"><img src="<?php echo $path ?>" alt="" /></p>

					<p><input name="filename" type="hidden" value="<?php echo $filename;?>"></p> 

					<ul>
						<li><label for="title<?php echo $i ?>">Title</label><input name="title" id="title<?php echo $i ?>" type="text" value="" /></li>
						<li><label for="description<?php echo $i ?>">Description</label><input name="description" id="description<?php echo $i ?>" type="text" value="" /></li>
						<li><label for="tags<?php echo $i ?>">Tags</label><textarea name="tags" id="tags<?php echo $i ?>"></textarea></li>
					</ul>

					<p><a class="selectbatch" href="#">Select for batch edit</a></p>

					<p><a class="picktags" href="#">Pick from existing tags</a></p>
				</div>

<?php
$i++;				
}
?>

				<!-- Fragment end -->
			</div>
		</div>

		<div id="bulkactions">
			<ul>
				<li class="button fr"><input id="saveAll" type="submit" value="Save all changes" /></li>
				<li class="button"><input type="submit" value="cancel" /></li>	
			</ul>
		</div>
	</div>

	<script type="text/javascript" src="<?php echo DIR_WS_JAVASCRIPT?>jquery/jquery-1.2.6.min.js"></script>
	<script type="text/javascript" src="/admin/_scripts/bo-upload-manager.js"></script>
	<script type="text/javascript" src="/admin/_scripts/bo-async.js"></script>