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

use APP\core\Application;
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
        $doiVersioning = (bool) $context->getData(Context::SETTING_DOI_VERSIONING);

        $q = DB::table($this->table, 'd')
            // Minor versions share their major version's DOIs, so a DOI can match several publications/objects
            ->leftJoin('publications as p', 'd.doi_id', '=', 'p.doi_id')
            ->leftJoin('submission_chapters as cd', 'd.doi_id', '=', 'cd.doi_id')
            ->leftJoin('publications as cp', 'cd.publication_id', '=', 'cp.publication_id')
            ->leftJoin('publication_formats as pfd', 'd.doi_id', '=', 'pfd.doi_id')
            ->leftJoin('publications as pfp', 'pfd.publication_id', '=', 'pfp.publication_id')
            ->leftJoin('submission_files as sfd', 'd.doi_id', '=', 'sfd.doi_id')
            ->where('d.context_id', '=', $context->getId())
            ->where(function (Builder $q) use ($enabledDoiTypes, $doiVersioning) {
                // Publication DOIs
                $q->when(in_array(Repo::doi()::TYPE_PUBLICATION, $enabledDoiTypes), function (Builder $q) use ($doiVersioning) {
                    $q->whereIn('d.doi_id', function (Builder $q) use ($doiVersioning) {
                        $q->select('p.doi_id')
                            ->from('publications', 'p')
                            ->whereNotNull('p.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                        $this->whereDepositablePublication($q, $doiVersioning);
                    });
                });
                // Chapter DOIs
                $q->when(in_array(Repo::doi()::TYPE_CHAPTER, $enabledDoiTypes), function (Builder $q) use ($doiVersioning) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) use ($doiVersioning) {
                        $q->select('spc.doi_id')
                            ->from('submission_chapters', 'spc')
                            ->join('publications as p', 'spc.publication_id', '=', 'p.publication_id')
                            ->whereNotNull('spc.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                        $this->whereDepositablePublication($q, $doiVersioning, false);
                    });
                });
                // Publication format DOIs
                $q->when(in_array(Repo::doi()::TYPE_REPRESENTATION, $enabledDoiTypes), function (Builder $q) use ($doiVersioning) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) use ($doiVersioning) {
                        $q->select('pf.doi_id')
                            ->from('publication_formats', 'pf')
                            ->join('publications as p', 'pf.publication_id', '=', 'p.publication_id')
                            ->whereNotNull('pf.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                        $this->whereDepositablePublication($q, $doiVersioning, false);
                    });
                });
                // Submission file DOIs, of the proof files of a publication format
                $q->when(in_array(Repo::doi()::TYPE_SUBMISSION_FILE, $enabledDoiTypes), function (Builder $q) use ($doiVersioning) {
                    $q->orWhereIn('d.doi_id', function (Builder $q) use ($doiVersioning) {
                        $q->select('sf.doi_id')
                            ->from('submission_files', 'sf')
                            ->join('publication_formats as pf', 'sf.assoc_id', '=', 'pf.publication_format_id')
                            ->join('publications as p', 'pf.publication_id', '=', 'p.publication_id')
                            ->where('sf.assoc_type', '=', Application::ASSOC_TYPE_PUBLICATION_FORMAT)
                            ->where('sf.file_stage', '=', SubmissionFile::SUBMISSION_FILE_PROOF)
                            ->whereNotNull('sf.doi_id')
                            ->where('p.status', '=', PKPPublication::STATUS_PUBLISHED);
                        $this->whereDepositablePublication($q, $doiVersioning, false);
                    });
                });
            })
            ->whereIn('d.status', [Doi::STATUS_UNREGISTERED, Doi::STATUS_ERROR, Doi::STATUS_STALE]);
        return $q->distinct()->get([DB::raw('COALESCE(p.submission_id, cp.submission_id, pfp.submission_id, sfd.submission_id) AS submission_id'), 'd.doi_id']);
    }
}
