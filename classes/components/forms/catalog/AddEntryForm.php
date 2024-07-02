<?php
/**
 * @file classes/components/form/catalog/AddEntryForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AddEntryForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A form to find and add submissions to the catalog
 */

namespace APP\components\forms\catalog;

use PKP\components\forms\FieldSelectSubmissions;
use PKP\components\forms\FormComponent;
use PKP\submission\PKPSubmission;

class AddEntryForm extends FormComponent
{
    public const FORM_ADD_ENTRY = 'addEntry';
    public $id = self::FORM_ADD_ENTRY;

    /**
     * @copydoc PKPAddEntryForm::__construct()
     */
    public function __construct($action, $apiUrl, $locales)
    {
        parent::__construct($this->id, 'PUT', $action, $locales);

        $this->addField(new FieldSelectSubmissions('submissionIds', [
            'label' => __('catalog.manage.findSubmissions'),
            'value' => [],
            'apiUrl' => $apiUrl,
            'getParams' => [
                'stageIds' => [WORKFLOW_STAGE_ID_EDITING, WORKFLOW_STAGE_ID_PRODUCTION],
                'status' => [PKPSubmission::STATUS_QUEUED, PKPSubmission::STATUS_SCHEDULED],
            ],
        ]));
    }
}
