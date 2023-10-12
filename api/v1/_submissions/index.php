<?php

/**
 * @defgroup api_v1_backend Backend API requests for submissions
 */

/**
 * @file api/v1/_submissions/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1__submissions
 *
 * @brief Handle requests for backend API.
 *
 */

return new \PKP\core\PKPApiRoutingHandler(new \APP\API\v1\_submissions\BackendSubmissionsController());
