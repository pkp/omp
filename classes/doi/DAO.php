<?php

/**
 * @file classes/doi/DAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DAO
 *
 * @ingroup doi
 *
 * @see Doi
 *
 * @brief Operations for retrieving and modifying Doi objects.
 */

namespace APP\doi;

use APP\facades\Repo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PKP\context\Context;
use PKP\doi\Doi;
use PKP\publication\PKPPublication;
use PKP\submissionFile\SubmissionFile;

class DAO extends \PKP\doi\DAO
{
    /**
     * Gets all depositable submission IDs along with all associated DOI IDs for use in DOI bulk deposit jobs.
     * This method is used to collect all valid submissions/IDs in a single query specifically for use with
     * queued jobs for depositing DOIs with a registration agency.
     *
     */
    public function getAllDepositableSubmissionIds(Context $context): Collection
    {
        $enabledDoiTypes = $context->getData(Context::SETTING_ENABLED_DOI_TYPES) ?? [];

        $q = DB::table($this->table, 'd')
            ->leftJoin('publications as p', 'd.doi_id', '=', 'p.doi_id')
            ->leftJoin('submissions as s', 'p.publication_id', '=', 's.current_publication_id')
            ->where('d.context_id', '=', $context->getId())
            ->where(function (Builder $q) use ($enabledDoiTypes) {
                // Publication DOIs
                $q->when(in_array(Repo::doi()::TYPE_PUBLICATION, $enabledDoiTypes), function (Builder $q) {
                    $q->whereIn('d.doi_id', function (Builder $q) {
                        $q->select('p.doi_id')
                            ->from('publications', 'p')
                            ->leftJoin('submissions as s', 'p.publication_id', '=', 's.current_publication_id')
                            ->whereColumn('p.publication_id', '=', 's.current_publication_id')
                            ->whereNotNull('p.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                    });
                });
                // Chapter DOIs
                $q->when(in_array(Repo::doi()::TYPE_CHAPTER, $enabledDoiTypes), function (Builder $q) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) {
                        $q->select('spc.doi_id')
                            ->from('submission_chapters', 'spc')
                            ->join('publications as p', 'spc.publication_id', '=', 'p.publication_id')
                            ->leftJoin('submissions as s', 'p.publication_id', '=', 's.current_publication_id')
                            ->whereColumn('p.publication_id', '=', 's.current_publication_id')
                            ->whereNotNull('spc.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                    });
                });
                // Publication format DOIs
                $q->when(in_array(Repo::doi()::TYPE_REPRESENTATION, $enabledDoiTypes), function (Builder $q) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) {
                        $q->select('pf.doi_id')
                            ->from('publication_formats', 'pf')
                            ->join('publications as p', 'pf.publication_id', '=', 'p.publication_id')
                            ->leftJoin('submissions as s', 'p.publication_id', '=', 's.current_publication_id')
                            ->whereColumn('p.publication_id', '=', 's.current_publication_id')
                            ->whereNotNull('pf.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                    });
                });
                // Submission file DOIs
                $q->when(in_array(Repo::doi()::TYPE_SUBMISSION_FILE, $enabledDoiTypes), function (Builder $q) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) {
                        $q->select('sf.doi_id')
                            ->from('submission_files', 'sf')
                            ->join('submissions as s', 's.submission_id', '=', 'sf.submission_id')
                            ->leftJoin('publications as p', 's.current_publication_id', '=', 'p.publication_id')
                            ->whereColumn('p.publication_id', '=', 's.current_publication_id')
                            ->where('sf.file_stage', '=', SubmissionFile::SUBMISSION_FILE_PROOF)
                            ->whereNotNull('sf.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                    });
                });
            })
            ->whereIn('d.status', [Doi::STATUS_UNREGISTERED, Doi::STATUS_ERROR, Doi::STATUS_STALE]);
        return $q->get(['s.submission_id', 'd.doi_id']);
    }
}
