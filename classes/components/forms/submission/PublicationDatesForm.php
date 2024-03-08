<?php
/**
 * @file classes/components/form/publication/PublicationDatesForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDatesForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring whether chapters get their own
 *  publication dates, landing pages and licenses.
 */

namespace APP\components\forms\submission;

use APP\submission\Submission;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FormComponent;

define('FORM_PUBLICATION_DATES', 'publicationDates');

class PublicationDatesForm extends FormComponent
{
    /** @copydoc FormComponent::$id */
    public $id = FORM_PUBLICATION_DATES;

    /** @copydoc FormComponent::$method */
    public $method = 'PUT';

    /** @var string */
    public $successMessage;

    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param Submission $submission The submission of this publication
     */
    public function __construct($action, $submission)
    {
        $this->action = $action;
        $this->successMessage = __('publication.catalogEntry.success');

        $this->addField(new FieldOptions('enableChapterPublicationDates', [
            'label' => __('submission.catalogEntry.chapterPublicationDates'),
            'type' => 'radio',
            'value' => $submission->getData('enableChapterPublicationDates'),
            'options' => [
                ['value' => false, 'label' => __('submission.catalogEntry.disableChapterPublicationDates')],
                ['value' => true, 'label' => __('submission.catalogEntry.enableChapterPublicationDates')],
            ]
        ]));
    }
}
