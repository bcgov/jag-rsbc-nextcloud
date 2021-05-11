<?php

namespace OCA\NotesTutorial\Controller;

use OCP\AppFramework\Controller;
use OCP\Files\IAppData;
use OCP\IRequest;

class FileController extends Controller {
    /** @var IAppData */
    private $appData;

    public function __construct($appName,
                                IRequest $request,
                                IAppData $appData) {
        parent::__construct($appName, $request);
        $this->appData = $appData;
    }

    public function upload($name) {
        $this->appData.newFolder($name);
    }
}