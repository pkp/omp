<?php
/**
 * @file classes/components/form/catalog/AddEntryForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AddEntryForm
 * @ingroup classes_controllers_form
 *
 * @brief A form to find and add submissions to the catalog
 */
namespace APP\components\forms\catalog;

use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldSelectSubmissions;

import('classes.submission.Submission'); // load STATUS_ constants

define('FORM_ADD_ENTRY', 'addEntry');

class AddEntryForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_ADD_ENTRY;

	/**
	 * @copydoc PKPAddEntryForm::__construct()
	 */
	public function __construct($action, $apiUrl, $locales) {
		parent::__construct($this->id, 'PUT', $action, $locales);

		$this->addField(new FieldSelectSubmissions('submissionIds', [
			'label' => __('catalog.manage.findSubmissions'),
			'value' => [],
			'apiUrl' => $apiUrl,
			'getParams' => [
				'stageIds' => [WORKFLOW_STAGE_ID_EDITING, WORKFLOW_STAGE_ID_PRODUCTION],
				'status' => [STATUS_QUEUED, STATUS_SCHEDULED],
			],
		]));
	}
}
