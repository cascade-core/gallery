<?php
/*
 * Copyright (c) 2011-2014, Josef Kufner  <jk@frozen-doe.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */


function TPL_html5__gallery__gallery($t, $id, $d, $so)
{
	extract($d);

	if (empty($list)) {
		return;
	}

	echo "<div class=\"gallery_listing\">\n";
	foreach ($list as $item) {
		echo	"\t<a class=\"item\" href=\"", htmlspecialchars($item['url']), "\">\n",
				"\t\t<span class=\"photo\"><img src=\"", htmlspecialchars($item['tb_url']), "\" alt=\"[thumbnail]\"></span>\n",
				"\t\t<span class=\"name\">", htmlspecialchars($item['filename']), "</span>",
			"</a>\n";
	}
	echo "</div>\n";
}



