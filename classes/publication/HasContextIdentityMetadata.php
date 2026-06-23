<?php

/**
 * @file classes/publication/HasContextIdentityMetadata.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @trait HasContextIdentityMetadata
 *
 * @brief Press-specific extension of the generic identity resolver: adds publisher,
 *   publisher code, and code value getters on top of the shared name getters.
 */

namespace APP\publication;

use PKP\context\Context;

trait HasContextIdentityMetadata
{
    use \PKP\publication\HasContextIdentityMetadata;

    /**
     * Get the stamped publisher name, falling back to the live context value.
     */
    public function getPublisher(Context $context): ?string
    {
        return $this->getData('publisher') ?: $context->getData('publisher');
    }

    /**
     * Get the stamped publisher code type, falling back to the live context value.
     */
    public function getCodeType(Context $context): ?string
    {
        return $this->getData('codeType') ?: $context->getData('codeType');
    }

    /**
     * Get the stamped publisher code value, falling back to the live context value.
     */
    public function getCodeValue(Context $context): ?string
    {
        return $this->getData('codeValue') ?: $context->getData('codeValue');
    }
}
