<?php

/**
 * @file classes/components/form/publication/PublicationLicenseForm.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationLicenseForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for setting a publication's license and copyright info
 */

namespace APP\components\forms\publication;

use APP\core\Application;
use APP\facades\Repo;
use APP\press\Press;
use APP\publication\Publication;
use APP\submission\Submission;
use Illuminate\Support\Enumerable;
use PKP\components\forms\FieldText;
use PKP\components\forms\publication\PKPPublicationLicenseForm;
use PKP\userGroup\UserGroup;

class PublicationLicenseForm extends PKPPublicationLicenseForm
{
    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param array $locales Supported locales
     * @param Publication $publication The publication to change settings for
     * @param Press $context The publication's context
     * @param Enumerable<UserGroup> $userGroups User groups in this context
     */
    public function __construct($action, $locales, $publication, $context, $userGroups)
    {
        parent::__construct($action, $locales, $publication, $context, $userGroups);

        $submission = Repo::submission()->get($publication->getData('submissionId'));
        if ($submission->getData('workType') === Submission::WORK_TYPE_EDITED_VOLUME) {
            // Get the name of the context's license setting
            $chapterLicenseUrlDescription = '';
            $licenseOptions = Application::getCCLicenseOptions();
            if ($publication->getData('licenseUrl')) {
                if (array_key_exists($publication->getData('licenseUrl'), $licenseOptions)) {
                    $licenseName = __($licenseOptions[$publication->getData('licenseUrl')]);
                } else {
                    $licenseName = $publication->getData('licenseUrl');
                }
                $chapterLicenseUrlDescription = __('submission.license.description', [
                    'licenseUrl' => $publication->getData('licenseUrl'),
                    'licenseName' => $licenseName,
                ]);
            } elseif ($context->getData('licenseUrl')) {
                if (array_key_exists($context->getData('licenseUrl'), $licenseOptions)) {
                    $licenseName = __($licenseOptions[$context->getData('licenseUrl')]);
                } else {
                    $licenseName = $context->getData('licenseUrl');
                }
                $chapterLicenseUrlDescription = __('submission.license.description', [
                    'licenseUrl' => $context->getData('licenseUrl'),
                    'licenseName' => $licenseName,
                ]);
            }

            $this->addField(new FieldText('chapterLicenseUrl', [
                'label' => __('publication.chapterDefaultLicenseURL'),
                'description' => $chapterLicenseUrlDescription,
                'optIntoEdit' => ($publication->getData('licenseUrl') || $context->getData('licenseUrl')) && !$publication->getData('chapterLicenseUrl'),
                'optIntoEditLabel' => __('common.override'),
                'value' => $publication->getData('chapterLicenseUrl'),
            ]));
        }
    }
}
