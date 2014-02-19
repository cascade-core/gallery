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


class B_gallery__photo__show extends \Cascade\Core\Block
{

	protected $inputs = array(
		'image_file' => array(),
		'slot' => 'default',
		'slot_weight' => 50,
	);

	protected $outputs = array(
	);

	const force_exec = true;

	public function main()
	{
		$image_file = $this->in('image_file');
		$this->templateSetType('jpeg');
		$this->templateAddToSlot(null, 'root', 1, 'gallery/image', array('image_file' => $image_file));
	}

};


