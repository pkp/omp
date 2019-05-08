<?php
/**
 * @file classes/components/form/context/AppearanceAdvancedForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceAdvancedForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for general website appearance setup, such as uploading
 *  a logo.
 */
namespace APP\components\forms\context;
use \PKP\components\forms\context\PKPAppearanceAdvancedForm;
use \PKP\components\forms\FieldText;

class AppearanceAdvancedForm extends PKPAppearanceAdvancedForm {

	/**
	 * @copydoc PKPAppearanceAdvancedForm::__construct()
	 */
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl) {
		parent::__construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl);

		$this->addField(new FieldText('coverThumbnailsMaxWidth', [
				'label' => __('manager.setup.coverThumbnailsMaxWidth'),
				'description' => __('manager.setup.coverThumbnailsMaxWidthHeight.description'),
				'value' => $context->getData('coverThumbnailsMaxWidth'),
			]))
			->addField(new FieldText('coverThumbnailsMaxHeight', [
				'label' => __('manager.setup.coverThumbnailsMaxHeight'),
				'description' => __('manager.setup.coverThumbnailsMaxWidthHeight.description'),
				'value' => $context->getData('coverThumbnailsMaxHeight'),
			]));
	}
}
