<?php

namespace OCA\NotesTutorial\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class NoteFile extends Entity implements JsonSerializable {
	protected $name;
	protected $size;

	public function __construct($name, $size) {
		$this->name = $name;
		$this->size = $size;
	}

	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'size' => $this->size
		];
	}
}
