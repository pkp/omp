<?php

/**
 * @file tests/jobs/statistics/CompileSubmissionGeoDailyMetricsTest.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Tests for compile submission geo daily metrics job.
 */

namespace APP\tests\jobs\statistics;

use APP\jobs\statistics\CompileSubmissionGeoDailyMetrics;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PKP\db\DAORegistry;
use PKP\tests\PKPTestCase;

#[RunTestsInSeparateProcesses]
#[CoversClass(CompileSubmissionGeoDailyMetrics::class)]
class CompileSubmissionGeoDailyMetricsTest extends PKPTestCase
{
    /**
     * base64_encoded serializion from OMP 3.4.0
     */
    protected string $serializedJobData = <<<END
    O:52:"APP\jobs\statistics\CompileSubmissionGeoDailyMetrics":3:{s:9:"\0*\0loadId";s:25:"usage_events_20240130.log";s:10:"connection";s:8:"database";s:5:"queue";s:5:"queue";}
    END;

    /**
     * Test job is a proper instance
     */
    public function testUnserializationGetProperDepositIssueJobInstance(): void
    {
        $this->assertInstanceOf(
            CompileSubmissionGeoDailyMetrics::class,
            unserialize($this->serializedJobData)
        );
    }

    /**
     * Ensure that a serialized job can be unserialized and executed
     */
    public function testRunSerializedJob(): void
    {
        /** @var CompileSubmissionGeoDailyMetrics $compileSubmissionGeoDailyMetricsJob */
        $compileSubmissionGeoDailyMetricsJob = unserialize($this->serializedJobData);

        $temporaryTotalsDAOMock = Mockery::mock(\APP\statistics\TemporaryTotalsDAO::class)
            ->makePartial()
            ->shouldReceive([
                'deleteSubmissionGeoDailyByLoadId' => null,
                'compileSubmissionGeoDailyMetrics' => null,
            ])
            ->withAnyArgs()
            ->getMock();

        DAORegistry::registerDAO('TemporaryTotalsDAO', $temporaryTotalsDAOMock);

        $temporaryTitleInvestigationsDAOMock = Mockery::mock(\APP\statistics\TemporaryTitleInvestigationsDAO::class)
            ->makePartial()
            ->shouldReceive([
                'compileSubmissionGeoDailyMetrics' => null,
            ])
            ->withAnyArgs()
            ->getMock();

        DAORegistry::registerDAO('TemporaryTitleInvestigationsDAO', $temporaryTitleInvestigationsDAOMock);

        $compileSubmissionGeoDailyMetricsJob->handle();

        $this->expectNotToPerformAssertions();
    }
}
