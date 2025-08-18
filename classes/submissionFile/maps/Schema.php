<?php

/**
 * @file classes/publication/maps/Schema.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Schema
 *
 * @brief Map publications to the properties defined in the publication schema
 */

namespace APP\submissionFile\maps;

use APP\facades\Repo;
use PKP\submissionFile\maps\Schema as BaseSchema;
use PKP\submissionFile\SubmissionFile;

class Schema extends BaseSchema
{
    /** @copydoc \PKP\submissionFile\maps\Schema::mapByProperties() */
    protected function mapByProperties(array $props, SubmissionFile $item, array $uploaderUsernames): array
    {
        $output = parent::mapByProperties($props, $item, $uploaderUsernames);

        if (in_array('doiObject', $props)) {
            if ($item->getData('doiId')) {
                $retVal = Repo::doi()->getSchemaMap()->summarize($item->getData('doiObject'));
            } else {
                $retVal = null;
            }
            $output['doiObject'] = $retVal;
        }

        ksort($output);

        return $output;
    }
}
