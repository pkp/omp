<?php
/**
 * @defgroup api_v1_mailables Email templates API requests
 */

/**
 * @file api/v1/mailables/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_mailables
 *
 * @brief Handle API requests for mailables.
 */

return new \PKP\handler\APIHandler(new \PKP\API\v1\mailables\PKPMailableController());
