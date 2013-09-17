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

class B_gallery__index__load extends Block {

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


