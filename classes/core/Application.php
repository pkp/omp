<?php

/**
 * @file classes/core/Application.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Application
 *
 * @ingroup core
 *
 * @see PKPApplication
 *
 * @brief Class describing this application.
 *
 */

namespace APP\core;

use APP\payment\omp\OMPPaymentManager;
use APP\press\PressDAO;
use PKP\context\Context;
use PKP\core\PKPApplication;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\submission\RepresentationDAOInterface;

class Application extends PKPApplication
{
    public const ASSOC_TYPE_MONOGRAPH = self::ASSOC_TYPE_SUBMISSION;
    public const ASSOC_TYPE_PUBLICATION_FORMAT = self::ASSOC_TYPE_REPRESENTATION;
    public const ASSOC_TYPE_PRESS = 0x0000200;
    public const ASSOC_TYPE_SERIES = self::ASSOC_TYPE_SECTION;
    public const ASSOC_TYPE_CHAPTER = 0x0000214;
    public const REQUIRES_XSL = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        if (!PKP_STRICT_MODE) {
            foreach ([
                'ASSOC_TYPE_MONOGRAPH',
                'ASSOC_TYPE_PUBLICATION_FORMAT',
                'ASSOC_TYPE_PRESS',
                'ASSOC_TYPE_SERIES',
                'ASSOC_TYPE_CHAPTER',
            ] as $constantName) {
                if (!defined($constantName)) {
                    define($constantName, constant('self::' . $constantName));
                }
            }
            if (!class_exists('\Application')) {
                class_alias('\APP\core\Application', '\Application');
            }
        }

        // Add application locales
        Locale::registerPath(BASE_SYS_DIR . '/locale');
    }

    /**
     * Get the name of the application context.
     */
    public function getContextName(): string
    {
        return 'press';
    }

    /**
     * Get the symbolic name of this application
     */
    public static function getName(): string
    {
        return 'omp';
    }

    /**
     * Get the locale key for the name of this application.
     */
    public function getNameKey(): string
    {
        return('common.software');
    }

    /**
     * Get the URL to the XML descriptor for the current version of this
     * application.
     */
    public function getVersionDescriptorUrl(): string
    {
        return 'https://pkp.sfu.ca/omp/xml/omp-version.xml';
    }

    /**
     * Get the map of DAOName => full.class.Path for this application.
     */
    public function getDAOMap(): array
    {
        return array_merge(parent::getDAOMap(), [
            'ChapterDAO' => 'APP\monograph\ChapterDAO',
            'FeatureDAO' => 'APP\press\FeatureDAO',
            'IdentificationCodeDAO' => 'APP\publicationFormat\IdentificationCodeDAO',
            'LayoutAssignmentDAO' => 'submission\layoutAssignment\LayoutAssignmentDAO',
            'MarketDAO' => 'APP\publicationFormat\MarketDAO',
            'NewReleaseDAO' => 'APP\press\NewReleaseDAO',
            'OAIDAO' => 'APP\oai\omp\OAIDAO',
            'OMPCompletedPaymentDAO' => 'APP\payment\omp\OMPCompletedPaymentDAO',
            'ONIXCodelistItemDAO' => 'APP\codelist\ONIXCodelistItemDAO',
            'PressDAO' => 'APP\press\PressDAO',
            'ProductionAssignmentDAO' => 'APP\submission\productionAssignment\ProductionAssignmentDAO',
            'PublicationDateDAO' => 'APP\publicationFormat\PublicationDateDAO',
            'PublicationFormatDAO' => 'APP\publicationFormat\PublicationFormatDAO',
            'QualifierDAO' => 'APP\codelist\QualifierDAO',
            'RepresentativeDAO' => 'APP\monograph\RepresentativeDAO',
            'SalesRightsDAO' => 'APP\publicationFormat\SalesRightsDAO',
            'SubjectDAO' => 'APP\codelist\SubjectDAO',
            'TemporaryTotalsDAO' => 'APP\statistics\TemporaryTotalsDAO',
            'TemporaryItemInvestigationsDAO' => 'APP\statistics\TemporaryItemInvestigationsDAO',
            'TemporaryItemRequestsDAO' => 'APP\statistics\TemporaryItemRequestsDAO',
            'TemporaryTitleInvestigationsDAO' => 'APP\statistics\TemporaryTitleInvestigationsDAO',
            'TemporaryTitleRequestsDAO' => 'APP\statistics\TemporaryTitleRequestsDAO',
        ]);
    }

    /**
     * Get the list of plugin categories for this application.
     */
    public function getPluginCategories(): array
    {
        return [
            // NB: Meta-data plug-ins are first in the list as this
            // will make them being loaded (and installed) first.
            // This is necessary as several other plug-in categories
            // depend on meta-data. This is a very rudimentary type of
            // dependency management for plug-ins.
            'metadata',
            'pubIds',
            'blocks',
            'generic',
            'gateways',
            'themes',
            'importexport',
            'oaiMetadataFormats',
            'paymethod',
            'reports',
        ];
    }

    /**
     * Get the top-level context DAO.
     */
    public static function getContextDAO(): PressDAO
    {
        return DAORegistry::getDAO('PressDAO');
    }

    /**
     * Get the representation DAO.
     */
    public static function getRepresentationDAO(): DAO|RepresentationDAOInterface
    {
        return DAORegistry::getDAO('PublicationFormatDAO');
    }

    /**
     * Get the stages used by the application.
     */
    public static function getApplicationStages(): array
    {
        // We leave out WORKFLOW_STAGE_ID_PUBLISHED since it technically is not a 'stage'.
        return [
            WORKFLOW_STAGE_ID_SUBMISSION,
            WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
            WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
            WORKFLOW_STAGE_ID_EDITING,
            WORKFLOW_STAGE_ID_PRODUCTION
        ];
    }

    /**
     * Get the review workflow stages used by this application.
     */
    public function getReviewStages(): array
    {
        return [
            WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
            WORKFLOW_STAGE_ID_INTERNAL_REVIEW
        ];
    }

    /**
     * Get the file directory array map used by the application.
     */
    public static function getFileDirectories(): array
    {
        return ['context' => '/presses/', 'submission' => '/monographs/'];
    }

    /**
     * Returns the context type for this application.
     */
    public static function getContextAssocType(): int
    {
        return self::ASSOC_TYPE_PRESS;
    }

    /**
     * Get the payment manager.
     */
    public function getPaymentManager(Context $context): OMPPaymentManager
    {
        return new OMPPaymentManager($context);
    }

    public static function getSectionIdPropName(): string
    {
        return 'seriesId';
    }

    /**
     * Define if the application has customizable reviewer recommendation functionality
     */
    public function hasCustomizableReviewerRecommendation(): bool
    {
        return true;
    }

    /**
     * Get the help URL of this application
     */
    public static function getHelpUrl(): string
    {
        return 'https://docs.pkp.sfu.ca/learning-omp/en/';
    }
}
