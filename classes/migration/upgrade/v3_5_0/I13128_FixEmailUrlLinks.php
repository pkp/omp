<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I13128_FixEmailUrlLinks.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I13128_FixEmailUrlLinks
 *
 * @brief Adds the replacements that only occur in the OMP translations
 *
 * See pkp/pkp-lib#13128.
 */

namespace APP\migration\upgrade\v3_5_0;

class I13128_FixEmailUrlLinks extends \PKP\migration\upgrade\v3_5_0\I13128_FixEmailUrlLinks
{
    public function up(): void
    {
        parent::up();

        $this->replace('INDEX_REQUEST', '{$contextUrl}', '<a href="{$contextUrl}">{$contextUrl}</a>', 'href="{$contextUrl}"');
        $this->replace('INDEX_REQUEST', '{$submissionUrl}', '<a href="{$submissionUrl}">{$submissionUrl}</a>', 'href="{$submissionUrl}"');
        $this->replace('REVIEW_RESPONSE_OVERDUE_AUTO', '{$reviewAssignmentUrl}', '<a href="{$reviewAssignmentUrl}">{$reviewAssignmentUrl}</a>', 'href="{$reviewAssignmentUrl}"');

        $this->replace('REVIEW_REQUEST_SUBSEQUENT', '< p>', '<p>');
        $this->replace('REVISED_VERSION_NOTIFY', '</ p>', '</p>');
        $this->replace('EDITOR_ASSIGN', '{ $submissionTitle}', '{$submissionTitle}');
        $this->replace('EDITOR_ASSIGN_PRODUCTION', '{ $submissionTitle}', '{$submissionTitle}');
        $this->replace('EDITOR_ASSIGN_PRODUCTION', '{ $submissionUrl}', '{$submissionUrl}');
        $this->replace('EDITOR_ASSIGN_PRODUCTION', '{$подпис}', '{$signature}');
        $this->replace('EDITOR_ASSIGN_REVIEW', '{ $submissionTitle}', '{$submissionTitle}');
        $this->replace('EDITOR_ASSIGN_REVIEW', '{ $submissionUrl}', '{$submissionUrl}');
        $this->replace('EDITOR_ASSIGN_SUBMISSION', '{ $submissionTitle}', '{$submissionTitle}');
        $this->replace('EDITOR_DECISION_SEND_TO_INTERNAL', '{ $potpis}', '{$signature}');
        $this->replace('EDITOR_DECISION_SEND_TO_INTERNAL', '{ $signature}', '{$signature}');
        $this->replace('EDITOR_DECISION_SEND_TO_INTERNAL', '{$jméno příjemce}', '{$recipientName}');
        $this->replace('INDEX_REQUEST', '{$подпис}', '{$signature}');
        $this->replace('LAYOUT_COMPLETE', '{ $senderName}', '{$senderName}');
        $this->replace('LAYOUT_COMPLETE', '{ $submissionTitle}', '{$submissionTitle}');
        $this->replace('LAYOUT_COMPLETE', '{$jméno odesílatele}', '{$senderName}');
        $this->replace('LAYOUT_COMPLETE', '{$jméno příjemce}', '{$recipientName}');
        $this->replace('LAYOUT_REQUEST', '{$jméno příjemce}', '{$recipientName}');
        $this->replace('LAYOUT_REQUEST', '{$submissionTitle }', '{$submissionTitle}');
        $this->replace('REVIEWER_REGISTER', '{$ password}', '{$password}');
        $this->replace('REVIEWER_REGISTER', '{$ principalContactSignature}', '{$signature}');
        $this->replace('REVIEWER_REGISTER', '{$ корисничко име}', '{$recipientUsername}');
        $this->replace('REVIEWER_REGISTER', '{$подпис}', '{$signature}');
        $this->replace('REVIEW_REQUEST', '>$responseDueDate}', '>{$responseDueDate}');
        $this->replace('REVIEW_REQUEST', '{$ responseDueDate}', '{$responseDueDate}');
        $this->replace('REVIEW_REQUEST_SUBSEQUENT', '{$reviewAssignmentUrl }', '{$reviewAssignmentUrl}');
        $this->replace('USER_REGISTER', '{$ password}', '{$password}');
        $this->replace('USER_REGISTER', '{$ principalContactSignature}', '{$signature}');
        $this->replace('USER_REGISTER', '{$ userFullName}', '{$recipientName}');
        $this->replace('USER_REGISTER', '{$ корисничко име}', '{$recipientUsername}');
        $this->replace('USER_REGISTER', '{$подпис}', '{$signature}');
    }

    public function down(): void
    {
        $this->replace('INDEX_REQUEST', '<a href="{$contextUrl}">{$contextUrl}</a>', '{$contextUrl}');
        $this->replace('INDEX_REQUEST', '<a href="{$submissionUrl}">{$submissionUrl}</a>', '{$submissionUrl}');
        $this->replace('REVIEW_RESPONSE_OVERDUE_AUTO', '<a href="{$reviewAssignmentUrl}">{$reviewAssignmentUrl}</a>', '{$reviewAssignmentUrl}');

        parent::down();
    }
}
