<?php

/**
 * @file classes/components/forms/publication/MetadataForm.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MetadataForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief The publication metadata form, restricting the subjects field to
 *   Thema subject categories when the press requires them.
 */

namespace APP\components\forms\publication;

use APP\codelist\Thema;
use APP\publication\Publication;
use PKP\components\forms\FieldControlledVocab;
use PKP\components\forms\publication\PKPMetadataForm;
use PKP\context\Context;

class MetadataForm extends PKPMetadataForm
{
    /**
     * Constructor
     *
     * When the press uses Thema subject categories, the subjects field carries
     * guidance on choosing them. When Thema is required, free-text entries are
     * disallowed so that only suggested Thema codes can be selected. The same
     * rules are enforced server-side when the publication is validated.
     */
    public function __construct(string $action, array $locales, Publication $publication, Context $context, string $suggestionUrlBase)
    {
        parent::__construct($action, $locales, $publication, $context, $suggestionUrlBase);

        /** @var ?FieldControlledVocab $subjectsField */
        $subjectsField = $this->getField('subjects');
        if ($subjectsField) {
            Thema::configureSubjectsField($subjectsField, $context);
        }
    }
}
