<html>
<head>
	<title>Test</title>
</head>
<body>
	<script type="text/javascript" src="/_scripts/dojo/dojo/dojo.js"  djConfig="parseOnLoad: true"></script>
	<script type="text/javascript">
       dojo.registerModulePath("widgets","/_scripts/dojo/widgets");
       dojo.require("widgets.ImageList");
       dojo.require("dojo.parser");
	   dojo.require("dojo.data.ItemFileReadStore");
     </script>

	<span dojoType="dojo.data.ItemFileReadStore" jsId="userData" url="/admin/ajax/assets.json.php"></span>
	 <div dojoType="widgets.ImageList" store="userData" imagePath="/data/asset/image/size_thumbnail/"></div>
</body>
</html>