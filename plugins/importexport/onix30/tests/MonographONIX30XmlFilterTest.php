<?php

/**
 * @file plugins/importexport/onix30/tests/MonographONIX30XmlFilterTest.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographONIX30XmlFilterTest
 *
 * @ingroup plugins_importexport_onix30
 *
 * @brief Functional tests for the ONIX 3.0 monograph export filter.
 */

namespace APP\plugins\importexport\onix30\tests;

use APP\codelist\ONIXCodelistItemDAO;
use APP\codelist\Thema;
use APP\core\Request;
use APP\monograph\RepresentativeDAO;
use APP\plugins\importexport\onix30\filter\MonographONIX30XmlFilter;
use APP\plugins\importexport\onix30\Onix30ExportDeployment;
use APP\press\Press;
use APP\publication\Publication;
use APP\publicationFormat\IdentificationCode;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use DOMXPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\context\Context;
use PKP\core\Dispatcher;
use PKP\core\Registry;
use PKP\db\DAORegistry;
use PKP\filter\FilterGroup;
use PKP\tests\PKPTestCase;

#[CoversClass(MonographONIX30XmlFilter::class)]
class MonographONIX30XmlFilterTest extends PKPTestCase
{
    private const ONIX_NS = 'http://ns.editeur.org/onix/3.0/reference';
    private const TEST_ISBN = '9780000000001';
    private const FORMAT_ID = 100;
    private const THEMA_SUBJECT_XPATH = "//onix:Subject[onix:SubjectSchemeIdentifier='93']";

    /** Subjects fixture: two Thema entries, one free-text entry and one legacy string entry. */
    private const SUBJECTS = [
        ['name' => 'Theory of art', 'source' => Thema::SOURCE, 'identifier' => 'ABA'],
        ['name' => 'Free text subject'],
        'Legacy string subject',
        ['name' => 'Paintings and painting in watercolours or pastels', 'source' => Thema::SOURCE, 'identifier' => 'AFCC'],
    ];

    /**
     * @see PKPTestCase::getMockedDAOs()
     */
    protected function getMockedDAOs(): array
    {
        return [...parent::getMockedDAOs(), 'ONIXCodelistItemDAO', 'RepresentativeDAO'];
    }

    /**
     * @see PKPTestCase::getMockedRegistryKeys()
     */
    protected function getMockedRegistryKeys(): array
    {
        return [...parent::getMockedRegistryKeys(), 'request'];
    }

    /**
     * The export produces expected ONIX 3.0 elements for a basic monograph.
     */
    public function testProcessProducesExpectedElements(): void
    {
        $this->registerMockDaos();
        $this->registerMockRequest();

        $filter = $this->createFilter();
        $submission = $this->createMonograph();

        $xpath = $this->processToXPath($filter, $submission);

        // Root message
        $root = $xpath->document->documentElement;
        self::assertSame('ONIXMessage', $root->localName);
        self::assertSame('3.0', $root->getAttribute('release'));

        // Header / Sender
        self::assertSame('Test Press', $this->xpathString($xpath, '//onix:Header/onix:Sender/onix:SenderName'));

        // Exactly one Product for the single publication format
        self::assertSame(1, $xpath->query('//onix:Product')->length);
        self::assertStringContainsString(
            '.testpress.' . self::FORMAT_ID,
            $this->xpathString($xpath, '//onix:Product/onix:RecordReference')
        );
        self::assertSame('03', $this->xpathString($xpath, '//onix:Product/onix:NotificationType'));

        // ISBN product identifier
        self::assertSame(
            self::TEST_ISBN,
            $this->xpathString($xpath, "//onix:ProductIdentifier[onix:ProductIDType='15']/onix:IDValue")
        );

        // Title
        self::assertSame(
            'A Basic Monograph',
            $this->xpathString($xpath, '//onix:TitleElement/onix:TitleWithoutPrefix')
        );

        // Keywords
        self::assertSame(
            'History; Science',
            $this->xpathString($xpath, "//onix:Subject[onix:SubjectSchemeIdentifier='20']/onix:SubjectHeadingText")
        );

        // No authors -> NoContributor element is emitted
        self::assertSame(1, $xpath->query('//onix:DescriptiveDetail/onix:NoContributor')->length);

        // Publisher is present
        self::assertSame(1, $xpath->query("//onix:Publisher[onix:PublishingRole='01']")->length);
        // Without FundingPlugin, no funding-body publisher is emitted
        self::assertSame(0, $xpath->query("//onix:Publisher[onix:PublishingRole='16']")->length);
    }

    /**
     * Subjects chosen from the Thema vocabulary are exported as coded subjects
     * (subject scheme 93) when the press has enabled or required Thema, with the
     * first flagged as the main subject, while free-text and legacy string
     * subjects are never exported as coded subjects.
     */
    #[DataProvider('themaSettingsProvider')]
    public function testProcessExportsThemaSubjectsWhenEnabled(array $pressData): void
    {
        $this->registerMockDaos();
        $this->registerMockRequest();

        $filter = $this->createFilter($pressData);
        $submission = $this->createMonograph(['subjects' => ['en' => self::SUBJECTS]]);

        $xpath = $this->processToXPath($filter, $submission);

        $themaSubjects = $xpath->query(self::THEMA_SUBJECT_XPATH);
        self::assertSame(2, $themaSubjects->length);
        self::assertSame(
            ['ABA', 'AFCC'],
            array_map(fn ($node) => $xpath->evaluate('string(onix:SubjectCode)', $node), iterator_to_array($themaSubjects))
        );
        self::assertSame(
            'Theory of art',
            $this->xpathString($xpath, self::THEMA_SUBJECT_XPATH . '[1]/onix:SubjectHeadingText')
        );
        self::assertSame(
            ['1.6', '1.6'],
            array_map(fn ($node) => $xpath->evaluate('string(onix:SubjectSchemeVersion)', $node), iterator_to_array($themaSubjects))
        );
        self::assertSame(
            ['MainSubject', 'SubjectSchemeIdentifier', 'SubjectSchemeVersion', 'SubjectCode', 'SubjectHeadingText'],
            array_map(fn ($node) => $node->localName, iterator_to_array($themaSubjects->item(0)->childNodes)),
            'Subject children must follow the ONIX schema order'
        );
        self::assertSame(1, $xpath->query('//onix:Subject/onix:MainSubject')->length);
        self::assertSame('ABA', $this->xpathString($xpath, '//onix:Subject[onix:MainSubject]/onix:SubjectCode'));
        self::assertSame(
            'MainSubject',
            $themaSubjects->item(0)->firstChild->localName,
            'MainSubject must be the first child of the Subject composite'
        );
        self::assertSame(0, $xpath->query("//onix:Subject[onix:SubjectHeadingText='Free text subject']")->length);
        self::assertSame(0, $xpath->query("//onix:Subject[onix:SubjectHeadingText='Legacy string subject']")->length);
    }

    /**
     * Press settings under which Thema subjects are exported.
     */
    public static function themaSettingsProvider(): array
    {
        return [
            'enabled' => [[Thema::SETTING => Context::METADATA_ENABLE]],
            'required' => [[Thema::SETTING => Context::METADATA_REQUIRE]],
        ];
    }

    /**
     * No coded Thema subjects are exported when the press has not enabled Thema,
     * even if Thema entries are stored on the publication.
     */
    public function testProcessOmitsThemaSubjectsWhenDisabled(): void
    {
        $this->registerMockDaos();
        $this->registerMockRequest();

        $filter = $this->createFilter();
        $submission = $this->createMonograph(['subjects' => ['en' => self::SUBJECTS]]);

        $xpath = $this->processToXPath($filter, $submission);

        self::assertSame(0, $xpath->query(self::THEMA_SUBJECT_XPATH)->length);
        self::assertSame(0, $xpath->query('//onix:Subject/onix:SubjectCode')->length);
    }

    /**
     * getFundingData() returns null when the FundingPlugin is not installed.
     */
    public function testGetFundingDataReturnsNullWhenFundingPluginDisabled(): void
    {
        $filter = $this->createFilter();
        self::assertNull($filter->getFundingData(1, 1));
    }

    //
    // Fixture helpers
    //

    /**
     * Construct the filter with a deployment and press. Extra press settings can
     * be supplied to exercise setting-dependent behaviour.
     */
    private function createFilter(array $pressData = []): MonographONIX30XmlFilter
    {
        $filterGroup = new FilterGroup();
        $filterGroup->setInputType('primitive::string');
        $filterGroup->setOutputType('primitive::string');

        $filter = new MonographONIX30XmlFilter($filterGroup);
        $filter->setDeployment(new Onix30ExportDeployment($this->createPress($pressData), null));

        return $filter;
    }

    /**
     * Create a minimal press, with any extra settings applied on top.
     */
    private function createPress(array $data = []): Press
    {
        $press = new Press();
        $press->setId(1);
        $press->setData('primaryLocale', 'en');
        $press->setData('name', ['en' => 'Test Press']);
        $press->setData('urlPath', 'testpress');
        $press->setData('contactName', 'Press Contact');
        $press->setData('contactEmail', 'press@example.org');
        $press->setData('publisher', 'Test Publisher');
        $press->setData('codeType', '01');
        $press->setData('codeValue', 'TEST');
        foreach ($data as $key => $value) {
            $press->setData($key, $value);
        }
        return $press;
    }

    /**
     * Create a minimal monograph, with any extra publication data applied on top.
     */
    private function createMonograph(array $publicationData = []): Submission
    {
        $publicationFormat = $this->createPublicationFormat();

        /** @var Publication&MockObject $publication */
        $publication = $this->getMockBuilder(Publication::class)
            ->onlyMethods(['getCoverImageUrl'])
            ->getMock();
        $publication->method('getCoverImageUrl')->willReturn('');
        $publication->setData('submissionId', 9);
        $publication->setData('locale', 'en');
        $publication->setData('title', 'A Basic Monograph', 'en');
        $publication->setData('abstract', 'A short abstract.', 'en');
        $publication->setData('keywords', ['en' => [['name' => 'History'], ['name' => 'Science']]]);
        $publication->setData('authors', collect([]));
        $publication->setData('publicationFormats', collect([$publicationFormat]));
        foreach ($publicationData as $key => $value) {
            $publication->setData($key, $value);
        }

        /** @var Submission&MockObject $submission */
        $submission = $this->getMockBuilder(Submission::class)
            ->onlyMethods(['getCurrentPublication'])
            ->getMock();
        $submission->method('getCurrentPublication')->willReturn($publication);

        return $submission;
    }

    /**
     * A physical publication format with a single ISBN identification code and no
     * markets/sales rights/publishing dates.
     */
    private function createPublicationFormat(): PublicationFormat
    {
        $isbn = new IdentificationCode();
        $isbn->setCode('15'); // ISBN-13
        $isbn->setValue(self::TEST_ISBN);

        /** @var PublicationFormat&MockObject $format */
        $format = $this->getMockBuilder(PublicationFormat::class)
            ->onlyMethods([
                'getId',
                'getPhysicalFormat',
                'getIdentificationCodes',
                'getMarkets',
                'getSalesRights',
                'getPublicationDates',
            ])
            ->getMock();
        $format->method('getId')->willReturn(self::FORMAT_ID);
        $format->method('getPhysicalFormat')->willReturn(true);
        // Use willReturnCallback so each call yields a fresh iterator
        $format->method('getIdentificationCodes')->willReturnCallback(fn () => $this->fakeIterator([$isbn]));
        $format->method('getMarkets')->willReturnCallback(fn () => $this->fakeIterator([]));
        $format->method('getSalesRights')->willReturnCallback(fn () => $this->fakeIterator([]));
        $format->method('getPublicationDates')->willReturnCallback(fn () => $this->fakeIterator([]));
        $format->setData('entryKey', 'BC'); // ProductForm: paperback
        return $format;
    }

    /**
     * Register a stub codelist DAO.
     */
    private function registerMockDaos(): void
    {
        /** @var ONIXCodelistItemDAO&MockObject $codelistDao */
        $codelistDao = $this->getMockBuilder(ONIXCodelistItemDAO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['codeExistsInList'])
            ->getMock();
        $codelistDao->method('codeExistsInList')->willReturn(false);
        DAORegistry::registerDAO('ONIXCodelistItemDAO', $codelistDao);

        /** @var RepresentativeDAO&MockObject $representativeDao */
        $representativeDao = $this->getMockBuilder(RepresentativeDAO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getById'])
            ->getMock();
        $representativeDao->method('getById')->willReturn(null);
        DAORegistry::registerDAO('RepresentativeDAO', $representativeDao);
    }

    /**
     * Register a request mock providing the accessors the filter needs.
     */
    private function registerMockRequest(): void
    {
        /** @var Dispatcher&MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->onlyMethods(['url'])
            ->getMock();
        $dispatcher->method('url')->willReturn('https://example.org/testpress');

        /** @var Request&MockObject $request */
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getServerHost', 'getDispatcher', 'url'])
            ->getMock();
        $request->method('getServerHost')->willReturn('example.org');
        $request->method('getDispatcher')->willReturn($dispatcher);
        $request->method('url')->willReturn('https://example.org/testpress/catalog/book/9');

        Registry::set('request', $request);
    }

    /**
     * Minimal stand-in for a DAOResultFactory.
     */
    private function fakeIterator(array $items): object
    {
        return new class ($items) {
            private array $items;

            public function __construct(array $items)
            {
                $this->items = array_values($items);
            }

            public function next()
            {
                return array_shift($this->items) ?? false;
            }
        };
    }

    /**
     * Run the filter and return an XPath evaluator over the resulting ONIX document.
     */
    private function processToXPath(MonographONIX30XmlFilter $filter, Submission $submission): DOMXPath
    {
        $xpath = new DOMXPath($filter->process($submission));
        $xpath->registerNamespace('onix', self::ONIX_NS);
        return $xpath;
    }

    private function xpathString(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)->item(0);
        self::assertNotNull($node, "Expected a node for XPath: {$query}");
        return $node->textContent;
    }
}
