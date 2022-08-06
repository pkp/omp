<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFileFilter.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFileFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a monograph file.
 */

namespace APP\plugins\importexport\native\filter;

class NativeXmlMonographFileFilter extends \PKP\plugins\importexport\native\filter\NativeXmlSubmissionFileFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.NativeXmlMonographFileFilter';
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\importexport\native\filter\NativeXmlMonographFileFilter', '\NativeXmlMonographFileFilter');
}
