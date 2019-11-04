<?php
/**
 * @file classes/components/form/publication/PublicationDatesForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDatesForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring whether chapters get their own
 *  publication dates
 */
namespace APP\components\forms\submission;
use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldOptions;

define('FORM_PUBLICATION_DATES', 'publicationDates');

class PublicationDatesForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_PUBLICATION_DATES;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $submission Submission The submission of this publication
	 */
	public function __construct($action, $submission) {
		$this->action = $action;
		$this->successMessage = __('publication.catalogEntry.success');

		$this->addField(new FieldOptions('enableChapterPublicationDates', [
			'label' => __('submission.catalogEntry.chapterPublicationDates'),
			'type' => 'checkbox',
			'value' => $submission->getData('enableChapterPublicationDates'),
			'options' => [
				['value' => true, 'false' => __('submission.catalogEntry.disableChapterPublicationDates')],
				['value' => true, 'label' => __('submission.catalogEntry.enableChapterPublicationDates')],
			]
		]));
	}
}
