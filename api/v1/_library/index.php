<?php
/**
 * @defgroup api_v1_library Library files API requests
 */

/**
 * @file api/v1/_library/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1__library
 *
 * @brief Handle API requests for the publisher and submission library files.
 */

return new \PKP\handler\APIHandler(new \PKP\API\v1\_library\PKPLibraryController());
