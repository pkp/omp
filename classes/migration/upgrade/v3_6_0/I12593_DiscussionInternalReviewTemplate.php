<?php

/**
 * @file classes/migration/upgrade/v3_6_0/I12593_DiscussionInternalReviewTemplate.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I12593_DiscussionInternalReviewTemplate
 *
 * @brief Migration to install the discussion template for the Internal Review stage
 */

namespace APP\migration\upgrade\v3_6_0;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\editorialTask\enums\EditorialTaskType;
use PKP\migration\Migration;

class I12593_DiscussionInternalReviewTemplate extends Migration
{
    /** The unique key identifying the discussion template for the Internal Review stage */
    protected string $templateKey = 'DISCUSSION_NOTIFICATION_INTERNAL_REVIEW';

    /**
     * @see \PKP\editorialTask\Repository::installTaskTemplates()
     */
    public function up(): void
    {
        $locales = DB::table('site')->pluck('supported_locales')->first();
        $locales = json_decode($locales, true);

        $contextDao = Application::getContextDAO();
        $contextIds = DB::table($contextDao->tableName)
            ->pluck($contextDao->primaryKeyColumn);

        foreach ($contextIds as $contextId) {
            $templateId = DB::table('edit_task_templates')->insertGetId([
                'key' => $this->templateKey,
                'stage_id' => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
                'context_id' => $contextId,
                'type' => EditorialTaskType::DISCUSSION->value,
            ], 'edit_task_template_id');

            foreach ($locales as $locale) {
                DB::table('edit_task_template_settings')->insert([
                    'edit_task_template_id' => $templateId,
                    'locale' => $locale,
                    'setting_name' => 'title',
                    'setting_value' => __('mailable.discussionReview.name', [], $locale),
                ]);

                DB::table('edit_task_template_settings')->insert([
                    'edit_task_template_id' => $templateId,
                    'locale' => $locale,
                    'setting_name' => 'description',
                    'setting_value' => __('emails.discussion.body', [], $locale),
                ]);
            }
        }
    }

    public function down(): void
    {
        $templateIds = DB::table('edit_task_templates')->where('key', $this->templateKey)
            ->pluck('edit_task_template_id')
            ->toArray();
        DB::table('edit_task_template_settings')->whereIn('edit_task_template_id', $templateIds)->delete();
        DB::table('edit_task_templates')->whereIn('edit_task_template_id', $templateIds)->delete();
    }
}
