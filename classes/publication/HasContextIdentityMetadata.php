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
     * Get the stamped publisher name, or the live context value if no identity has been stamped.
     */
    public function getPublisher(Context $context): ?string
    {
        return $this->hasContextIdentity() ? ($this->getData('publisher') ?: null) : $context->getData('publisher');
    }

    /**
     * Get the stamped publisher code type, or the live context value if no identity has been stamped.
     */
    public function getCodeType(Context $context): ?string
    {
        return $this->hasContextIdentity() ? ($this->getData('codeType') ?: null) : $context->getData('codeType');
    }

    /**
     * Get the stamped publisher code value, or the live context value if no identity has been stamped.
     */
    public function getCodeValue(Context $context): ?string
    {
        return $this->hasContextIdentity() ? ($this->getData('codeValue') ?: null) : $context->getData('codeValue');
    }
}
