<?php
namespace OCA\NotesTutorial\Cron;

use \OCA\NotesTutorial\Service\NoteService;
use \OCP\BackgroundJob\TimedJob;
use \OCP\AppFramework\Utility\ITimeFactory;
use \OCP\Files\IRootFolder;

class SomeTask extends TimedJob {

    private $userId;
    private $myService;
    private $rootFolder;
    private $userFolder;

    public function __construct(ITimeFactory $time, NoteService $service, IRootFolder $rootFolder) {
        parent::__construct($time);
        $this->userId = 'admin';
        $this->myService = $service;
        $this->rootFolder = $rootFolder;
        $this->userFolder = $this->rootFolder->getUserFolder($this->userId);

        // Run once an hour
        parent::setInterval(60);
    }

    protected function run($arguments) {
        // $this->myService->doCron($arguments['uid']);
        $count = 0;
        if ($this->userFolder->nodeExists('/RSFTPOC-cron-count.txt') === FALSE) {
            $file = $this->userFolder->newFile('/RSFTPOC-cron-count.txt');
        } else {
            $file = $this->userFolder->get('/RSFTPOC-cron-count.txt');
            $count = (int)$file->getContent();
        }

        $file->putContent($count);
    }

}