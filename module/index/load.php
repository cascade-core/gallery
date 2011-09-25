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

class M_gallery__index__load extends Module {

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
		if ($directory == '') {
			$directory = './';
		}

		if (($d = opendir($directory))) {
			while (($file = readdir($d)) !== false) {
				if ($directory == './') {
					$full_name = $file;
				} else {
					$full_name = $directory.$file;
				}
				if ($file[0] != '.' && is_dir($full_name)) {
					if (preg_match_all('/^([0-9-]+)( ?[0-9]\+)?[ -.]+(.+)$/', $file, $matches)) {
						$list[$file] = array(
							'filename' => $file,
							'path' => $path_prefix.$full_name,
							'url' => $url_prefix.$full_name,
							'date' => $matches[2][0] != '' 
									? strftime('%Y-%m-%d %H:%M:%S', strtotime($matches[1][0]).' '.$matches[2][0])
									: strftime('%Y-%m-%d', strtotime($matches[1][0])),
							'title' => str_replace('_', ' ', $matches[3][0]),
							'mtime' => strftime('%Y-%m-%d %H:%M:%S', filemtime($full_name)),
						);
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


