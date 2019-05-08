<?php
/**
 * @file classes/components/form/context/ReviewGuidanceForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGuidanceForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring the guidance a reviewer should receive.
 */
namespace APP\components\forms\context;
use \PKP\components\forms\context\PKPReviewGuidanceForm;
use \PKP\components\forms\FieldRichTextarea;

class ReviewGuidanceForm extends PKPReviewGuidanceForm {

	/**
	 * @copydoc PKPAppearanceSetupForm::__construct()
	 */
	public function __construct($action, $locales, $context) {
		parent::__construct($action, $locales, $context);

		$this->addField(new FieldRichTextarea('internalReviewGuidelines', [
				'label' => __('manager.setup.internalReviewGuidelines'),
				'helpTopic' => 'settings',
				'helpSection' => 'workflow-review-guidelines',
				'value' => $context->getData('internalReviewGuidelines'),
				'isMultilingual' => true,
			]), [FIELD_POSITION_BEFORE, 'reviewGuidelines']);
	}
}
