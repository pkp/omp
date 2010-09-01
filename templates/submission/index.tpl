<!-- templates/submission/index.tpl -->

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
			 * Display the submission list corresponding to the
			 * given role id.
			 * @param roleId integer
			 */
			var displayList = function(roleId) {ldelim}
				var authorListMarkup =
					'{init_tabs|escape:javascript id="#authorSubmissions"}' +
					'<div id="authorSubmissions">' +
					'	<ul>' +
					'		<li><a href="{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="active"}">{translate key="common.queue.short.active"}</a></li>' +
					'		<li><a href="{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="completed"}">{translate key="common.queue.short.completed"}</a></li>' +
					'	</ul>' +
					'</div>';
	
				{url|assign:editorSubmissionListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid"}
				var editorListMarkup =
					'{load_url_in_div|escape:javascript id="editorSubmissionListGrid" url="$editorSubmissionListGridUrl"}';

				switch(roleId) {ldelim}
					case {$smarty.const.ROLE_ID_AUTHOR}:
						$('#submissionList').html(authorListMarkup);
						break;
					
					case {$smarty.const.ROLE_ID_PRESS_MANAGER}:
					case {$smarty.const.ROLE_ID_SERIES_EDITOR}:
						$('#submissionList').html(editorListMarkup);
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

<!-- / templates/submission/index.tpl -->

