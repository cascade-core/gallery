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
		'directory' => '.',
		'subdirectory' => null,
		'path_prefix' => './',
		'url_prefix' => '/',
		'url_thumbnail_prefix' => '/thumbnail/',
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
		$directory = trim($this->in('directory'), '/').'/';
		$path_prefix = $this->in('path_prefix');
		$url_prefix  = $this->in('url_prefix');
		$url_thumbnail_prefix  = $this->in('url_thumbnail_prefix');

		$list = array();
		$others = array();

		$directory = preg_replace('/\.\+\//', '', $directory);

		$gallery_info = self::getGalleryInfo($path_prefix.$directory);

		$subdirectory = $this->in('subdirectory');
		if (!empty($subdirectory)) {
			if (is_array($subdirectory)) {
				$subdirectory = join('/', $subdirectory);
			} else {
				$subdirectory = trim($subdirectory, '/');
			}
			$directory .= $subdirectory.'/';
		}

		if ($gallery_info !== false && ($d = opendir($path_prefix.$directory))) {
			while (($file = readdir($d)) !== false) {
				if ($directory == './') {
					$full_name = $file;
				} else {
					$full_name = $directory.$file;
				}
				if ($file[0] != '.') {
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


