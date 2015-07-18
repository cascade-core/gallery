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
		'gallery_config' => null,
	);

	protected $connections = array(
		'gallery_config' => array('config', 'gallery'),
	);

	protected $outputs = array(
		'list' => true,
		'done' => true,
	);


	public function main()
	{
		$gallery_config = $this->in('gallery_config');

		$path_prefix = $gallery_config['path_prefix'];
		$url_prefix  = $gallery_config['url_prefix'];
		$index_file  = $gallery_config['index_file'];
		$list = array();

		if ($index_file) {
			// Read index file and scan only named subdirectories
			foreach (file($index_file) as $file) {
				$file = trim($file);
				if ($file == '' || $file[0] == '#') {
					continue;
				}
				if (is_dir($path_prefix.$file)) {
					$info = B_gallery__gallery__load::getGalleryInfo($path_prefix.$file);
					if ($info !== false) {
						$list[$file] = array_merge($info, array(
								'url' => $url_prefix.$file,
							));
					}
				}
			}
		} else if (($d = opendir($path_prefix))) {
			// Read directory contents and scan all subdirecotries
			while (($file = readdir($d)) !== false) {
				if ($file[0] != '.' && is_dir($path_prefix.$file)) {
					$info = B_gallery__gallery__load::getGalleryInfo($path_prefix.$file);
					if ($info !== false) {
						$list[$file] = array_merge($info, array(
								'url' => $url_prefix.$file,
							));
					}
				}
			}

			closedir($d);
		}

		if (!empty($list)) {
			uksort($list, 'strcoll');

			$this->out('done', true);
			$this->out('list', $list);
		}
	}
};


