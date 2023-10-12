<?php
/**
 * @defgroup api_v1_dois Backend DOI API requests
 */

/**
 * @file api/v1/_dois/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1__dois
 *
 * @brief Handle API requests for backend DOI operations.
 */

return new \PKP\handler\APIHandler(new \APP\API\v1\_dois\BackendDoiController());
