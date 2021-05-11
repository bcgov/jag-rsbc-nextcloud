<?php

namespace OCA\NotesTutorial\Controller;

use OCA\NotesTutorial\AppInfo\Application;
// use OCA\NotesTutorial\Service\NoteService;
use OCA\NotesTutorial\Db\Note;
use OCA\NotesTutorial\Db\NoteFile;
use OCA\NotesTutorial\Db\Settings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\IRequest;
use OCP\AppFramework\Db\Entity;
use \DateTime;
use \DateTimeZone;

class NoteController extends Controller {
	/** @var NoteService */
	private $service;

	/** @var string */
	private $userId;

    /** @var IAppData */
    private $appData;

	/** @var IRootFolder */
	private $rootFolder;

	private $userFolder;

	private $orgName;

	private $draftFolderPath;

	private $readyFolderPath;

	use Errors;

	public function __construct(IRequest $request,
								// NoteService $service,
								IAppData $appData,
								IRootFolder $rootFolder,
								$userId) {
		parent::__construct(Application::APP_ID, $request);
		// $this->service = $service;
		$this->userId = $userId;
		$this->appData = $appData;
		$this->rootFolder = $rootFolder;
		$this->userFolder = $rootFolder->getUserFolder($userId);

		$this->loadUserSettings();
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		//return new DataResponse($this->service->findAll($this->userId));

		// $a = array();

		// $n = new Note();

		// $n->setId(1);
		// $n->setFormno('ABC');

		// array_push($a, $n);

		// $n = new Note();

		// $n->setId(2);
		// $n->setFormno('123');
		// $n->setReady(true);

		// array_push($a, $n);

		// $n = new Note();

		// $n->setId(2);
		// $n->setFormno('456');
		// $n->setReady(true);

		// array_push($a, $n);

		// $n = new Note();

		// $n->setId(2);
		// $n->setFormno('998');
		// $n->setReady(true);

		// array_push($a, $n);

		// https://help.nextcloud.com/t/how-create-a-folder-file-in-nextcloud-with-php/85409/2
		$userFolder = $this->rootFolder->getUserFolder($this->userId);

		$a = array();

		try {
			if ($userFolder->nodeExists($this->draftFolderPath) === FALSE) {
				$draftFolder = $userFolder->newFolder($this->draftFolderPath);
			} else {
				$draftFolder = $userFolder->get($this->draftFolderPath);
			}
			foreach ($draftFolder->getDirectoryListing() as $folder) {
				$n = new Note();
				$n->setId($folder->getId());
				$n->setFormno($folder->getName());

				$f = $folder->get('metadata.txt');
				// strtok($f->getContent(),':');
				// strtok(':')

				$pages = trim(strtok($f->getContent(),','));

				if ($pages === '') {
					$pages = trim($f->getContent());
				}

				$n->setPoliceno($pages);


				$n->setReady(false);
				array_push($a, $n);
			}
		} catch (\OCP\Files\NotFoundException $e) {
		}

		try {
			$readyFolderPath = $this->readyFolderPath;
			if ($userFolder->nodeExists($readyFolderPath) === FALSE) {
				$readyFolder = $userFolder->newFolder($readyFolderPath);
			} else {
				$readyFolder = $userFolder->get($readyFolderPath);
			}
			foreach ($readyFolder->getDirectoryListing() as $folder) {
				$n = new Note();
				// $n->setId($folder->getId());
				$n->setId(13);
				$n->setFormno($folder->getName());
				$n->setPoliceno('0');
				$n->setReady(true);
				array_push($a, $n);
			}
		} catch (\OCP\Files\NotFoundException $e) {
		}

		return new DataResponse($a);
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		// return $this->handleNotFound(function () use ($id) {
		// 	return $this->service->find($id, $this->userId);
		// });

		// https://help.nextcloud.com/t/how-create-a-folder-file-in-nextcloud-with-php/85409/2
		// $userFolder = $this->rootFolder->getUserFolder($this->userId);

		$n = null;

		try {
			$folder = $this->rootFolder->getById($id)[0]->getParent();
			$n = new Note();
			$n->setId($id);
			$n->setFormno($folder->getName());
			$n->setPoliceno('333');;
			$n->setReady(strpos($folder->getPath(), $this->readyFolderPath, 0));
		} catch (\OCP\Files\NotFoundException $e) {
		}

		return new DataResponse($n);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, string $content,
		string $to, string $formno, string $agency, string $policeno, string $policeemail, string $packagetype): DataResponse {

		// create folder
		//$this->appData->newFolder($title);
		
		//$policeemail = sizeof($this->appData->getDirectoryListing()) + 100;

		//$this->appData->newFolder('app-settings');

		//$policeno = Folder::getUserFolder($this->userid)->newFolder($formno);
		//$this->rootFolder->newFolder($formno);

		// https://help.nextcloud.com/t/how-create-a-folder-file-in-nextcloud-with-php/85409/2
		$userFolder = $this->rootFolder->getUserFolder($this->userId);

		try {
			try {
				$folderPath = $this->draftFolderPath . $formno;
				if ($userFolder->nodeExists($folderPath) === FALSE) {
					$folder = $userFolder->newFolder($folderPath);

					if ($folder->nodeExists('metadata.txt') === FALSE) {
						$file = $folder->newFile('metadata.txt');
					} else {
						$file = $folder->get('metadata.txt');
					}
				} else {
					throw new NotPermittedException ('Already exists');
				}
			} catch (\OCP\Files\NotFoundException $e) {
			}

			// the id can be accessed by $file->getId();
			$file->putContent('' . $policeno);
		} catch(\OCP\Files\NotPermittedException $e) {
			// you have to create this exception by yourself
		}
		
		// return new DataResponse($this->service->create($title, $content,
		// 	$this->userId,
		// 	$to, $formno, $agency, $policeno, $policeemail, $packagetype));
		$n = new Note();
		$n->setId($folder->getId());
		$n->setFormno($formno);

		return new DataResponse($n);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title, string $content,
		string $to, string $formno, string $agency, string $policeno, string $policeemail, string $packagetype): DataResponse {

		// https://help.nextcloud.com/t/how-create-a-folder-file-in-nextcloud-with-php/85409/2
		$userFolder = $this->rootFolder->getUserFolder($this->userId);

		try {
			try {
				if (empty($policeemail)) {
					// update
					$folderPath = $this->draftFolderPath . $formno;
					
					$folder = $userFolder->get($folderPath);
					
					if ($folder->nodeExists('metadata.txt') === FALSE) {
						$file = $folder->newFile('metadata.txt');
					} else {
						$file = $folder->get('metadata.txt');
					}

					// the id can be accessed by $file->getId();
					$file->putContent('' . $policeno);
				} else {
					// move
					$folderPath = $this->draftFolderPath . $formno;

					if ($userFolder->nodeExists($this->readyFolderPath) === TRUE) {
						$readyFolderFullPath = $userFolder->get($this->readyFolderPath)->getPath();

						$folder = $userFolder->get($folderPath);

						// add date/time stamp
						if ($folder->nodeExists('metadata.txt') === TRUE) {
							$datetime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
							//$datetimestring = $datetime->format("Y-m-d\Th:i:sP");  -- date and time
							$datetimestring = $datetime->format("Y-m-d");

							$file = $folder->get('metadata.txt');

							// $content = $file->getContent();

							$orgName = "orgname:" . $this->orgName;

							$uniqid_true = uniqid("",true);
							$uniqid_false = uniqid("",false);

							$content = $policeno . "," 
								. $this->orgName . ","
								. $datetimestring;

							$file->putContent($content);
						} else {
							throw new NotFoundException("Not found");
						}
	
						$folder->move($readyFolderFullPath . '/' . $formno);
					} else {
						throw new NotFoundException("Not found");
					}
				}
			} catch (\OCP\Files\NotFoundException $e) {
			}
		} catch(\OCP\Files\NotPermittedException $e) {
			// you have to create this exception by yourself
		}

		// return $this->handleNotFound(function () use ($id, $title, $content, $to, $formno, $agency, $policeno, $policeemail, $packagetype) {
		// 	return $this->service->update($id, $title, $content, $this->userId,
		// 		$to, $formno, $agency, $policeno, $policeemail, $packagetype);
		// });
		$n = new Note();
		$n->setId($folder->getId());
		$n->setFormno($formno);

		return new DataResponse($n);
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		// return $this->handleNotFound(function () use ($id) {
		// 	return $this->service->delete($id, $this->userId);
		// });

		$folder = $this->rootFolder->getById($id)[0];
		$folder->delete();

		return new DataResponse(new Note());

	}

    public function uploadfile(string $id, string $name, string $data): DataResponse {
        $folder = $this->rootFolder->getById($id)[0];
		$file = $folder->newFile($name);

		// extract base64 encoded file content
		strtok($data,',');
		$base64 = strtok(',');
		$file->putContent(base64_decode($base64));

		return new DataResponse(new NoteFile($name, 0));
    }

	public function deletefile(string $id, string $name): DataResponse {
		$folder = $this->rootFolder->getById($id)[0];
		$file = $folder->search($name)[0];
		$file->delete();

		return new DataResponse(new NoteFile($name, 0));
	}

	public function listfiles(string $id): DataResponse {
		$folder = $this->rootFolder->getById($id)[0];
		
		$a = array();

		foreach ($folder->getDirectoryListing() as $f) {
			if ($f->getName() != 'metadata.txt') {
				$nf = new NoteFile($f->getName(), $f->getSize());
				array_push($a, $nf);
			}
		}
		
		return new DataResponse($a);
	}

	protected function loadUserSettings() {
		$this->util_assertFolderCreated('/RSFTPOC/Settings');
		$settingsfolder = $this->userFolder->get('/RSFTPOC/Settings');

		if ($settingsfolder->nodeExists('orgName.txt') === TRUE) {
			$file = $settingsfolder->get('orgName.txt');
			$this->orgName = $file->getContent();
		}

		if ($settingsfolder->nodeExists('draftFolderPath.txt') === TRUE) {
			$file = $settingsfolder->get('draftFolderPath.txt');
			$this->draftFolderPath = $file->getContent();
		}

		if ($settingsfolder->nodeExists('readyFolderPath.txt') === TRUE) {
			$file = $settingsfolder->get('readyFolderPath.txt');
			$this->readyFolderPath = $file->getContent();
		}
	}

	protected function saveUserSettings() {
		$this->util_assertFolderCreated('/RSFTPOC/Settings');

		$settingsfolder = $this->userFolder->get('/RSFTPOC/Settings');

		$file = $settingsfolder->newFile('orgName.txt');
		$file->putContent($this->orgName);

		$file = $settingsfolder->newFile('draftFolderPath.txt');
		$file->putContent($this->draftFolderPath);

		$file = $settingsfolder->newFile('readyFolderPath.txt');
		$file->putContent($this->readyFolderPath);
	}

	protected function util_assertBeginsEndsWithSlash(string $s): string {
		if (substr($s,0,1) == '/') {
			$prefix = '';
		} else {
			$prefix = '/';
		}

		if (substr($s,-1) == '/') {
			$suffix = '';
		} else {
			$suffix = '/';
		}

		return $prefix . $s . $suffix;
	}

	protected function util_assertFolderCreated(string $path) {
		$tok = strtok($this->util_assertBeginsEndsWithSlash($path),'/');

		$fullpath = '';

		while ($tok !== FALSE) {
			$fullpath = $fullpath . '/' . $tok;
			if ($this->userFolder->nodeExists($fullpath) == FALSE) {
				$this->userFolder->newFolder($fullpath);
			}

			$tok = strtok('/');
		}
	}

	public function uploadSettings(string $orgName, string $draftFolderPath, string $readyFolderPath): DataResponse {
		$this->orgName = $orgName;

		$this->draftFolderPath = $this->util_assertBeginsEndsWithSlash($draftFolderPath);
		
		$this->util_assertFolderCreated($this->draftFolderPath);
		
		$this->readyFolderPath = $this->util_assertBeginsEndsWithSlash($readyFolderPath);

		$this->util_assertFolderCreated($this->readyFolderPath);

		$this->saveUserSettings();

		return new DataResponse();
	}

	public function downloadSettings(): DataResponse {
		return new DataResponse(new Settings(
			$this->orgName,
			$this->draftFolderPath,
			$this->readyFolderPath));
	}
}
