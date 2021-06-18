<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Artem Sidorenko <artem@posteo.de>
 * @author Christopher Schäpers <kondou@ts.unde.re>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Damjan Georgievski <gdamjan@gmail.com>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Ko- <k.stoffelen@cs.ru.nl>
 * @author Michael Kuhn <michael@ikkoku.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Oliver Kohl D.Sc. <oliver@kohl.bz>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Steffen Lindner <mail@steffen-lindner.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

require_once __DIR__ . '/lib/versioncheck.php';

try {
	require_once __DIR__ . '/lib/base.php';

	if (\OCP\Util::needUpgrade()) {
		\OC::$server->getLogger()->debug('Update required, skipping cron', ['app' => 'cron']);
		exit;
	}
	if ((bool) \OC::$server->getSystemConfig()->getValue('maintenance', false)) {
		\OC::$server->getLogger()->debug('We are in maintenance mode, skipping cron', ['app' => 'cron']);
		exit;
	}

	// load all apps to get all api routes properly setup
	OC_App::loadApps();

	\OC::$server->getSession()->close();

	// initialize a dummy memory session
	$session = new \OC\Session\Memory('');
	$cryptoWrapper = \OC::$server->getSessionCryptoWrapper();
	$session = $cryptoWrapper->wrapSession($session);
	\OC::$server->setSession($session);

	$logger = \OC::$server->getLogger();
	$config = \OC::$server->getConfig();

	// Don't do anything if Nextcloud has not been installed
	if (!$config->getSystemValue('installed', false)) {
		exit(0);
	}

	// ===========================================================================
	// RSFT POC - Start
    // - For each $folder in '$userFolder/Police Ready for Pick-up'
    //     - Acquire unique id $uniqid
    //     - Create  folder '/S3/DPS Ready/$uniqid (package name)'
    //     - Move $folder to '/S3/DPS Ready/$uniqid (package name)/Received'
    //     - Create XML file '/S3/DPS Ready/$uniqid (package name)/$uniqid.xml'
    //     - For every pdf file in the source folder,
    //         - Add file name to XML file
    //         - Copy file to '/DPS'
    //     - Copy $uniqid.xml to '/DPS'
    //     - Copy $uniqid.xml to '/DPS/Control'
    //     - Move '/Storage/DPS Ready/$uniqid (package name)' to '/Storage/DPS Sent'
	try {
		// DEV DPS
		$DPSserver = 'not set';
		$DPSpasswd = 'not set';

		// TEST DPS
		// $DPSserver = 'not set'; -- test
		// $DPSpasswd = 'not set'; -- test

		$user = 'keycloak-a5bb4bda-427b-4efc-9d48-c102fc285b74';
		$userFolder = \OC::$server->getUserFolder($user);
		$S3ReadyFolder = $userFolder->get('/S3/Ready');
		// $S3ReadyFolder = $userFolder->get('/temp-S3/DPS Ready');
		$S3ReadyFolderPath = $S3ReadyFolder->getPath();
		$S3SentFolder = $userFolder->get('/S3/Sent');
		// $S3SentFolder = $userFolder->get('/temp-S3/DPS Sent');
		$S3SentFolderPath = $S3SentFolder->getPath();
		$DPSFolder = $userFolder->get('/DPS');
		// $DPSFolder = $userFolder->get('/temp-DPS');
		$DPSFolderPath = $DPSFolder->getPath();
		$DPSControlFolderPath = $DPSFolderPath . '/Control';
		// $newFile = $userFolder->newFile(uniqid('test-',false) . '.txt');
		// $content = "uniqid = " . $uniqid;
		$policeFolder = $userFolder->get('/Police Ready for Pick-up');
		// $DPSReadyFolderName = '/temp';
		// $content = $content . "\r\nListing '" . $policeFolder->getName() . "':\r\n\r\n";
		// $content = $content . "S3ReadyFolderPath = " . $S3ReadyFolderPath . "\r\n";
		foreach($policeFolder->getDirectoryListing() as $packageFolder) {
			$packageFolderName = $packageFolder->getName();

			// unique identifiers
			$uniqid = uniqid('',false);
			// $receivedDate = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
			// $receivedDateString = $datetime->format("Y-m-d");
			
			$packageMetadataFile = $packageFolder->get("metadata.txt");
			$packageMetadataContent = $packageMetadataFile->getContent();
			$pages = trim(strtok($packageMetadataContent,','));
			$orgName = trim(strtok(','));
			$receivedDateString = trim(strtok(','));

			//$packageMetadataFile = $packageFolder->get("metadata.txt");
			//$packageMetadataContent = $packageMetadataFile->getContent();
			//$pages = 1;
			//$orgName = 'Police';
			//$receivedDateString = '2021-04-09';

			// $DPSBatchName = 'SFT-2021-04-09-15-41-31-423';
			$datetime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
			$DPSBatchName = 'SFT-' . $datetime->format("Y-m-d-h-i-s-v");

			// $content = $content . "   " . $packageName . "\r\n";
			$newFolder = $S3ReadyFolder->newFolder($uniqid . " (" . $packageFolderName . ")");
			$newFolderName = $newFolder->getName();
			$newReceivedFolder = $newFolder->newFolder('Received');
			$newReceivedFolderPath = $newReceivedFolder->getPath();
			$packageFolder->move($newReceivedFolderPath . '/' . $packageFolderName);

			$newXMLFile = $newFolder->newFile($uniqid . '.xml');
			$content = 
				"<ImportSession UserID=\"" . $DPSserver . "\\kofax.service\" Password=\"" . $DPSpasswd . "\">\r\n" .
				"	<Batches>\r\n" . 
				"		<Batch Name=\"" . $DPSBatchName . "\" BatchClassName=\"VIPS\" EnableAutomaticSeparationAndFormID=\"1\" RelativeImageFilePath=\".\">\r\n" .
				"			<BatchFields>\r\n" . 
				"				<BatchField Name=\"ImportDate\" Value=\"" . $receivedDateString . "\"/>\r\n" .
				"				<BatchField Name=\"FaxReceivedDate\" Value=\"" . $receivedDateString . "\"/>\r\n" .
				"				<BatchField Name=\"OriginatingNumber\" Value=\"" . $orgName . "\"/>\r\n" .
				"				<BatchField Name=\"ImportID\" Value=\"" . $newFolderName . "\"/>\r\n" .
				"			</BatchFields>\r\n" .
				"			<Pages>\r\n";

			//$packageFolder1 = $userFolder->get("/temp-S3/DPS Ready/" . $newFolderName . "/" . "Received/" . $packageFolderName);
			foreach($packageFolder->getDirectoryListing() as $packageFile) {
				$packageFileName = $packageFile->getName();
				if (strcmp($packageFileName,"metadata.txt") != 0) {
					$content = $content . 
						"				<Page ImportFileName=\"" . $packageFileName . "\" OriginalFileName=\"" . $packageFileName . "\"/>\r\n";
					$packageFile->copy($DPSFolderPath . "/" . $packageFileName);
				}
			}

			// temp
			// $packageFileName = "Vehicle ImpoundmentV2.pdf";
			// $packageFile = $packageFolder->get($packageFileName);
			// if (strcmp($packageFileName,"metadata.txt") != 0) {
			// 	$content = $content . 
			// 		"				<Page ImportFileName=\"" . $packageFile->getName() . "\" OriginalFileName=\"" . $packageFile->getName() . "\"/>\r\n";
			// }

			$content = $content . 
				"			</Pages>\r\n" .
				"		</Batch>\r\n" .
				"	</Batches>\r\n" .
				"</ImportSession>\r\n";
			$newXMLFile->putContent($content);

			$newXMLFile->copy($DPSFolderPath . "/" . $newXMLFile->getName());
			$newXMLFile->copy($DPSControlFolderPath . "/" . $newXMLFile->getName());

			$newFolder->move($S3SentFolderPath . "/" . $newFolderName);
		}
		// $content2 = $content . "\r\nFolder name = " . $policeFolder;
		// $f1 = $userFolder->get('/Police Ready for Pick-up/F2');
		// $content = $content . "F2 folder path = " . $f1->getPath() . "\r\n";
		// $f1file = $f1->newFile(uniqid('test-file-',false));
		// $f1file->putContent($content);
		// $f1->move($DPSReadyFolderPath);
		// $newFile->putContent($content);
	} catch (\OCP\Files\NotFoundException $ex) {
		// no files to work with.  do nothing.
		$DPSFolder = $userFolder->get('/temp');
		if ($DPSFolder->nodeExists('notfound.txt')) {
			$f = $DPSFolder->get('notfound.txt');
		} else {
			$f = $DPSFolder->newFile('notfound.txt');
		}
		$c = $f->getContent();
		$c = $c . uniqid("\r\New entry = ",false);
		$f->putContent($c);
	}
	// RSFT POC - End
	// ===========================================================================

	\OC::$server->getTempManager()->cleanOld();

	// Exit if background jobs are disabled!
	$appMode = $config->getAppValue('core', 'backgroundjobs_mode', 'ajax');
	if ($appMode === 'none') {
		if (OC::$CLI) {
			echo 'Background Jobs are disabled!' . PHP_EOL;
		} else {
			OC_JSON::error(['data' => ['message' => 'Background jobs disabled!']]);
		}
		exit(1);
	}

	if (OC::$CLI) {
		// set to run indefinitely if needed
		if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
			@set_time_limit(0);
		}

		// the cron job must be executed with the right user
		if (!function_exists('posix_getuid')) {
			echo "The posix extensions are required - see http://php.net/manual/en/book.posix.php" . PHP_EOL;
			exit(1);
		}

		$user = posix_getuid();
		$configUser = fileowner(OC::$configDir . 'config.php');
		if ($user !== $configUser) {
			echo "Console has to be executed with the user that owns the file config/config.php" . PHP_EOL;
			echo "Current user id: " . $user . PHP_EOL;
			echo "Owner id of config.php: " . $configUser . PHP_EOL;
			exit(1);
		}


		// We call Nextcloud from the CLI (aka cron)
		if ($appMode !== 'cron') {
			$config->setAppValue('core', 'backgroundjobs_mode', 'cron');
		}

		// Work
		$jobList = \OC::$server->getJobList();

		// We only ask for jobs for 14 minutes, because after 5 minutes the next
		// system cron task should spawn and we want to have at most three
		// cron jobs running in parallel.
		$endTime = time() + 14 * 60;

		$executedJobs = [];
		while ($job = $jobList->getNext()) {
			if (isset($executedJobs[$job->getId()])) {
				$jobList->unlockJob($job);
				break;
			}

			$job->execute($jobList, $logger);
			// clean up after unclean jobs
			\OC_Util::tearDownFS();

			$jobList->setLastJob($job);
			$executedJobs[$job->getId()] = true;
			unset($job);

			if (time() > $endTime) {
				break;
			}
		}

	} else {
		// We call cron.php from some website
		if ($appMode === 'cron') {
			// Cron is cron :-P
			OC_JSON::error(['data' => ['message' => 'Backgroundjobs are using system cron!']]);
		} else {
			// Work and success :-)
			$jobList = \OC::$server->getJobList();
			$job = $jobList->getNext();
			if ($job != null) {
				$job->execute($jobList, $logger);
				$jobList->setLastJob($job);
			}
			OC_JSON::success();
		}
	}

	// Log the successful cron execution
	$config->setAppValue('core', 'lastcron', time());
	exit();
} catch (Exception $ex) {
	\OC::$server->getLogger()->logException($ex, ['app' => 'cron']);
} catch (Error $ex) {
	\OC::$server->getLogger()->logException($ex, ['app' => 'cron']);
}
