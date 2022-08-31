<?php

/**
 * @file classes/mail/mailables/DecisionSendInternalReviewNotifyAuthor.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DecisionSendInternalReviewNotifyAuthor
 *
 * @brief Email sent to the author(s) when a Decision::INTERNAL_REVIEW
 *  decision is made.
 */

namespace APP\mail\mailables;

use PKP\mail\mailables\DecisionSendExternalReviewNotifyAuthor;
use PKP\mail\traits\Recipient;
use PKP\mail\traits\Sender;

class DecisionSendInternalReviewNotifyAuthor extends DecisionSendExternalReviewNotifyAuthor
{
    use Recipient;
    use Sender;

    public const REVIEW_TYPE_DESCRIPTION_VARIABLE = 'reviewTypeDescription';

    protected static ?string $name = 'mailable.decision.sendInternalReview.notifyAuthor.name';
    protected static ?string $description = 'mailable.decision.sendInternalReview.notifyAuthor.description';
    protected static ?string $emailTemplateKey = 'EDITOR_DECISION_SEND_TO_INTERNAL';
}
