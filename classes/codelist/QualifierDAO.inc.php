<?php

/**
 * @file classes/codelist/QualifierDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class QualifierDAO
 * @ingroup codelist
 *
 * @see Qualifier
 *
 * @brief Operations for retrieving and modifying Subject Qualifier objects.
 *
 */

namespace APP\codelist;

use APP\i18n\AppLocale;

class QualifierDAO extends CodelistItemDAO
{
    /**
     * Get the filename of the qualifier database
     *
     * @param string $locale
     *
     * @return string
     */
    public function getFilename($locale)
    {
        if (!AppLocale::isLocaleValid($locale)) {
            $locale = AppLocale::MASTER_LOCALE;
        }
        return "lib/pkp/locale/${locale}/bic21qualifiers.xml";
    }

    /**
     * Get the base node name particular codelist database
     * This is also the node name in the XML.
     *
     * @return string
     */
    public function getName()
    {
        return 'qualifier';
    }

    /**
     * Get the name of the CodelistItem subclass.
     *
     * @return Qualifier
     */
    public function newDataObject()
    {
        return new Qualifier();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\QualifierDAO', '\QualifierDAO');
}
