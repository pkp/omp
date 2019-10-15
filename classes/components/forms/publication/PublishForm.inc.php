<?php
/**
 * @file classes/components/form/publication/PublishForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for confirming a publication has met requirements
 *   before it is published.
 */
namespace APP\components\forms\publication;
use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldHTML;

define('FORM_PUBLISH', 'publish');

class PublishForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_PUBLISH;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/** @var Publication The publication being published */
	public $publication;

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $publication Publication The publication to change settings for
	 * @param $requirementErrors array A list of pre-publication requirements that are not met.
	 */
	public function __construct($action, $publication, $requirementErrors) {
		$this->action = $action;
		$this->successMessage = __('publication.publish.success');
		$this->errors = $requirementErrors;
		$this->publication = $publication;

		// Set separate messages and buttons if publication requirements have passed
		if (empty($requirementErrors)) {
			$msg = __('publication.publish.confirmation');
			$this->addPage([
				'id' => 'default',
				'submitButton' => [
					'label' => __('publication.publish'),
				],
			]);
		} else {
			$msg = '<p>' . __('publication.publish.requirements') . '</p>';
			$msg .= '<ul>';
			foreach ($requirementErrors as $error) {
				$msg .= '<li>' . $error . '</li>';
			}
			$msg .= '</ul>';
			$this->addPage([
				'id' => 'default',
			]);
		}

		$this->addGroup([
				'id' => 'default',
				'pageId' => 'default',
			])
			->addField(new FieldHTML('validation', [
				'description' => $msg,
				'groupId' => 'default',
			]));
	}
}
