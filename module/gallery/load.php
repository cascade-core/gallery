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

class M_gallery__gallery__load extends Module {

	protected $inputs = array(
		'directory' => '.',
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

		$gallery_info = self::get_gallery_info($path_prefix.$directory);

		if ($gallery_info !== false && ($d = opendir($path_prefix.$directory))) {
			while (($file = readdir($d)) !== false) {
				if ($directory == './') {
					$full_name = $file;
				} else {
					$full_name = $directory.$file;
				}
				if ($file[0] != '.') {
					if (preg_match('/\.jpe?g|\.png|\.gif|\.tiff/i', $file)) {
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


	public static function get_gallery_info($dir)
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


