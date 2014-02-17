<?php
/*
 * Copyright (c) 2014, Josef Kufner  <jk@frozen-doe.net>
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

class B_gallery__route_postprocessor extends \Cascade\Core\Block
{

	protected $inputs = array(
		'gallery_config' => null,
	);

	protected $connections = array(
		'gallery_config' => array('config', 'gallery'),
	);

	protected $outputs = array(
		'postprocessor' => true,
		'done' => true,
	);


	public function main()
	{
		$this->out('postprocessor', array($this, 'routePostprocess'));
		$this->out('done', true);
	}

	public function routePostprocess($outputs)
	{
		$gallery_config = $this->in('gallery_config');
		$base_dir = $gallery_config['path_prefix'].str_replace('/', '_', $outputs['gallery']);

		// abort if gallery does not exist
		if (!is_dir($base_dir)) {
			return false;
		}

		// gallery root
		if (empty($outputs['path_tail'])) {
			return $outputs;
		}

		$full_path = $base_dir.'/'.join('/', (array) $outputs['path_tail']);

		// check for gallery folders
		if (is_dir($full_path)) {
			return $outputs;
		}

		// check for file
		if (is_file($full_path)) {
			// FIXME
			header('Content-type: image/'.preg_replace('/.*\./', '', $full_path));
			readfile($full_path);
			exit;
		}
		debug_dump($full_path);
		debug_dump($outputs, __METHOD__);

		return $outputs;
	}

}

