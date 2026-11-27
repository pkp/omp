<?php

/**
 * @file classes/view/MetadataBlockRepository.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to register and load metadata blocks.
 */

namespace APP\view;

class MetadataBlocksRegistry extends \PKP\view\MetadataBlocksRegistry
{
    protected function registerDefaultBlocks(): void
    {
        // Not supported in OMP
        // When supported, call the parent method
        // ----
        // parent::registerDefaultBlocks();
    }
}
