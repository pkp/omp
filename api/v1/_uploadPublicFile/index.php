<?php
/**
 * @defgroup api_v1_uploadPublicFile Email templates API requests
 */

/**
 * @file api/v1/_uploadPublicFile/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1__uploadPublicFile
 *
 * @brief Handle API requests for uploadPublicFile.
 */

return new \PKP\core\PKPApiRoutingHandler(new \PKP\API\v1\_uploadPublicFile\PKPUploadPublicFileController());
