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

class B_gallery__breadcrumbs extends \Cascade\Core\Block
{

	protected $inputs = array(
		'path' => null,
		'slot' => 'default',
		'slot_weight' => 50,
	);

	protected $connections = array(
		'path' => array('router', 'path'),
	);

	protected $outputs = array(
	);

	const force_exec = true;

	public function main()
	{
		$path = $this->in('path');

		if (!is_array($path)) {
			$path = $path == '/' ? array() : explode('/', trim($path, '/'));
		}

		$breadcrumbs = array();

		for ($i = count($path); $i > 0; $i--) {
			$breadcrumbs[] = array(
				'label' => end($path),
				'url' => '/'.join('/', $path),
			);
			array_pop($path);
		}

		if (!empty($breadcrumbs)) {
			$this->templateAdd(null, 'gallery/breadcrumbs', array(
				'breadcrumbs' => array_reverse($breadcrumbs),
			));
		}
	}

}

