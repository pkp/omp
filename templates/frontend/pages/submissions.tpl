{**
 * templates/frontend/pages/submissions.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view submission guidelines
 *
 * @uses $submissionInfo array Page content such as guidelines, checklist, etc
 *}
{include file="common/frontend/header.tpl" pageTitle="about.submissions"}

<div class="page page_submissions">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="about.submissions"}
	{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="guidelines" sectionTitleKey="about.submissions"}

	{* Login/register prompt *}
	{capture assign="login"}
		<a href="{url page="login"}">{translate key="about.onlineSubmissions.login"}</a>
	{/capture}
	{capture assign="register"}
		<a href="{url page="user" op="register"}">{translate key="about.onlineSubmissions.register"}</a>
	{/capture}
	<p>
		{translate key="about.onlineSubmissions.registrationRequired" login=$login register=$register}
	</p>

	{if $submissionInfo.authorGuidelines}
		{* id is used in a link in the submission checklist *}
		<div id="authorGuidelines" class="author_guidelines">
			<h2>
				{translate key="about.authorGuidelines"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="guidelines" sectionTitleKey="about.authorGuidelines"}
			</h2>
			{$submissionInfo.authorGuidelines|nl2br|strip_unsafe_html}
		</div>
	{/if}

	{if $submissionInfo.checklist}
		<div class="submission_checklist">
			<h2>
				{translate key="about.submissionPreparationChecklist"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="publication" anchor="submissionStage" sectionTitleKey="about.submissionPreparationChecklist"}
			</h2>
			{translate key="about.submissionPreparationChecklist.description"}
			<ul>
				{foreach from=$submissionInfo.checklist item=checklistItem}
					<li>
						{$checklistItem.content|nl2br|strip_unsafe_html}
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}

	{if $submissionInfo.copyrightNotice}
		<div class="copyright">
			<h2>
				{translate key="about.copyrightNotice"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="distribution" anchor="permissions" sectionTitleKey="about.copyrightNotice"}
			</h2>
			{$submissionInfo.copyrightNotice|nl2br|strip_unsafe_html}
		</div>
	{/if}

	{if $submissionInfo.privacyStatement}
		<div class="privacy">
			<h2>
				{translate key="about.privacyStatement"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitleKey="about.privacyStatement"}
			</h2>
			<p>
				{$submissionInfo.privacyStatement|nl2br|strip_unsafe_html}
			</p>
		</div>
	{/if}

	{if $submissionInfo.reviewPolicy}
		<div class="review">
			<h2>
				{translate key="about.reviewPolicy"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitleKey="about.reviewPolicy"}
			</h2>
			<p>
				{$submissionInfo.reviewPolicy|nl2br|strip_unsafe_html}
			</p>
		</div>
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
