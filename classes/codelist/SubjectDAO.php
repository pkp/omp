<?php

/**
 * @file classes/codelist/SubjectDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubjectDAO
 * @ingroup codelist
 *
 * @see Subject
 *
 * @brief Operations for retrieving and modifying Subject Subject objects.
 *
 */

namespace APP\codelist;

use PKP\facades\Locale;
use PKP\i18n\interfaces\LocaleInterface;

class SubjectDAO extends CodelistItemDAO
{
    /**
     * Get the filename of the subject database
     *
     * @param string $locale
     *
     * @return string
     */
    public function getFilename($locale)
    {
        if (!Locale::isLocaleValid($locale)) {
            $locale = LocaleInterface::DEFAULT_LOCALE;
        }
        return "lib/pkp/locale/{$locale}/bic21subjects.xml";
    }

    /**
     * Get the base node name particular codelist database
     * This is also the node name in the XML.
     *
     * @return string
     */
    public function getName()
    {
        return 'subject';
    }

    /**
     * Get the name of the CodelistItem class.
     *
     * @return Subject
     */
    public function newDataObject()
    {
        return new Subject();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\SubjectDAO', '\SubjectDAO');
}
