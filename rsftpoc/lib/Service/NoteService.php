<?php

namespace OCA\NotesTutorial\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCP\Files\Folder;

use OCA\NotesTutorial\Db\Note;
use OCA\NotesTutorial\Db\NoteMapper;

class NoteService {

	/** @var NoteMapper */
	private $mapper;

	public function __construct(NoteMapper $mapper) {
		$this->mapper = $mapper;
	}

	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new NoteNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find($id, $userId) {
		try {
			return $this->mapper->find($id, $userId);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create($title, $content, $userId, $to, $formno, $agency, $policeno, $policeemail, $packagetype) {
		//$f = Folder::newFolder('test folder');

		

		$note = new Note();
		$note->setTitle($title);
		//$note->setContent($f::getDirectoryListing()[0].getPath());
		$note->setContent($content);
		$note->setUserId($userId);
		$note->setTo($to);
		$note->setFormno($formno);
		$note->setAgency($agency);
		$note->setPoliceno($policeno);
		$note->setPoliceemail($policeemail);
		$note->setPackagetype($packagetype);
		return $this->mapper->insert($note);
	}

	public function update($id, $title, $content, $userId, $to, $formno, $agency, $policeno, $policeemail, $packagetype) {
		try {
			$note = $this->mapper->find($id, $userId);
			$note->setTitle($title);
			$note->setContent($content);
			$note->setTo($to);
			$note->setFormno($formno);
			$note->setAgency($agency);
			$note->setPoliceno($policeno);
			$note->setPoliceemail($policeemail);
			$note->setPackagetype($packagetype);
			return $this->mapper->update($note);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete($id, $userId) {
		try {
			$note = $this->mapper->find($id, $userId);
			$this->mapper->delete($note);
			return $note;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
