{**
 * submissionName.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to accept the submission and send it for review
 *}
{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.editorReviewFileSelection.EditorReviewFileSelectionGridHandler" op="fetchGrid" monographId=$monographId}
{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}