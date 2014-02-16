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


class B_gallery__index__load extends \Cascade\Core\Block {

	protected $inputs = array(
		'directory' => '.',
		'path_prefix' => './',
		'url_prefix' => '/',
	);

	protected $outputs = array(
		'list' => true,
		'done' => true,
	);


	public function main()
	{
		$directory = $this->in('directory').'/';
		$path_prefix = $this->in('path_prefix');
		$url_prefix  = $this->in('url_prefix');
		$list = array();

		$directory = preg_replace('/\.\+\//', '', $directory);

		if (($d = opendir($path_prefix.$directory))) {
			while (($file = readdir($d)) !== false) {
				if ($directory == './') {
					$full_name = $file;
				} else {
					$full_name = $directory.$file;
				}
				if ($file[0] != '.' && is_dir($path_prefix.$full_name)) {
					$info = B_gallery__gallery__load::getGalleryInfo($path_prefix.$full_name);
					if ($info !== false) {
						$list[$file] = array_merge($info, array(
								'url' => $url_prefix.$full_name,
							));
					}
				}
			}

			closedir($d);
			uksort($list, 'strcoll');

			$this->out('done', true);
			$this->out('list', $list);
		}
	}
};


