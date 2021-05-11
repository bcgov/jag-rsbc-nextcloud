<?php

namespace OCA\NotesTutorial\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Settings extends Entity implements JsonSerializable {
	protected $orgName;
	protected $draftFolderPath;

	public function __construct($orgName, $draftFolderPath, $readyFolderPath) {
		$this->orgName = $orgName;
		$this->draftFolderPath = $draftFolderPath;
		$this->readyFolderPath = $readyFolderPath;
	}

	public function jsonSerialize(): array {
		return [
			'orgName' => $this->orgName,
			'draftFolderPath' => $this->draftFolderPath,
			'readyFolderPath' => $this->readyFolderPath
		];
	}
}
