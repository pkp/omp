<?php

/**
 * @defgroup pages_information Information page
 */

/**
 * @file pages/information/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_information
 * @brief Handle information requests.
 *
 */

switch ($op) {
    case 'index':
    case 'readers':
    case 'authors':
    case 'librarians':
    case 'competingInterestPolicy':
    case 'sampleCopyrightWording':
        define('HANDLER_CLASS', 'APP\pages\information\InformationHandler');
        break;
}
