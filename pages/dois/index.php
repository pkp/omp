<?php
/**
 * @defgroup pages_doiManagement DOI Management Pages
 */

/**
 * @file pages/doiManagement/index.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_doiManagement
 * @brief Handle requests for DOI management functions.
 *
 */

switch ($op) {
    case 'index':
        define('HANDLER_CLASS', 'DoisHandler');
        import('pages.dois.DoisHandler');
        break;
}
