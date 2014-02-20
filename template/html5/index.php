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


function TPL_html5__gallery__index($t, $id, $d, $so)
{
	extract($d);

	if (empty($list)) {
		return;
	}

	echo "<table class=\"gallery_index table\">\n";
	echo "<col><col width=\"100%\"><col>\n";
	echo "\t<thead><tr>",
		"<th>", _('Date'), "</th>",
		"<th>", _('Title'), "</th>",
		"<th>", _('Modified'), "</th>",
		"</tr></thead>\n";
	echo "\t<tbody>\n";
	foreach ($list as $item) {
		echo	"\t\t<tr>",
			"<td class=\"date\" nowrap>", htmlspecialchars($item['date']), "</td>",
			"<td class=\"title\"><a href=\"", htmlspecialchars($item['url']), "\">", htmlspecialchars($item['title']), "</a></td>",
			"<td class=\"mtime\" nowrap>", htmlspecialchars($item['mtime']), "</td>",
			"</tr>\n";
	}
	echo "\t</tbody>\n";
	echo "</table>\n";
}



