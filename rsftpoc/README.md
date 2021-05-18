# Nextcloud App Tutorial

[![PHPUnit GitHub Action](https://github.com/nextcloud/app-tutorial/workflows/PHPUnit/badge.svg)](https://github.com/nextcloud/app-tutorial/actions?query=workflow%3APHPUnit)
[![Node GitHub Action](https://github.com/nextcloud/app-tutorial/workflows/Node/badge.svg)](https://github.com/nextcloud/app-tutorial/actions?query=workflow%3ANode)
[![Lint GitHub Action](https://github.com/nextcloud/app-tutorial/workflows/Lint/badge.svg)](https://github.com/nextcloud/app-tutorial/actions?query=workflow%3ALint)

This is the [RoadSafetyBC Secure File Transfer Proof-of-concept (RSFTPOC) app.  ](https://docs.nextcloud.com/server/latest/developer_manual/app_development/tutorial.html)
 
## Run it locally

1. Set up your local instance of Nextcloud (version v20), following the Nextcloud install instructions.

2. Clone the JAG RSBC Nextcloud proof-of-concept GitHub repo to your local machine

    git clone https://github.com/bcgov/jag-rsbc-nextcloud.git

3. Copy the repo's sub-directory 'rsftpoc' into your local Nextcloud's apps directory

    ex: nextcloud/apps/rsftpoc

4. Install the dependencies in the 'rsftpoc' app folder using:

    make composer

## Frontend development

The app tutorial also shows the very basic implementation of an app frontend using [Vue.js](https://vuejs.org/). To build the frontend code after doing changes to its source in `src/` requires to have Node and npm installed.

- 👩‍💻 Run `make dev-setup` to install the frontend dependencies
- 🏗 To build the Javascript whenever you make changes, run `make build-js`

To continuously run the build when editing source files you can make use of the `make watch-js` command.

## Cronjob development

RSFTPOC has the frontend component, as well as a cronjob component.  The scheduled job development is uploaded to the repository as 'rsftpoc/cron-poc/cron.php'.  The cron.php is intended to replace the out of the box Nextcloud cron.php file.

## Notes

* The RSFTPOC app is built from Nextcloud's demo application 'notestutorial'.  The app still has internal system id 'notestutorial'.
* The RSFTPOC will need to be installed via Nextcloud Apps management UI.
* Any time RSFTPOC app is updated, the following three steps will need to be executed, for Nextcloud to recognize the changes:
   1. Execute the make command under 'rsftpoc': make build-js
   2. Increment the version number in 'rsftpoc/appinfo/info.xml': e.g., 18.2.74 -> 18.2.75
   3. Revisit the RSFTPOC app to trigger the update either through the top menu as 'RSFTPOC', or access the app's URL: '/apps/notestutorial'
* Any changes made in the local 'nextcloud/apps/rsftpoc' directory will need to be manually copied back into the repo and pushed to Github in order to persist the changes.