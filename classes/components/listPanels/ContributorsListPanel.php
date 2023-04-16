<?php
/**
 * @file classes/components/listPanels/ContributorsListPanel.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ContributorsListPanel
 *
 * @ingroup classes_components_list
 *
 * @brief A ListPanel component for viewing and editing contributors
 */

namespace APP\components\listPanels;

use APP\components\forms\publication\ContributorForm;

class ContributorsListPanel extends \PKP\components\listPanels\ContributorsListPanel
{
    protected function getForm(string $url): ContributorForm
    {
        return new ContributorForm(
            $url,
            $this->locales,
            $this->submission,
            $this->context
        );
    }
}
