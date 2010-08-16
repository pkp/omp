<!-- templates/settings/setup/step3.tpl -->

{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="settings.setup.preparingWorkflow"}
{include file="settings/setup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSetup" path="3"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
{fbvFormArea id="locales"}
{fbvFormSection title="form.formLanguage" for="languageSelector"}
	{fbvCustomElement}
		{url|assign:"setupFormUrl" op="setup" path="1"}
		{form_language_chooser form="setupForm" url=$setupFormUrl}
		<span class="instruct">{translate key="form.formLanguage.description"}</span>
	{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1*}

<h3>3.1 {translate key="settings.setup.pressRoles"}</h3>

<p>{translate key="settings.setup.pressRolesDescription"}</p>

{url|assign:authorRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_AUTHOR title='settings.setup.authorRole'}
{load_url_in_div id="#authorRolesContainer" url=$authorRolesUrl}

{url|assign:pressRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_REVIEWER title='settings.setup.pressRole'}
{load_url_in_div id="#pressRolesContainer" url=$pressRolesUrl}

{url|assign:settingsialRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_PRESS_MANAGER title='settings.setup.settingsialRole'}
{load_url_in_div id="#settingsialRolesContainer" url=$settingsialRolesUrl}

<div class="separator"></div>

<h3>3.2 {translate key="settings.setup.submissionRoles"}</h3>

<p>{translate key="settings.setup.submissionRolesDescription"}</p>

{url|assign:submissionRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupStageAssignmentListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_AUTHOR stageId=$smarty.const.WORKFLOW_STAGE_ID_SUBMISSION title="settings.setup.submissionRoles" escape=false}
{load_url_in_div id="#submissionRolesContainer" url=$submissionRolesUrl}

<div class="separator"></div>

<h3>3.3 {translate key="settings.setup.bookFileTypes"}</h3>

<p>{translate key="settings.setup.bookFileTypesDescription"}</p>

{url|assign:bookFileTypesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.bookFileType.BookFileTypeGridHandler" op="fetchGrid"}
{load_url_in_div id="#bookFileTypesContainer" url=$bookFileTypesUrl}

<div class="separator"></div>

<h3>3.4 {translate key="settings.setup.submissionLibrary"}</h3>

{url|assign:submissionLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_SUBMISSION}
{load_url_in_div id="#submissionLibraryGridDiv" url=$submissionLibraryUrl}

<div class="separator"></div>

<h3>3.5 {translate key="settings.setup.internalReviewRoles"}</h3>

<p>{translate key="settings.setup.internalReviewRolesDescription"}</p>

{url|assign:internalReviewRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupStageAssignmentListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_REVIEWER stageId=$smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW title="settings.setup.internalReviewRoles" escape=false}
{load_url_in_div id="#internalReviewRolesContainer" url=$internalReviewRolesUrl}

<div class="separator"></div>

<h3>3.6 {translate key="settings.setup.externalReviewRoles"}</h3>

<p>{translate key="settings.setup.externalReviewRolesDescription"}</p>

{url|assign:externalReviewRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupStageAssignmentListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_REVIEWER stageId=$smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW title="settings.setup.externalReviewRoles" escape=false}
{load_url_in_div id="#externalReviewRolesContainer" url=$externalReviewRolesUrl}

<div class="separator"></div>

<h3>3.7 {translate key="settings.setup.reviewLibrary"}</h3>

{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
{load_url_in_div id="#reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

<div class="separator"></div>

<h3>3.8 {translate key="settings.setup.reviewForms"}</h3>

{url|assign:reviewFormGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.reviewForm.ReviewFormGridHandler" op="fetchGrid"}
{load_url_in_div id="#reviewFormGridDiv" url=$reviewFormGridUrl}

<div class="separator"></div>

<h3>3.9 {translate key="settings.setup.editorialRoles"}</h3>

<p>{translate key="settings.setup.editorialRolesDescription"}</p>

{url|assign:editorialRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupStageAssignmentListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_EDITOR stageId=$smarty.const.WORKFLOW_STAGE_ID_EDITING title="settings.setup.editorialRoles" escape=false}
{load_url_in_div id="#editorialRolesContainer" url=$editorialRolesUrl}

<div class="separator"></div>

<h3>3.10 {translate key="settings.setup.editorialLibrary"}</h3>

{url|assign:editorialLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_EDITORIAL}
{load_url_in_div id="#editorialLibraryGridDiv" url=$editorialLibraryGridUrl}

<div class="separator"></div>

<h3>3.11 {translate key="settings.setup.productionRoles"}</h3>

<p>{translate key="settings.setup.productionRolesDescription"}</p>

{url|assign:productionRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupStageAssignmentListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_EDITOR stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION title="settings.setup.productionRoles" escape=false}
{load_url_in_div id="#productionRolesContainer" url=$productionRolesUrl}

<div class="separator"></div>

<h3>3.12 {translate key="settings.setup.productionLibrary"}</h3>

{url|assign:productionLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION}
{load_url_in_div id="#productionLibraryGridDiv" url=$productionLibraryGridUrl}

<div class="separator"></div>

<h3>3.13 {translate key="settings.setup.productionTemplates"}</h3>

{url|assign:productionTemplateLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION_TEMPLATE}
{load_url_in_div id="#productionTemplateLibraryDiv" url=$productionTemplateLibraryUrl}

<div class="separator"></div>

<h3>3.14 {translate key="settings.setup.publicationFormats"}</h3>

<p>{translate key="settings.setup.publicationFormatsDescription"}</p>

{url|assign:publicationFormatsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.PublicationFormatsListbuilderHandler" op="fetch"}
{load_url_in_div id="#publicationFormatsContainer" url=$publicationFormatsUrl}

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>

{include file="common/footer.tpl"}

<!-- / templates/settings/setup/step3.tpl -->

