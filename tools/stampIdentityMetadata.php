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
 * @brief CLI tool to re-stamp press identity metadata onto publications from the press's
 *   current settings. Use this to backfill historical records or to correct stamps after
 *   a press identity change.
 */

use APP\core\Application;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\PressDAO;
use PKP\cliTool\CommandLineTool;

require(dirname(__FILE__) . '/bootstrap.php');

class StampPressIdentityMetadata extends CommandLineTool
{
    // =========================================================================
    // OVERRIDE SECTION
    // Fill in these values if the data to be stamped differs from the press's
    // current live settings. Leave as null to use the press's current settings.
    // =========================================================================

    /**
     * Press name per locale.
     * e.g. ['en' => 'Old Press Name', 'fr' => 'Ancien nom de presse']
     *
     * @var array<string,string>|null
     */
    public ?array $contextNameOverride = null;

    /** @var string|null Publisher name, e.g. 'Old Publisher Name' */
    public ?string $publisherOverride = null;

    /** @var string|null Publisher location (city), e.g. 'Berlin' */
    public ?string $publisherLocationOverride = null;

    /** @var string|null Publisher code type */
    public ?string $codeTypeOverride = null;

    /** @var string|null Publisher code value */
    public ?string $codeValueOverride = null;

    // =========================================================================

    public int $contextId;
    public string $command;
    public array $parameters;

    /**
     * Constructor.
     *
     * @param array $argv command-line arguments
     */
    public function __construct($argv = [])
    {
        parent::__construct($argv);
        if (count($this->argv) < 2) {
            $this->usage();
            exit();
        }
        $this->contextId = (int)array_shift($this->argv);
        $this->command = array_shift($this->argv);
        $this->parameters = $this->argv;
    }

    /**
     * Print command usage information.
     */
    public function usage()
    {
        echo "Re-stamps press identity metadata (name, publisher) onto publications\n"
            . "from the press's current settings.\n"
            . "To stamp with different values, edit the override variables at the top of this file.\n"
            . "Usage:\n"
            . "\t{$this->scriptName} <context_id> submission_id <id> [<id> ...]\n"
            . "\t{$this->scriptName} <context_id> year <year_or_range> [<year_or_range> ...]\n"
            . "\t{$this->scriptName} <context_id> all\n"
            . "Year ranges are specified as YYYY-YYYY, e.g. 2010-2020.\n"
            . "Use 'all' to stamp everything: all published publications in the press.\n";
    }

    /**
     * Re-stamp identity metadata on the targeted publications.
     */
    public function execute()
    {
        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();
        /** @var Press $context */
        $context = $contextDao->getById($this->contextId);
        if (!$context) {
            printf("Error: Unknown context ID %d.\n", $this->contextId);
            exit(1);
        }

        if ($this->command === 'all') {
            $this->stampAll($context);
            return;
        }

        if ($this->command === 'submission_id') {
            $submissionIds = array_map('intval', $this->parameters);
            if (empty($submissionIds)) {
                echo "Error: No submission IDs provided.\n";
                $this->usage();
                exit(1);
            }
            foreach ($submissionIds as $submissionId) {
                $this->stampSubmission($submissionId, $context);
            }
            return;
        }

        if ($this->command === 'year') {
            $years = $this->parseYears($this->parameters);
            if (empty($years)) {
                echo "Error: No valid years or year ranges provided.\n";
                exit(1);
            }
            $this->stampByYear($context, $years);
            return;
        }

        printf("Error: Unknown command '%s'. Expected 'submission_id', 'year', or 'all'.\n", $this->command);
        $this->usage();
        exit(1);
    }

    /**
     * Stamp all published publications in the press.
     */
    protected function stampAll(Press $context): void
    {
        $count = 0;
        $submissions = Repo::submission()->getCollector()
            ->filterByContextIds([$this->contextId])
            ->getMany();

        foreach ($submissions as $submission) {
            $count += $this->stampPublications($submission->getId(), $context);
        }

        printf("Stamped %d publication(s) in total.\n", $count);
    }

    /**
     * Stamp all published publications of a single submission.
     */
    protected function stampSubmission(int $submissionId, Press $context): void
    {
        $submission = Repo::submission()->get($submissionId, $this->contextId);
        if (!$submission) {
            printf("Error: Skipping submission %d — unknown or does not belong to context %d.\n", $submissionId, $this->contextId);
            return;
        }

        $count = $this->stampPublications($submissionId, $context);
        printf("Stamped %d publication(s) for submission %d.\n", $count, $submissionId);
    }

    /**
     * Stamp publications published in the given years.
     */
    protected function stampByYear(Press $context, array $years): void
    {
        $count = 0;
        $submissions = Repo::submission()->getCollector()
            ->filterByContextIds([$this->contextId])
            ->getMany();

        foreach ($submissions as $submission) {
            $publications = Repo::publication()->getCollector()
                ->filterBySubmissionIds([$submission->getId()])
                ->getMany();

            foreach ($publications as $publication) {
                $datePublished = $publication->getData('datePublished');
                if (!$datePublished || !in_array((int) date('Y', strtotime($datePublished)), $years)) {
                    continue;
                }
                $publication->stampContextIdentity($context);
                $this->applyOverrides($publication);
                Repo::publication()->edit($publication, []);
                $count++;
            }
        }

        if ($count > 0) {
            printf("Stamped %d publication(s) published in the given year(s).\n", $count);
        } else {
            echo "No publications found published in the given year(s).\n";
        }
    }

    /**
     * Stamp all publications of a submission, returning the count stamped.
     */
    protected function stampPublications(int $submissionId, Press $context): int
    {
        $count = 0;
        $publications = Repo::publication()->getCollector()
            ->filterBySubmissionIds([$submissionId])
            ->getMany();

        foreach ($publications as $publication) {
            $publication->stampContextIdentity($context);
            $this->applyOverrides($publication);
            Repo::publication()->edit($publication, []);
            $count++;
        }

        return $count;
    }

    /**
     * Apply any user-specified overrides to a publication after stampContextIdentity().
     * Only non-null overrides are applied; null means "use whatever stampContextIdentity() set".
     */
    protected function applyOverrides(object $object): void
    {
        if ($this->contextNameOverride !== null) {
            foreach ($this->contextNameOverride as $locale => $name) {
                $object->setData('contextName', $name, $locale);
            }
        }
        if ($this->publisherOverride !== null) {
            $object->setData('publisher', $this->publisherOverride);
        }
        if ($this->publisherLocationOverride !== null) {
            $object->setData('publisherLocation', $this->publisherLocationOverride);
        }
        if ($this->codeTypeOverride !== null) {
            $object->setData('codeType', $this->codeTypeOverride);
        }
        if ($this->codeValueOverride !== null) {
            $object->setData('codeValue', $this->codeValueOverride);
        }
    }

    /**
     * Parse a list of year strings (e.g. '2010', '2015-2020') into a flat array of integers.
     */
    protected function parseYears(array $params): array
    {
        $years = [];
        foreach ($params as $param) {
            if (str_contains($param, '-')) {
                [$start, $end] = explode('-', $param, 2);
                if (strlen($start) === 4 && ctype_digit($start) && strlen($end) === 4 && ctype_digit($end)) {
                    foreach (range((int)$start, (int)$end) as $year) {
                        $years[$year] = true;
                    }
                } else {
                    printf("Warning: Skipping invalid year range '%s'.\n", $param);
                }
            } elseif (strlen($param) === 4 && ctype_digit($param)) {
                $years[(int)$param] = true;
            } else {
                printf("Warning: Skipping invalid year '%s'.\n", $param);
            }
        }
        return array_keys($years);
    }
}

$tool = new StampPressIdentityMetadata($argv ?? []);
$tool->execute();
