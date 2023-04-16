<?php
/**
 * @file classes/components/form/context/ContextForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ContextForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief Add OMP-specific fields to the context add/edit form.
 */

namespace APP\components\forms\context;

use PKP\components\forms\context\PKPContextForm;
use PKP\components\forms\FieldOptions;

class ContextForm extends PKPContextForm
{
    /**
     * @copydoc PKPContextForm::__construct()
     */
    public function __construct($action, $locales, $baseUrl, $context)
    {
        parent::__construct($action, $locales, $baseUrl, $context);

        $this->addField(new FieldOptions('enabled', [
            'label' => __('common.enable'),
            'options' => [
                ['value' => true, 'label' => __('manager.setup.enablePressInstructions')],
            ],
            'value' => $context ? (bool) $context->getData('enabled') : false,
        ]));
    }
}
