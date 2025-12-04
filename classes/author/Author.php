<?php

/**
 * @file classes/author/Author.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Author
 *
 * @ingroup monograph
 *
 * @see DAO
 *
 * @brief Monograph author metadata class.
 */

namespace APP\author;

use Illuminate\Support\Arr;
use PKP\author\contributorRole\ContributorRole;
use PKP\author\contributorRole\ContributorRoleIdentifier;

class Author extends \PKP\author\Author
{
    /**
     * Get whether or not this author should be displayed as an editor
     */
    public function getIsEditor(): bool
    {
        return !!Arr::first(
            $this->getContributorRoles(),
            fn (ContributorRole $cr): bool =>
            $cr->getAttribute('contributor_role_identifier') === ContributorRoleIdentifier::EDITOR->getName()
        );
    }
}
