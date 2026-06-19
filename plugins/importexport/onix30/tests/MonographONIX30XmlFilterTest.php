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
use PHPUnit\Framework\MockObject\MockObject;
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

        $doc = $filter->process($submission);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('onix', self::ONIX_NS);

        // Root message
        $root = $doc->documentElement;
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
    }

    //
    // Fixture helpers
    //

    /**
     * Construct the filter with a deployment and press.
     */
    private function createFilter(): MonographONIX30XmlFilter
    {
        $filterGroup = new FilterGroup();
        $filterGroup->setInputType('primitive::string');
        $filterGroup->setOutputType('primitive::string');

        $filter = new MonographONIX30XmlFilter($filterGroup);
        $filter->setDeployment(new Onix30ExportDeployment($this->createPress(), null));

        return $filter;
    }

    /**
     * Create a minimal press.
     */
    private function createPress(): Press
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
        return $press;
    }

    /**
     * Create a minimal monograph.
     */
    private function createMonograph(): Submission
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

    private function xpathString(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)->item(0);
        self::assertNotNull($node, "Expected a node for XPath: {$query}");
        return $node->textContent;
    }
}
