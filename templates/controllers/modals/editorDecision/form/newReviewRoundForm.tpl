<!-- templates/controllers/modals/editorDecision/form/newReviewRoundForm.tpl -->

{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

{modal_title id="#newRound" key='editor.monograph.newRound' iconClass="fileManagement" canClose=1}

<p>{translate key="editor.monograph.newRoundDescription"}</p>
<form name="newRound" id="newRound" method="post" action="{url op="saveNewReviewRound"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- Revision files grid (Displays only revisions at first, and hides all other files which can then be displayed with filter button -->
	{url|assign:newRoundRevisionsUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.revisions.RevisionsGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$round isSelectable=1 escape=false}
	{load_url_in_div id="newRoundRevisionsGrid" url=$newRoundRevisionsUrl}
</form>

{init_button_bar id="#newRound" submitText="editor.monograph.createNewRound"}

<!-- / templates/controllers/modals/editorDecision/form/newReviewRoundForm.tpl -->

