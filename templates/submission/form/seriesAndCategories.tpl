{**
 * templates/submission/form/seriesAndCategories.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include series placement and categories for submissions. This template is
 * included in:
 *
 * templates/submission/form/step1.tpl
 * controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl
 * controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl
 *}
{include file="submission/form/categories.tpl"}
{include file="submission/form/series.tpl" includeSeriesPosition=$includeSeriesPosition}
