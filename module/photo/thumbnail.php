<?php
/*
 * Copyright (c) 2011, Josef Kufner  <jk@frozen-doe.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the author nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

class M_gallery__photo__thumbnail extends Module
{

	protected $inputs = array(
		'path_prefix' => './',
		'filename' => array(),
		'size' => 120,
	);

	protected $outputs = array(
		'thumbnail' => true,
		'done' => true,
	);

	public function main()
	{
		$path_prefix = $this->in('path_prefix');
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
		list($width_orig, $height_orig) = $size_orig;

		$ratio_orig = $width_orig / $height_orig;

		if ($width / $height > $ratio_orig) {
			$width = $height * $ratio_orig;
		} else {
			$height = $width / $ratio_orig;
		}

		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		$image = imagecreatefromjpeg($filename);
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
		imagejpeg($image_p, null, 80);
		return ob_get_clean();
	}

};


