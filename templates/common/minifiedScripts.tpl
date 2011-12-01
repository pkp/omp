{**
 * templates/common/minifiedScripts.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * This file contains a list of all JavaScript files that should be compiled
 * for distribution.
 *
 * NB: Please make sure that you add your scripts in the same format as the
 * exiting files because this file will be parsed by the build script.
 *}

{* External jQuery plug-ins to be minified *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.form.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.tag-it.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.pnotify.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.sortElements.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.imgpreview.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.cookie.js"></script>

{* JSON library *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/json/json2.js"></script>

{* Our own functions (depend on plug-ins) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/fontController.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/general.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/modal.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/jqueryValidatorI18n.js"></script>

{* Our own classes (depend on plug-ins) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/Helper.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/ObjectProxy.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/Handler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/LinkActionRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/RedirectRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/NullAction.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/AjaxRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/ModalRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/notification/NotificationHelper.js"></script>

{* Generic controllers *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/SiteHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/PageHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/TabHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/ExtrasOnDemandHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/UploaderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/AutocompleteHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/RangeSliderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/NotificationHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/FormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/DropdownFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/AjaxFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/ClientFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/FileUploadFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/form/MultilingualInputHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/grid/GridHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/listbuilder/ListbuilderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/ModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/ConfirmationModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/RemoteActionConfirmationModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/CallbackConfirmationModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/ButtonConfirmationModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/AjaxModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/WizardModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/linkAction/LinkActionHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/wizard/WizardHandler.js"></script>

{* Specific controllers *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/FileUploadWizardHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/form/FileUploadFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/form/RevisionConfirmationHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/grid/users/reviewer/AdvancedReviewerSearchHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/grid/users/stageParticipant/form/AddParticipantFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/grid/files/signoff/form/AddAuditorFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/informationCenter/InformationCenterHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/informationCenter/NotesHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/modals/editorDecision/form/EditorDecisionFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/modals/submissionMetadata/MonographlessCatalogEntryHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/grid/settings/user/form/UserFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/tab/settings/form/FileViewFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/tab/settings/siteAccessOptions/form/SiteAccessOptionsFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/controllers/tab/settings/homepage/form/HomepageFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/authorDashboard/AuthorDashboardHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/catalog/CatalogHeaderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/catalog/CatalogSearchFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/catalog/MonographListHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/catalog/MonographHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/workflow/SubmissionHeaderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/workflow/ProductionHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/pages/admin/PressesHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/js/site/form/PressSwitcherFormHandler.js"></script>

{* Our own plug-in (depends on classes) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.pkp.js"></script>
