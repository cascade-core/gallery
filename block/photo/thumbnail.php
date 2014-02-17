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


class B_gallery__photo__thumbnail extends \Cascade\Core\Block
{

	protected $inputs = array(
		'filename' => array(),
		'size' => 120,
		'gallery_config' => null,
	);

	protected $connections = array(
		'gallery_config' => array('config', 'gallery'),
	);

	protected $outputs = array(
		'thumbnail' => true,
		'done' => true,
	);

	public function main()
	{
		$gallery_config = $this->in('gallery_config');
		$path_prefix = $gallery_config['path_prefix'];
		$filename = $this->in('filename');
		$size = $this->in('size');

		if (is_array($filename)) {
			$filename = $path_prefix.join($filename, '/');
		} else {
			$filename = $path_prefix.$filename;
		}

		// prepare cache file
		$cache_dir = DIR_ROOT.'/var/cache';
		if (!is_dir($cache_dir)) {
			mkdir($cache_dir);
		}
		$cache_fn = md5($filename.'|'.$size);
		$cache_file = $cache_dir.'/'.substr($cache_fn, 0, 2);
		if (!is_dir($cache_file)) {
			mkdir($cache_file);
		}
		$cache_file .= '/'.$cache_fn;

		// update cache if required
		if (!is_readable($cache_file) || filemtime($filename) > filemtime($cache_file) || filemtime(__FILE__) > filemtime($cache_file)) {
			$image = self::generate_thumbnail($filename, $size, $size);
			file_put_contents($cache_file, $image);
		} else {
			// otherwise read image from cache
			$image = file_get_contents($cache_file);
		}
		
		if ($image !== false) {
			$this->out('thumbnail', $image);
			$this->out('done', true);
		}
	}

	public static function generate_thumbnail($filename, $width, $height)
	{
		// Content type
		//header('Content-Type: image/jpeg');

		// Get new dimensions
		$size_orig = getimagesize($filename);
		if ($size_orig === false) {
			return false;
		}
		list($width_orig, $height_orig, $type) = $size_orig;

		$ratio_orig = $width_orig / $height_orig;

		if ($width / $height > $ratio_orig) {
			$width = $height * $ratio_orig;
		} else {
			$height = $width / $ratio_orig;
		}

		// Load source image
		$image = imagecreatefromstring(file_get_contents($filename));
		if ($image === false) {
			return false;
		}

		// Prepare thumbnail image
		$image_p = imagecreatetruecolor($width, $height);
		if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
			$transparent_color = imagecolorallocatealpha($image_p, 32, 32, 32, 0);	// no transparency, use #888; FIXME: configurable background color
			imagecolortransparent($image_p, $transparent_color);
			imagefill($image_p, 0, 0, $transparent_color);
			imagealphablending($image_p, true);
			imagesavealpha($image_p, true);
		}

		// Resample
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

		// Rotate if required
		$exif = exif_read_data($filename);
		$ort = @$exif['Orientation'];
		switch($ort)
		{
			case 1: // nothing
				break;

			case 2: // horizontal flip
				//$image->flipImage($public,1);			// FIXME
				break;

			case 3: // 180 rotate left
				$image_p = imagerotate($image_p, 180, 0);
				break;

			case 4: // vertical flip
				//$image->flipImage($public,2);			// FIXME
				break;

			case 5: // vertical flip + 90 rotate right
				//$image->flipImage($public, 2);		// FIXME
				$image_p = imagerotate($image_p, 270, 0);
				break;

			case 6: // 90 rotate right
				$image_p = imagerotate($image_p, 270, 0);
				break;

			case 7: // horizontal flip + 90 rotate right
				//$image->flipImage($public,1);    		// FIXME
				$image_p = imagerotate($image_p, 270, 0);
				break;

			case 8: // 90 rotate left
				$image_p = imagerotate($image_p, 90, 0);
				break;
		}

		// Result
		ob_start();
		imagejpeg($image_p);
		return ob_get_clean();
	}

};


