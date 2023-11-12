<?php

/**
 * @file classes/facades/Repo.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repo
 *
 * @brief Extends the base Repo facade with any overrides for OMP
 */

namespace APP\facades;

use APP\author\Repository as AuthorRepository;
use APP\decision\Repository as DecisionRepository;
use APP\doi\Repository as DoiRepository;
use APP\mail\Repository as MailRepository;
use APP\publication\Repository as PublicationRepository;
use APP\section\Repository as SectionRepository;
use APP\submission\Repository as SubmissionRepository;
use APP\submissionFile\Repository as SubmissionFileRepository;
use APP\user\Repository as UserRepository;
use PKP\submission\reviewAssignment\Repository as ReviewAssignmentRepository;

class Repo extends \PKP\facades\Repo
{
    public static function author(): AuthorRepository
    {
        return app(AuthorRepository::class);
    }

    public static function decision(): DecisionRepository
    {
        return app(DecisionRepository::class);
    }

    public static function doi(): DoiRepository
    {
        return app()->make(DoiRepository::class);
    }

    public static function publication(): PublicationRepository
    {
        return app(PublicationRepository::class);
    }

    public static function section(): SectionRepository
    {
        return app(SectionRepository::class);
    }

    public static function submission(): SubmissionRepository
    {
        return app(SubmissionRepository::class);
    }

    public static function submissionFile(): SubmissionFileRepository
    {
        return app(SubmissionFileRepository::class);
    }

    public static function user(): UserRepository
    {
        return app(UserRepository::class);
    }

    public static function mailable(): MailRepository
    {
        return app(MailRepository::class);
    }

    public static function reviewAssignment(): ReviewAssignmentRepository
    {
        return app(ReviewAssignmentRepository::class);
    }
}
