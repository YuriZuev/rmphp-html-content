<?php

namespace Rmphp\Content;


class ContentData {

	private array $data;

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __get($name) {
		return $this->data[$name] ?? null;
	}
}