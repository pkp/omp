{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Monograph index.
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{* FIXME: This page does not have a spec, just temporary layout, see #5849 *}
<div id="submissionList">
	<script type='text/javascript'>
		$(function(){ldelim}
			/**
			 * Generate mark-up for a tabbed submission component
			 * (i.e. authors' and reviewers' submission grid).
			 * @param roleId integer
			 */
			var getTabbedListMarkup = function(roleId) {ldelim}
				var authorUrls = {ldelim}
					'active': '{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="active"}',
					'completed': '{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="completed"}'
				{rdelim};
				var reviewerUrls = {ldelim}
					'active': '{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.reviewer.ReviewerSubmissionsListGridHandler" op="fetchGrid" status="active"}',
					'completed': '{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.reviewer.ReviewerSubmissionsListGridHandler" op="fetchGrid" status="completed"}'
				{rdelim};
				
				// Choose the correct endpoint URLs for list retrieval.
				var urls;
				switch(roleId) {ldelim}
					case {$smarty.const.ROLE_ID_AUTHOR}:
						urls = authorUrls;
						break;
					
					case {$smarty.const.ROLE_ID_REVIEWER}:
						urls = reviewerUrls;
						break;

					default:
						return '';
				{rdelim}

				// Generate the mark-up.
				var listMarkup =
					'{init_tabs|escape:javascript id="#submissions"}' +
					'<div id="submissions">' +
					'	<ul>' +
					'		<li><a href="' + urls.active + '">{translate key="common.queue.short.active"}</a></li>' +
					'		<li><a href="' + urls.completed + '">{translate key="common.queue.short.completed"}</a></li>' +
					'	</ul>' +
					'</div>';
				return listMarkup;
			{rdelim};

			/**
			 * Generate mark-up for the editor
			 * submission list component
			 */
			var getEditorListMarkup = function() {ldelim}
				{url|assign:editorSubmissionListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid"}
				return '{load_url_in_div|escape:javascript id="editorSubmissionListGrid" url="$editorSubmissionListGridUrl"}';
			{rdelim};

			/**
			 * Display the submission list corresponding to the
			 * given role id.
			 * @param roleId integer
			 */
			var displayList = function(roleId) {ldelim}
				switch(roleId) {ldelim}
					case {$smarty.const.ROLE_ID_AUTHOR}:
					case {$smarty.const.ROLE_ID_REVIEWER}:
						$('#submissionList').html(getTabbedListMarkup(roleId));
						break;
					
					case {$smarty.const.ROLE_ID_PRESS_MANAGER}:
					case {$smarty.const.ROLE_ID_SERIES_EDITOR}:
						$('#submissionList').html(getEditorListMarkup());
						break;
				{rdelim}
			{rdelim};
			
			// Bind to the user-group change event.
			$('body').bind('user-group-change', function(event, newRoleId, previousRoleId) {ldelim}
				if (newRoleId !== previousRoleId) {ldelim}
					displayList(newRoleId);
				{rdelim}
			{rdelim});

			// Show the initial list.
			displayList({$roleId});
		{rdelim});
	</script>
</div>

{include file="common/footer.tpl"}

