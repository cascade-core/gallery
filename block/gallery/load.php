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
		$url_thumbnail_ext = $gallery_config['url_thumbnail_ext'];

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
					$fs_full_name = $path_prefix.$full_name;
					if (preg_match('/(\.jpe?g|\.png|\.gif|\.tiff)$/i', $file)) {
						// get metadata
						$exif = @ exif_read_data($fs_full_name);
						//debug_dump($exif);
						if ($exif) {
							$location = $this->exifToLocation($exif);
							if (isset($exif['COMPUTED']['Width']) && isset($exif['COMPUTED']['Height'])) {
								$width = $exif['COMPUTED']['Width'];
								$height = $exif['COMPUTED']['Height'];
							} else {
								@ list($width, $height) = getimagesize($fs_full_name);
							}
							$orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 0;
						} else {
							@ list($width, $height) = getimagesize($fs_full_name);
							$orientation = 0;
						}

						list($tb_width, $tb_height) = B_gallery__photo__thumbnail::calculateThumbnailSize($width, $height, $orientation,
								$gallery_config['resize_mode'], $gallery_config['thumbnail_size'], $gallery_config['thumbnail_size']);

						// store item
						$list[$file] = array(
							'filename' => $file,
							'path' => $fs_full_name,
							'url' => $url_prefix.$full_name,
							'tb_url' => $url_prefix.$full_name.$url_thumbnail_ext,
							'location' => $location,
							'width' => $width,
							'height' => $height,
							'tb_width' => $tb_width,
							'tb_height' => $tb_height,
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

			//debug_dump($list);

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


	private function exifToLocation($exif)
	{
		if (empty($exif)) {
			return null;
		}
		if (isset($exif["GPSLongitude"]) && isset($exif['GPSLongitudeRef'])
			&& isset($exif["GPSLatitude"]) && isset($exif['GPSLatitudeRef']))
		{
			$lon = $this->exifCoordToDecimal($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
			$lat = $this->exifCoordToDecimal($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
			if (isset($exif['GPSAltitude'])) {
				$alt = $this->exifNumberToFloat($exif['GPSAltitude']);
			} else {
				$alt = null;
			}
		} else {
			$lon = null;
			$lat = null;
			$alt = null;
		}
		return array($lon, $lat, $alt);
	}


	private function exifCoordToDecimal($coord, $ref)
	{
		$unit = 1;
		$val = 0;
		foreach ($coord as $c) {
			$val += $this->exifNumberToFloat($c) / $unit;
			$unit *= 60;
		}
		return ($ref == 'W' || $ref == 'S') ? - $val : $val;
	}


	private function exifNumberToFloat($str)
	{
		@ list($a, $b) = explode('/', $str);
		if (!$b) {
			return $a ? $a : null;
		}
		return (float) $a / (float) $b;
	}

};


