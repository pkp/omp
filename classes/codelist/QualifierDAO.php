<?php

/**
 * @file classes/codelist/QualifierDAO.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class QualifierDAO
 *
 * @see Qualifier
 *
 * @brief Operations for retrieving and modifying Subject Qualifier objects.
 *
 */

namespace APP\codelist;

use PKP\facades\Locale;
use PKP\i18n\interfaces\LocaleInterface;

class QualifierDAO extends CodelistItemDAO
{
    /**
     * Get the filename of the qualifier database
     */
    public function getFilename(string $locale): string
    {
        if (!Locale::isLocaleValid($locale)) {
            $locale = LocaleInterface::DEFAULT_LOCALE;
        }
        return "lib/pkp/locale/{$locale}/bic21qualifiers.xml";
    }

    /**
     * Get the base node name particular codelist database
     * This is also the node name in the XML.
     */
    public function getName(): string
    {
        return 'qualifier';
    }

    /**
     * Get the name of the CodelistItem subclass.
     */
    public function newDataObject(): Qualifier
    {
        return new Qualifier();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\QualifierDAO', '\QualifierDAO');
}
