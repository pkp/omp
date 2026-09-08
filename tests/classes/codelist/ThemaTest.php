<?php

/**
 * @file tests/classes/codelist/ThemaTest.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ThemaTest
 *
 * @ingroup tests_classes_codelist
 *
 * @see Thema
 *
 * @brief Tests for loading the Thema subject category code list.
 */

namespace APP\tests\classes\codelist;

use APP\codelist\Thema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PKP\tests\PKPTestCase;
use RuntimeException;

#[CoversClass(Thema::class)]
class ThemaTest extends PKPTestCase
{
    private const FIXTURE = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <CodeList>
            <IssueNumber>1.6</IssueNumber>
            <ThemaCodes>
                <Code><CodeValue>A</CodeValue><CodeDescription>The Arts</CodeDescription><IssueNumber>1.0</IssueNumber></Code>
                <Code><CodeValue>AB</CodeValue><CodeDescription>The arts: general topics</CodeDescription></Code>
                <Code><CodeValue>ABA</CodeValue><CodeDescription>Theory of art</CodeDescription></Code>
                <Code><CodeValue>AFCC</CodeValue><CodeDescription>Paintings in watercolours</CodeDescription></Code>
                <Code><CodeValue>AKLC1</CodeValue><CodeDescription>Graphic novel and Manga artwork</CodeDescription></Code>
                <Code><CodeValue>1DDB-BE-B</CodeValue><CodeDescription>Brussels</CodeDescription></Code>
                <Code><CodeValue>2ACB</CodeValue><CodeDescription>English</CodeDescription></Code>
                <Code><CodeValue>6W</CodeValue><CodeDescription>Styles (W)</CodeDescription><CodeNotes>DO NOT USE</CodeNotes></Code>
                <Code><CodeValue>ZZZ</CodeValue><CodeDescription></CodeDescription></Code>
                <Code><CodeValue></CodeValue><CodeDescription>No code</CodeDescription></Code>
            </ThemaCodes>
        </CodeList>
        XML;

    private string $fixturePath;

    /**
     * @see PKPTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturePath = tempnam(sys_get_temp_dir(), 'thema');
        file_put_contents($this->fixturePath, self::FIXTURE);
    }

    /**
     * @see PKPTestCase::tearDown()
     */
    protected function tearDown(): void
    {
        unlink($this->fixturePath);
        parent::tearDown();
    }

    /**
     * Only subject categories are loaded: qualifier codes (which begin with a
     * digit) and entries lacking a code or description are skipped.
     */
    public function testParseFileLoadsSubjectCategoriesOnly(): void
    {
        $entries = $this->parseFile($this->fixturePath);

        self::assertSame(
            [
                'A' => 'The Arts',
                'AB' => 'The arts: general topics',
                'ABA' => 'Theory of art',
                'AFCC' => 'Paintings in watercolours',
                'AKLC1' => 'Graphic novel and Manga artwork',
            ],
            $entries
        );
    }

    /**
     * The shipped EDItEUR file yields subject categories and no qualifiers.
     */
    public function testShippedFileContainsNoQualifierCodes(): void
    {
        $entries = $this->parseFile((new Thema())->getFilename('en'));

        self::assertArrayHasKey('ABA', $entries);
        self::assertArrayHasKey('AKLC1', $entries);
        self::assertGreaterThan(3000, count($entries));
        self::assertSame(
            [],
            preg_grep('/^[A-Z][A-Z0-9]*$/', array_keys($entries), PREG_GREP_INVERT),
            'Expected every loaded code to begin with a letter'
        );
        self::assertArrayNotHasKey('6VC', $entries);
        self::assertArrayNotHasKey('1DDB-BE-B', $entries);
    }

    /**
     * Search results and code validation are limited to loaded subject categories.
     */
    public function testSearchAndValidationUseLoadedEntries(): void
    {
        $thema = $this->themaWithEntries($this->parseFile($this->fixturePath));

        self::assertSame(
            [['name' => 'Theory of art', 'source' => Thema::SOURCE, 'identifier' => 'ABA']],
            $thema->search('theory')
        );
        self::assertSame(['A', 'AB', 'ABA', 'AFCC', 'AKLC1'], array_column($thema->search('a'), 'identifier'));
        self::assertTrue($thema->isValidCode('AFCC'));
        self::assertTrue($thema->isValidCode('A'));
        self::assertFalse($thema->isValidCode('2ACB'));
        self::assertFalse($thema->isValidCode('6W'));
    }

    /**
     * The hierarchy helpers follow Thema's prefix-based nesting.
     */
    public function testHierarchyHelpers(): void
    {
        self::assertTrue(Thema::isAncestorOf('FJ', 'FJH'));
        self::assertTrue(Thema::isAncestorOf('F', 'FJH'));
        self::assertFalse(Thema::isAncestorOf('FJH', 'FJ'));
        self::assertFalse(Thema::isAncestorOf('FJ', 'FJ'));
        self::assertFalse(Thema::isAncestorOf('FJ', 'FK'));

        self::assertSame(
            [['FJH', 'FJHA'], ['FJ', 'FJH'], ['FJ', 'FJHA']],
            Thema::getAncestorConflicts(['FJH', 'FJ', 'FJHA', 'AB', 'FJH'])
        );
        self::assertSame([], Thema::getAncestorConflicts(['FJH', 'FK', 'AB']));
    }

    /**
     * Subject entries are validated against the Thema rules in both modes.
     */
    #[DataProvider('subjectErrorsProvider')]
    public function testGetSubjectErrors(array $entries, bool $required, array $expectedFragments): void
    {
        $thema = $this->themaWithEntries($this->parseFile($this->fixturePath));

        $errors = $thema->getSubjectErrors($entries, $required);

        self::assertCount(count($expectedFragments), $errors, print_r($errors, true));
        foreach ($expectedFragments as $i => $fragment) {
            self::assertStringContainsString($fragment, $errors[$i]);
        }
    }

    /**
     * Each case: entries, whether Thema is required, and a distinctive fragment
     * expected in each error message, in order.
     */
    public static function subjectErrorsProvider(): array
    {
        $aba = ['name' => 'Theory of art', 'source' => Thema::SOURCE, 'identifier' => 'ABA'];
        $ab = ['name' => 'The arts: general topics', 'source' => Thema::SOURCE, 'identifier' => 'AB'];
        $afcc = ['name' => 'Paintings in watercolours', 'source' => Thema::SOURCE, 'identifier' => 'AFCC'];
        $top = ['name' => 'The Arts', 'source' => Thema::SOURCE, 'identifier' => 'A'];
        $unknown = ['name' => 'Bogus', 'source' => Thema::SOURCE, 'identifier' => 'ZZZZ'];
        $freeText = ['name' => 'Free text'];

        return [
            'valid distinct categories' => [[$aba, $afcc], false, []],
            'free text allowed when not required' => [[$aba, $freeText, 'Legacy string'], false, []],
            'free text rejected once when required' => [[$aba, $freeText, 'Legacy string'], true, ['Thema subject classification']],
            'top-level allowed on its own' => [[$top], true, []],
            'top-level is an ancestor of its descendants' => [[$top, $afcc], false, ['A and AFCC']],
            'unknown code rejected' => [[$unknown], false, ['ZZZZ']],
            'ancestor conflict rejected' => [[$aba, $ab], false, ['AB and ABA']],
            'ancestor conflict alongside free text when required' => [[$ab, $freeText, $aba], true, ['AB and ABA', 'Thema subject classification']],
        ];
    }

    /**
     * The list version comes from the header, not from a code's own issue number.
     */
    public function testParseVersionReadsTheHeaderIssueNumber(): void
    {
        self::assertSame('1.6', $this->parseVersion($this->fixturePath));
        self::assertSame('1.6', $this->parseVersion((new Thema())->getFilename('en')));

        file_put_contents($this->fixturePath, '<CodeList><ThemaCodes/></CodeList>');
        self::assertNull($this->parseVersion($this->fixturePath));
    }

    /**
     * A missing code list raises an exception instead of yielding an empty
     * (and cacheable) list.
     */
    public function testParseFileThrowsWhenFileCannotBeOpened(): void
    {
        $this->expectException(RuntimeException::class);
        $this->parseFile($this->fixturePath . '.missing');
    }

    /**
     * Parse a file directly, bypassing the cache.
     */
    private function parseFile(string $filename): array
    {
        return (new class () extends Thema {
            public function parse(string $filename): array
            {
                return $this->parseFile($filename);
            }
        })->parse($filename);
    }

    /**
     * Read a file's version directly, bypassing the cache.
     */
    private function parseVersion(string $filename): ?string
    {
        return (new class () extends Thema {
            public function version(string $filename): ?string
            {
                return $this->parseVersion($filename);
            }
        })->version($filename);
    }

    /**
     * Build a Thema instance whose entries are fixed, bypassing the cache.
     */
    private function themaWithEntries(array $entries): Thema
    {
        return new class ($entries) extends Thema {
            public function __construct(private array $entries)
            {
            }

            public function getEntries(?string $locale = null): array
            {
                return $this->entries;
            }
        };
    }
}
