<?php
/**
 * @file classes/components/form/context/LicenseForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LicenseForm
 * @ingroup classes_controllers_form
 *
 * @brief Add OJS-specific details to the license settings forms
 */
namespace APP\components\forms\context;
use \PKP\components\forms\context\PKPLicenseForm;

define('FORM_LICENSE', 'license');

class LicenseForm extends PKPLicenseForm {}
