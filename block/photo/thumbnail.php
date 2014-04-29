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
		'gallery' => null,
		'path' => null,
		'size' => 160,
		'gallery_config' => null,
	);

	protected $connections = array(
		'gallery_config' => array('config', 'gallery'),
	);

	protected $outputs = array(
		'thumbnail_file' => true,
		'done' => true,
	);

	public function main()
	{
		$gallery_config = $this->in('gallery_config');
		$path_prefix = $gallery_config['path_prefix'];
		$gallery = $this->in('gallery');
		$path = $this->in('path');
		$size = $this->in('size');

		$filename = $path_prefix.$gallery.'/';
		if (is_array($path)) {
			$filename .= join($path, '/');
		} else {
			$filename .= $path;
		}

		// prepare cache file
		$cache_dir = DIR_ROOT.'var/cache';
		if (!is_dir($cache_dir)) {
			mkdir($cache_dir);
		}
		$cache_fn = md5($filename.'|'.$size.'|'.$gallery_config['resize_mode']);
		$cache_file = $cache_dir.'/'.substr($cache_fn, 0, 2);
		if (!is_dir($cache_file)) {
			mkdir($cache_file);
		}
		$cache_file .= '/'.$cache_fn;

		// update cache if required
		if (!is_readable($cache_file) || filemtime($filename) > filemtime($cache_file) || filemtime(__FILE__) > filemtime($cache_file)) {
			self::generateThumbnail($cache_file, $filename, $size, $size, $gallery_config['resize_mode']);
		}
		if ($cache_file !== false) {
			$this->out('thumbnail_file', $cache_file);
			$this->out('done', true);
		}
	}


	public static function calculateThumbnailSize($w_orig, $h_orig, $ort, $resize_mode, $w_dst, $h_dst)
	{
		list($w_dst, $h_dst, $w_src, $h_src, $x_src, $y_src, $needs_rotation, $needs_flip)
			= self::calculateTransformParameters($w_orig, $h_orig, $ort, $resize_mode, $w_dst, $h_dst);

		if ($needs_rotation == 90 || $needs_rotation == 270) {
			return array($h_dst, $w_dst);
		} else {
			return array($w_dst, $h_dst);
		}
	}


	private static function calculateTransformParameters($w_orig, $h_orig, $ort, $resize_mode, $w_dst, $h_dst)
	{
		//debug_dump(get_defined_vars());
		$x_src = 0;
		$y_src = 0;
		$w_src = $w_orig;
		$h_src = $h_orig;
		$ratio_orig = $w_orig / $h_orig;
		$ratio_dst  = $w_dst / $h_dst;

		// If rotate is required, swap w_dst and h_dst
		switch($ort) {

			// No rotation
			case 0: // missing info
			case 1: // nothing
			case 2: // horizontal flip
			case 3: // 180 rotate left
			case 4: // vertical flip
				switch ($resize_mode) {

					default:
					case 'fit':
						if ($ratio_orig > $ratio_dst) {
							// original is wider -- reduce height
							$h_dst = $w_dst / $ratio_orig;
						} else {
							// original is taller -- reduce width
							$w_dst = $h_dst * $ratio_orig;
						}
						break;

					case 'fill':
						if ($ratio_orig > $ratio_dst) {
							// original is wider -- crop sides
							$w_src = $h_src * $ratio_dst;
							$x_src = ($w_orig - $w_src) / 2;
						} else {
							// original is taller -- crop top & bottom
							$h_src = $w_src / $ratio_dst;
							$y_src = ($h_orig - $h_src) / 2;
						}
						break;

					case 'same_height':
						// height is given -- calculate width
						$w_dst = $h_dst * $ratio_orig;
						break;
				}
				break;

			// With rotation
			case 5: // vertical flip + 90 rotate right
			case 6: // 90 rotate right
			case 7: // horizontal flip + 90 rotate right
			case 8: // 90 rotate left
				switch ($resize_mode) {

					default:
					case 'fit':
						if ($ratio_orig > $ratio_dst) {
							// original is wider -- reduce height
							$h_dst = $w_dst;
							$w_dst = $h_dst * $ratio_orig;
						} else {
							// original is taller -- reduce width
							$w_dst = $h_dst;
							$h_dst = $w_dst / $ratio_orig;
						}
						break;

					case 'fill':
						if ($ratio_orig > $ratio_dst) {
							// original is wider -- crop sides
							$w_src = $h_src;
							$h_src = $w_src * $ratio_dst;
							$x_src = ($w_orig - $w_src) / 2;
						} else {
							// original is taller -- crop top & bottom
							$h_src = $w_src;
							$w_src = $h_src / $ratio_dst;
							$y_src = ($h_orig - $h_src) / 2;
						}
						$x = $h_dst;
						$h_dst = $w_dst;
						$w_dst = $x;
						break;

					case 'same_height':
						// height is given -- calculate width
						$w_dst = $h_dst;
						$h_dst = $w_dst / $ratio_orig;
						break;
				}
				break;
		}

		// Rotation & flip
		switch($ort)
		{
			default:
			case 1: // nothing
				$needs_rotation = 0;
				$needs_flip     = null;
				break;

			case 2: // horizontal flip
				$needs_rotation = 0;
				$needs_flip     = 'h';
				break;

			case 3: // 180 rotate left
				$needs_rotation = 180;
				$needs_flip     = null;
				break;

			case 4: // vertical flip
				$needs_rotation = 0;
				$needs_flip     = 'v';
				break;

			case 5: // vertical flip + 90 rotate right
				$needs_rotation = 270;
				$needs_flip     = 'v';
				break;

			case 6: // 90 rotate right
				$needs_rotation = 270;
				$needs_flip     = null;
				break;

			case 7: // horizontal flip + 90 rotate right
				$needs_rotation = 270;
				$needs_flip     = 'h';
				break;

			case 8: // 90 rotate left
				$needs_rotation = 90;
				$needs_flip     = null;
				break;
		}

		return array($w_dst, $h_dst, $w_src, $h_src, $x_src, $y_src, $needs_rotation, $needs_flip);
	}


	public static function generateThumbnail($target_file, $filename, $width, $height, $resize_mode)
	{
		// Content type
		//header('Content-Type: image/jpeg');

		// Get new dimensions
		$size_orig = getimagesize($filename);
		if ($size_orig === false) {
			return false;
		}
		list($w_orig, $h_orig, $type) = $size_orig;

		$exif = exif_read_data($filename);
		$ort = @$exif['Orientation'];

		list($w_dst, $h_dst, $w_src, $h_src, $x_src, $y_src, $needs_rotation, $needs_flip)
			= self::calculateTransformParameters($w_orig, $h_orig, $ort, $resize_mode, $width, $height);

		// Load source image
		$image = imagecreatefromstring(file_get_contents($filename));
		if ($image === false) {
			return false;
		}

		// Prepare thumbnail image
		$image_p = imagecreatetruecolor($w_dst, $h_dst);
		if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
			$transparent_color = imagecolorallocatealpha($image_p, 32, 32, 32, 0);	// no transparency, use #888; FIXME: configurable background color
			imagecolortransparent($image_p, $transparent_color);
			imagefill($image_p, 0, 0, $transparent_color);
			imagealphablending($image_p, true);
			imagesavealpha($image_p, true);
		}

		// Resample
		imagecopyresampled($image_p, $image, 0, 0, $x_src, $y_src, $w_dst, $h_dst, $w_src, $h_src);

		// Flip if required
		if ($needs_flip) {
			//$image_p = imageflip($image_p, $needs_flip);	// FIXME
		}

		// Rotate if required
		if ($needs_rotation) {
			$image_p = imagerotate($image_p, $needs_rotation, 0);
		}

		// Result
		return imagejpeg($image_p, $target_file, 92);
	}

};


