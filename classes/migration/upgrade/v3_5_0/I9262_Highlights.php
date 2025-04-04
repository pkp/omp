<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I9262_Highlights.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2000-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I9262_Highlights
 *
 * @brief Migrates spotlights to the new highlights model used in all apps
 */

namespace APP\migration\upgrade\v3_5_0;

use APP\core\Application;
use APP\file\PublicFileManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\core\Core;
use PKP\facades\Locale;
use PKP\file\ContextFileManager;
use PKP\install\DowngradeNotSupportedException;
use stdClass;

class I9262_Highlights extends \PKP\migration\upgrade\v3_5_0\I9262_Highlights
{
    public const SPOTLIGHT_TYPE_BOOK = 3; // APP\spotlight\Spotlight::SPOTLIGHT_TYPE_BOOK
    public const SPOTLIGHT_TYPE_SERIES = 4; // APP\spotlight\Spotlight::SPOTLIGHT_TYPE_SERIES

    /**
     * Migrate spotlights into highlights
     */
    public function up(): void
    {
        parent::up();

        $request = Application::get()->getRequest();
        $dispatcher = Application::get()->getDispatcher();
        $validAssocTypes = $this->getValidAssocTypes();
        $contextRows = $this->getContextRows();
        $contextLocales = $this->getContextLocales();

        DB::table('spotlights')
            ->get()
            ->each(function ($row) use ($request, $dispatcher, $validAssocTypes, $contextRows, $contextLocales) {

                if (!$validAssocTypes->contains($row->assoc_type)) {
                    $this->_installer->log("Unable to upgrade spotlight {$row->spotlight_id} to a highlight because the assoc_type {$row->assoc_type} is not recognized.");
                    return;
                }

                if ($row->assoc_type === self::SPOTLIGHT_TYPE_BOOK) {

                    $seriesRow = DB::table('series')
                        ->where('series_id', $row->assoc_id)
                        ->where('press_id', $row->press_id)
                        ->first();

                    if (!$seriesRow) {
                        $this->_installer->log("Unable to upgrade spotlight {$row->spotlight_id} to a highlight because the series {$row->assoc_id} does not exist in the press {$row->press_id}.");
                        return;
                    }

                    $highlightId = DB::table('highlights')->insertGetId([
                        'context_id' => $seriesRow->press_id,
                        'sequence' => $this->getNextSequence($seriesRow->press_id),
                        'url' => $dispatcher->url(
                            $request,
                            Application::ROUTE_PAGE,
                            $contextRows->get($seriesRow->press_id)->path,
                            'catalog',
                            'series',
                            [$seriesRow->path]
                        ),
                    ], 'highlight_id');

                    $image = json_decode($seriesRow->image);
                    if ($image && is_object($image) && $image->name) {
                        $newImageFilename = $this->copySeriesImage($highlightId, $seriesRow->press_id, $image->name);
                        DB::table('highlight_settings')->insert([
                            [
                                'highlight_id' => $highlightId,
                                'setting_name' => 'image',
                                'setting_value' => json_encode(
                                    [
                                        'name' => $image->name,
                                        'uploadName' => $newImageFilename,
                                        'dateUploaded' => Core::getCurrentDate(),
                                        'altText' => '',
                                    ]
                                ),
                            ]
                        ]);
                    }

                    $this->migrateSettings($row->spotlight_id, $highlightId, $contextLocales->get($row->press_id));

                } else {

                    $submissionRow = DB::table('submissions')
                        ->where('submission_id', $row->assoc_id)
                        ->where('context_id', $row->press_id)
                        ->where('status', 3) // \PKP\Submission\Submission::STATUS_PUBLISHED
                        ->first();

                    if (!$submissionRow) {
                        $this->_installer->log("Unable to upgrade spotlight {$row->spotlight_id} to a highlight because the submission {$row->assoc_id} does not exist in the press {$row->press_id}.");
                        return;
                    }

                    $publicationRow = DB::table('publications')
                        ->where('submission_id', $submissionRow->submission_id)
                        ->where('publication_id', $submissionRow->current_publication_id)
                        ->first();

                    if (!$publicationRow) {
                        $this->_installer->log("Unable to upgrade spotlight {$row->spotlight_id} to a highlight because the publication {$submissionRow->current_publication_id} does not exist.");
                        return;
                    }

                    $highlightId = DB::table('highlights')->insertGetId([
                        'context_id' => $submissionRow->context_id,
                        'sequence' => $this->getNextSequence($submissionRow->context_id),
                        'url' => $dispatcher->url(
                            $request,
                            Application::ROUTE_PAGE,
                            $contextRows->get($submissionRow->context_id)->path,
                            'catalog',
                            'book',
                            $publicationRow && $publicationRow->url_path
                                ? [$publicationRow->url_path]
                                : [$submissionRow->submission_id]
                        ),
                    ], 'highlight_id');

                    $imageEncoded = DB::table('publication_settings')
                        ->where('publication_id', $submissionRow->current_publication_id)
                        ->where('setting_name', 'coverImage')
                        ->get()
                        ->sortBy(fn (stdClass $settingRow, int $key) => $settingRow->locale !== $contextRows->get($row->press_id)->primary_locale)
                        ->first()
                        ?->setting_value;

                    $image = $imageEncoded
                        ? json_decode($imageEncoded)
                        : null;

                    if ($image && is_object($image) && $image->uploadName) {
                        $newImageFilename = $this->copySubmissionImage($highlightId, $submissionRow->context_id, $image->uploadName);
                        DB::table('highlight_settings')->insert([
                            [
                                'highlight_id' => $highlightId,
                                'setting_name' => 'image',
                                'setting_value' => json_encode(
                                    [
                                        'name' => $image->uploadName,
                                        'uploadName' => $newImageFilename,
                                        'dateUploaded' => Core::getCurrentDate(),
                                        'altText' => $image?->altText ?? '',
                                    ]
                                ),
                            ]
                        ]);
                    }

                    $this->migrateSettings($row->spotlight_id, $highlightId, $contextLocales->get($row->press_id));
                }
            });

        Schema::drop('spotlight_settings');
        Schema::drop('spotlights');

        DB::table('press_settings')
            ->where('setting_name', 'displayInSpotlight')
            ->delete();
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }

    protected function getValidAssocTypes(): Collection
    {
        return new Collection([
            self::SPOTLIGHT_TYPE_BOOK, // APP\spotlight\Spotlight::SPOTLIGHT_TYPE_BOOK,
            self::SPOTLIGHT_TYPE_SERIES, // APP\spotlight\Spotlight::SPOTLIGHT_TYPE_SERIES,
        ]);
    }

    /**
     * Get the rows of the "presses" table with the Collection keys
     * mapped to the press id
     */
    protected function getContextRows(): Collection
    {
        return DB::table('presses')
            ->get()
            ->mapWithKeys(function (stdClass $row, int $key) {
                return [$row->press_id => $row];
            });
    }

    /**
     * Get the supported form locales for each press
     *
     * Collection keys are mapped to the press id
     */
    protected function getContextLocales(): Collection
    {
        return DB::table('press_settings')
            ->where('setting_name', 'supportedFormLocales')
            ->get()
            ->mapWithKeys(function (stdClass $row, int $key) {
                return [$row->press_id => json_decode($row->setting_value)];
            });
    }

    /**
     * @return string Filename of the new image
     */
    protected function copySubmissionImage(int $highlightId, int $contextId, string $fileName): string
    {
        $filenameParts = explode('.', $fileName);
        $newFilename = $highlightId . '.' . end($filenameParts);

        $publicFileManager = new PublicFileManager();
        $result = $publicFileManager->copyContextFile(
            $contextId,
            join('/', [$publicFileManager->getContextFilesPath($contextId), $fileName]),
            join('/', ['highlights', $newFilename])
        );

        if (!$result) {
            $this->_installer->log("Unable to copy image {$fileName} in the public files directory when migrating a spotlight to highlight id {$highlightId}.");
        }

        return $newFilename;
    }

    /**
     * @return string Filename of the new image
     */
    protected function copySeriesImage(int $highlightId, int $contextId, string $fileName): string
    {
        $contextFileManager = new ContextFileManager($contextId);
        $imagePath = join('/', [
            $contextFileManager->getBasePath(),
            'series',
            $fileName,
        ]);

        $publicFileManager = new PublicFileManager();
        $filenameParts = explode('.', $imagePath);
        $newFilename = $highlightId . '.' . end($filenameParts);

        $result = $publicFileManager->copyContextFile(
            $contextId,
            $imagePath,
            join('/', ['highlights', $newFilename])
        );

        if (!$result) {
            $this->_installer->log("Unable to copy image {$imagePath} to the public files directory when migrating a spotlight to highlight id {$highlightId}.");
        }

        return $newFilename;
    }

    protected function getNextSequence(int $contextId): int
    {
        $lastSequence = DB::table('highlights')
            ->where('context_id', $contextId)
            ->orderBy('sequence', 'desc')
            ->first('sequence')
            ?->sequence;

        return is_null($lastSequence)
            ? 1
            : $lastSequence + 1;
    }

    protected function migrateSettings(int $spotlightId, int $highlightId, array $locales): void
    {
        $newRows = DB::table('spotlight_settings')
            ->where('spotlight_id', $spotlightId)
            ->get()
            ->map(function ($row) use ($highlightId) {
                return [
                    'highlight_id' => $highlightId,
                    'locale' => $row->locale,
                    'setting_name' => $row->setting_name,
                    'setting_value' => $row->setting_value,
                ];
            });

        $initialLocale = Locale::getLocale();

        foreach ($locales as $locale) {
            Locale::setLocale($locale);
            $newRows->add([
                'highlight_id' => $highlightId,
                'locale' => $locale,
                'setting_name' => 'urlText',
                'setting_value' => __('common.readMore'),
            ]);
        }

        Locale::setLocale($initialLocale);

        DB::table('highlight_settings')->insert($newRows->toArray());
    }
}
