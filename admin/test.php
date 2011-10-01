<?php

// This file is part of In-Folio - http://blog.in-folio.org.uk/blog/
//
// In-Folio is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// In-Folio is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with In-Folio.  If not, see <http://www.gnu.org/licenses/>.
?>
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