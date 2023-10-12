<?php

/**
 * @defgroup api_v1_backend Backend API requests for payments settings
 */

/**
 * @file api/v1/_payments/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1__payments
 *
 * @brief Handle requests for backend API.
 *
 */

return new \PKP\handler\APIHandler(new \PKP\API\v1\_payments\PKPBackendPaymentsSettingsController());
