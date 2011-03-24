{**
 * step3.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of press setup.
 *}
{assign var="pageTitle" value="manager.setup.preparingWorkflow"}
{include file="manager/setup/setupHeader.tpl"}

<form id="setupForm" method="post" action="{url op="saveSetup" path="3"}" enctype="multipart/form-data">
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

<h3>3.1 {translate key="manager.setup.genres"}</h3>

<p>{translate key="manager.setup.genresDescription"}</p>

{url|assign:genresUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.genre.GenreGridHandler" op="fetchGrid"}
{load_url_in_div id="genresContainer" url=$genresUrl}

<div class="separator"></div>

<h3>3.2 {translate key="manager.setup.submissionLibrary"}</h3>

{url|assign:submissionLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_SUBMISSION}
{load_url_in_div id="submissionLibraryGridDiv" url=$submissionLibraryUrl}

<div class="separator"></div>

<h3>3.3 {translate key="manager.setup.reviewLibrary"}</h3>

{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
{load_url_in_div id="reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

<div class="separator"></div>

<h3>3.4 {translate key="manager.setup.reviewForms"}</h3>

{url|assign:reviewFormGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.reviewForm.ReviewFormGridHandler" op="fetchGrid"}
{load_url_in_div id="reviewFormGridDiv" url=$reviewFormGridUrl}

<div class="separator"></div>

<h3>3.5 {translate key="manager.setup.editorialLibrary"}</h3>

{url|assign:editorialLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_EDITORIAL}
{load_url_in_div id="editorialLibraryGridDiv" url=$editorialLibraryGridUrl}

<div class="separator"></div>

<h3>3.6 {translate key="manager.setup.productionLibrary"}</h3>

{url|assign:productionLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION}
{load_url_in_div id="productionLibraryGridDiv" url=$productionLibraryGridUrl}

<div class="separator"></div>

<h3>3.7 {translate key="manager.setup.productionTemplates"}</h3>

{url|assign:productionTemplateLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION_TEMPLATE}
{load_url_in_div id="productionTemplateLibraryDiv" url=$productionTemplateLibraryUrl}

<div class="separator"></div>

<h3>3.8 {translate key="manager.setup.publicationFormats"}</h3>

<p>{translate key="manager.setup.publicationFormatsDescription"}</p>

{url|assign:publicationFormatsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.PublicationFormatsListbuilderHandler" op="fetch"}
{load_url_in_div id="publicationFormatsContainer" url=$publicationFormatsUrl}

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>

{include file="common/footer.tpl"}
