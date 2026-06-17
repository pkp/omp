<?php

/**
 * @file tools/stampIdentityMetadata.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StampPressIdentityMetadata
 *
 * @ingroup tools
 *
 * @brief CLI tool to re-stamp the press identity metadata onto published publications.
 *   Use this to backfill historical records or to correct stamps after a press identity change.
 */

use APP\codelist\ONIXCodelistItemDAO;
use PKP\cliTool\StampIdentityMetadataTool;
use PKP\db\DAORegistry;

require(dirname(__FILE__) . '/bootstrap.php');

class StampPressIdentityMetadata extends StampIdentityMetadataTool
{
    /**
     * @copydoc StampIdentityMetadataTool::getContextNoun()
     */
    protected function getContextNoun(): string
    {
        return 'press';
    }

    /**
     * @copydoc StampIdentityMetadataTool::getLocalizedOptions()
     *
     * Presses have no abbreviation setting, so the press initials are stamped as abbreviation.
     */
    protected function getLocalizedOptions(): array
    {
        $options = parent::getLocalizedOptions();
        $options['abbreviation']['label'] = 'Initials';
        $options['abbreviation']['description'] = 'Press initials, as used in citations';
        return $options;
    }

    /**
     * @copydoc StampIdentityMetadataTool::getScalarOptions()
     */
    protected function getScalarOptions(): array
    {
        $options = parent::getScalarOptions();
        return [
            'primary-locale' => $options['primary-locale'],
            'publisher' => ['field' => 'publisher', 'label' => 'Publisher', 'description' => 'Publisher name'],
            'publisher-location' => $options['publisher-location'],
            'code-type' => ['field' => 'codeType', 'label' => 'Publisher code type', 'description' => 'Publisher code type, an ONIX code from list 44, e.g. 01'],
            'code-value' => ['field' => 'codeValue', 'label' => 'Publisher code', 'description' => 'Publisher code'],
        ];
    }

    /**
     * @copydoc StampIdentityMetadataTool::parseOptions()
     *
     * Also checks the code type against ONIX list 44.
     */
    protected function parseOptions(): void
    {
        parent::parseOptions();
        $codeType = $this->scalarOverrides['codeType'] ?? '';
        if ($codeType !== '') {
            /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
            $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
            if (!$onixCodelistItemDao->codeExistsInList($codeType, '44')) {
                $this->exitWithError("Invalid --code-type '{$codeType}': not a code in ONIX list 44.");
            }
        }
    }
}

$tool = new StampPressIdentityMetadata($argv ?? []);
$tool->execute();
