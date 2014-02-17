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


class B_gallery__gallery__load extends \Cascade\Core\Block {

	protected $inputs = array(
		'gallery' => null,
		'path' => null,			// path within gallery
		'gallery_config' => null,
	);

	protected $connections = array(
		'gallery_config' => array('config', 'gallery'),
	);

	protected $outputs = array(
		'title' => true,
		'info' => true,
		'list' => true,
		'others' => true,
		'done' => true,
	);


	public function main()
	{
		$directory = $this->in('gallery');
		$gallery_config = $this->in('gallery_config');

		$path_prefix = $gallery_config['path_prefix'];
		$url_prefix  = $gallery_config['url_prefix'];
		$url_thumbnail_prefix = $gallery_config['url_thumbnail_prefix'];

		$list = array();
		$others = array();

		$directory = str_replace('/', '_', $directory);

		$gallery_info = self::getGalleryInfo($path_prefix.$directory);

		$subdirectory = $this->in('path');
		if (!empty($subdirectory)) {
			if (is_array($subdirectory)) {
				$subdirectory = join('/', $subdirectory);
			} else {
				$subdirectory = trim($subdirectory, '/');
			}
			$directory .= '/'.$subdirectory.'/';
		} else {
			$directory .= '/';
		}

		if ($gallery_info !== false && ($d = opendir($path_prefix.$directory))) {
			while (($file = readdir($d)) !== false) {
				if ($file[0] != '.') {
					$full_name = $directory.$file;
					if (preg_match('/(\.jpe?g|\.png|\.gif|\.tiff)$/i', $file)) {
						$list[$file] = array(
							'filename' => $file,
							'path' => $path_prefix.$full_name,
							'url' => $url_prefix.$full_name,
							'tb_url' => $url_thumbnail_prefix.$full_name,
						);
					} else {
						$others[$file] = array(
							'title' => $file,
							'link' => $url_prefix.$full_name,
						);
					}
				}
			}

			closedir($d);
			uksort($list, 'strcoll');
			uksort($others, 'strcoll');

			$this->out('title', $gallery_info['title']);
			$this->out('info', $gallery_info);
			$this->out('list', $list);
			$this->out('others', $others);
			$this->out('done', true);
		}
	}


	public static function getGalleryInfo($dir)
	{
		$file = basename(rtrim($dir, '/'));
		if (preg_match_all('/^([0-9-]+)( ?[0-9]\+)?[ -.]+(.+)$/', $file, $matches)) {
			return array(
				'filename' => $file,
				'path' => $dir,
				'date' => $matches[2][0] != '' 
						? strftime('%Y-%m-%d %H:%M:%S', strtotime($matches[1][0]).' '.$matches[2][0])
						: strftime('%Y-%m-%d', strtotime($matches[1][0])),
				'title' => str_replace('_', ' ', $matches[3][0]),
				'mtime' => strftime('%Y-%m-%d %H:%M:%S', filemtime($dir)),
			);
		} else {
			return false;
		}
	}
};


