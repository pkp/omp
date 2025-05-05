<?php

/**
 * @file classes/components/form/publication/PublishForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublishForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for confirming a publication has met requirements
 *   before it is published.
 */

namespace APP\components\forms\publication;

use APP\facades\Repo;
use APP\publication\Publication;
use PKP\components\forms\FieldHTML;
use PKP\components\forms\FormComponent;
use PKP\publication\enums\VersionStage;

class PublishForm extends FormComponent
{
    public const FORM_PUBLISH = 'publish';
    public $id = self::FORM_PUBLISH;
    public $method = 'PUT';

    /** @var Publication The publication being published */
    public $publication;

    /** @var \Context */
    public $submissionContext;

    /** @var string */
    public $successMessage;

    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param Publication $publication The publication to change settings for
     * @param \Context $submissionContext journal or press
     * @param array $requirementErrors A list of pre-publication requirements that are not met.
     */
    public function __construct($action, $publication, $submissionContext, $requirementErrors)
    {
        $this->action = $action;
        $this->successMessage = __('publication.publish.success');
        $this->errors = $requirementErrors;
        $this->publication = $publication;
        $this->submissionContext = $submissionContext;

        // Set separate messages and buttons if publication requirements have passed
        if (empty($requirementErrors)) {
            $msg = __('publication.publish.confirmation');
            $this->addPage([
                'id' => 'default',
                'submitButton' => [
                    'label' => __('publication.publish'),
                ],
            ]);

            // If publication does not have a version stage assigned
            $publicationVersion = $publication->getVersion();
            if (!isset($publicationVersion)) {
                $submission = Repo::submission()->get($publication->getData('submissionId'));
                $nextVersion = Repo::submission()->getNextAvailableVersion($submission, VersionStage::VERSION_OF_RECORD, false);

                $msg .= '<p>' . __('publication.required.versionStage') . '</p>';
                $msg .= '<p>' . __('publication.required.versionStage.assignment', [
                    'versionString' => $nextVersion
                ]) . '</p>';
            } else {
                $msg .= '<p>' . __('publication.required.versionStage.alreadyAssignment', [
                    'versionString' => $publicationVersion
                ]) . '</p>';
            }
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
