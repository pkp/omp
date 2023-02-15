# Upgrading an OMP Installation

Note: Before upgrading your installation, perform a complete backup of your
data files and database. If the upgrade process fails, you may need to recover
from backup before continuing.

If you are using PHP Safe Mode, please ensure that the max_execution_time
directive in your php.ini configuration file is set to a high limit. If this
or any other time limit (e.g. Apache's "Timeout" directive) is reached and
the upgrade process is interrupted, manual intervention will be required.

Upgrading to the latest version of OMP involves two steps:

- [Obtaining the latest OMP code](#obtaining-the-latest-omp-code)
- [Upgrading the OMP database](#upgrading-the-omp-database)

It is highly recommended that you also review the release notes ([RELEASE](RELEASE))
and [How to Upgrade](https://docs.pkp.sfu.ca/dev/upgrade-guide/en/) before performing an upgrade.


### Obtaining the latest OMP code

The OMP source code is available in two forms: a complete stand-alone
package, and from read-only github access.

#### 1. Full Package

If you have not made local code modifications to the system, upgrade by
downloading the complete package for the latest release of OMP:

- Download and decompress the package from the OMP web site into an empty
	directory (NOT over top of your current OMP installation)
- Move or copy the following files and directories from your current OMP
	installation:
      
		- config.inc.php
		- public 

- Move the old OJS installation directory to a safe location and move the new one into
	its place
- Be sure to review the Configuration Changes section of the release notes
	in docs/release-notes/README-(version) for all versions between your
	original version and the new version. You may need to manually add
	new items to your config.inc.php file.

#### 2. git

Updating from github is the recommended approach if you have made local
modifications to the system.

##### Updating the application code

To update the OMP code from a git check-out, run the following command from
your OMP directory:

    git rebase --onto <new-release-tag> <previous-release-tag>

This assumes that you have made local changes and committed them on top of
the old release tag. The command will take your custom changes and apply
them on top of the new release. This may cause merge conflicts which have to
be resolved in the usual way, e.g. using a merge tool like kdiff3.

"TAG" should be replaced with the git tag corresponding to the new release.
OMP release version tags are of the form "omp-MAJOR_MINOR_REVISION-BUILD".
For example, the tag for the initial release of OMP 1.0 is "omp-1_0_0-0".

Consult the [README](README.md) of the latest OMP package or the OMP web site for the
tag corresponding to the latest available OMP release.

Note that attempting to update to an unreleased version (e.g., using the HEAD
tag to obtain the bleeding-edge OMP code) is not recommended for anyone other
than OMP or third-party developers; using experimental code on a production
deployment is strongly discouraged and will not be supported in any way by
the OMP team.

##### Updating dependencies

After obtaining to the latest OMP code, additional steps are required to
update OMP's dependencies.

Firstly, update all submodules and libraries like so:

```
git submodule update --init --recursive
```

Then, install and update dependencies via Composer:

```
composer --working-dir=lib/pkp install
composer --working-dir=plugins/paymethod/paypal install
```

and NPM:

```
# install [nodejs](https://nodejs.org/en/) if you don't already have it
npm install
npm run build
```

### Upgrading the OMP database

After updating your OMP installation, an additional script must be run to
complete the upgrade process by upgrading the OMP database and potentially
executing additional upgrade code.

This script can be executed from the command-line or via the OMP web interface.

#### 1. Command-line

If you have the CLI version of PHP installed (e.g., /usr/bin/php), you can
upgrade the database as follows:

- Edit config.inc.php and change "installed = On" to "installed = Off"
- Run the following command from the OMP directory:
  php tools/upgrade.php upgrade
- Re-edit config.inc.php and change "installed = Off" back to
   "installed = On"

#### 2. Web

If you do not have the PHP CLI installed, you can also upgrade by running a
web-based script. To do so:

- Edit config.inc.php and change "installed = On" to "installed = Off"
- Open a web browser to your OMP site; you should be redirected to the
  installation and upgrade page
- Select the "Upgrade" link and follow the on-screen instructions
- Re-edit config.inc.php and change "installed = Off" back to
   "installed = On"
