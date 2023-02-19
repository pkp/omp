<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I5932_RenameAndGenerateSeriesUrlPaths.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I5932_RenameAndGenerateSeriesUrlPaths
 * @brief Rename section urlPath column and generate url paths.
 */

namespace APP\migration\upgrade\v3_4_0;

use APP\core\Application;
use APP\facades\Repo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\migration\Migration;
use Stringy\Stringy;

class I5932_RenameAndGenerateSeriesUrlPaths extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // pkp/pkp-lib#5932 Rename urlPath for series
        Schema::table('series', function (Blueprint $table) {
            $table->dropUnique('series_path');
            $table->renameColumn('path', 'url_path');
            $table->unique(['press_id', 'url_path'], 'series_path');
        });

        $contextDao = Application::getContextDAO();
        $seriesIterator = Repo::section()->getCollector()->filterByContextIds(DB::table('presses')->pluck('press_id')->toArray())->getMany();

        foreach ($seriesIterator as $series) {
            if (!$series->getUrlPath()) {
                $context = $contextDao->getById($series->getContextId());
                $seriesTitle = $series->getLocalizedData('title', $context->getPrimaryLocale());
                $seriesUrlpath = (string) Stringy::create($seriesTitle)->toAscii()->toLowerCase()->dasherize()->regexReplace('[^a-z0-9\-\_.]', '');
                $series->setUrlPath($seriesUrlpath);
                Repo::section()->edit($series, []);
            }
        }
    }

    /**
     * Reverse the downgrades
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('series_path');
            $table->renameColumn('url_path', 'path');
            $table->unique(['press_id', 'path'], 'series_path');
        });
    }
}
