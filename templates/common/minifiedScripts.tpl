{**
 * templates/common/minifiedScripts.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * This file contains a list of all JavaScript files that should be compiled
 * for distribution.
 *
 * NB: Please make sure that you add your scripts in the same format as the
 * existing files because this file will be parsed by the build script.
 *}

{* External jQuery plug-ins to be minified *}
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.form.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.tag-it.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.sortElements.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.cookie.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.equalizer.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.jlabel-1.3.min.js"></script>
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.selectBox.min.js"></script>

{* JSON library *}
<script src="{$baseUrl}/lib/pkp/js/lib/json/json2.js"></script>

{* Our own functions (depend on plug-ins) *}
<script src="{$baseUrl}/lib/pkp/js/functions/general.js"></script>

{* Our own classes (depend on plug-ins) *}
<script src="{$baseUrl}/lib/pkp/js/classes/Helper.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/ObjectProxy.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/Handler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/TinyMCEHelper.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/LinkActionRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/RedirectRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/OpenWindowRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/PostAndRedirectRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/NullAction.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/EventAction.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/AjaxRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/linkAction/ModalRequest.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/notification/NotificationHelper.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/Feature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/OrderItemsFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/OrderGridItemsFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/OrderCategoryGridItemsFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/OrderListbuilderItemsFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/OrderMultipleListsItemsFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/GridCategoryAccordionFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/GeneralPagingFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/PagingFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/InfiniteScrollingFeature.js"></script>
<script src="{$baseUrl}/lib/pkp/js/classes/features/CollapsibleGridFeature.js"></script>
{* Generic controllers *}
<script src="{$baseUrl}/lib/pkp/js/controllers/SiteHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/UrlInDivHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/ExtrasOnDemandHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/PageHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/TabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/MenuHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/UploaderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/AutocompleteHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/RangeSliderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/RevealMoreHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/NotificationHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/HelpPanelHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/FormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/DropdownHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/AjaxFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/ClientFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/ToggleFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/FileUploadFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/MultilingualInputHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/CancelActionAjaxFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/UserFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/form/reviewer/ReviewerReviewStep3FormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/GridHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/CategoryGridHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/listbuilder/ListbuilderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/listbuilder/MultipleListsListbuilderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/ModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/ConfirmationModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/RedirectConfirmationModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/RemoteActionConfirmationModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/ButtonConfirmationModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/JsEventConfirmationModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/AjaxModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modal/WizardModalHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/modals/editorDecision/form/EditorDecisionFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/linkAction/LinkActionHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/wizard/WizardHandler.js"></script>

{* Specific controllers *}
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/queries/ReadQueryHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/FileUploadWizardHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/form/FileUploadFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/wizard/fileUpload/form/RevisionConfirmationHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/form/FileViewFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/announcements/form/AnnouncementSettingsFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/permissions/form/PermissionSettingsFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/paymentMethod/PaymentMethodHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/notifications/NotificationsGridHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/header/HeaderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/header/TasksHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/admin/ContextsHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/submission/SubmissionTabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/settings/user/form/UserDetailsFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/settings/roles/form/UserGroupFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/settings/metadata/MetadataGridHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/siteAccessOptions/form/SiteAccessOptionsFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/settings/managementSettings/UsersAndRolesTabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/workflow/WorkflowTabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/tab/representations/RepresentationsTabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/dashboard/form/DashboardTaskFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/informationCenter/NotesHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/statistics/ReportGeneratorFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/submission/SubmissionStep2FormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/submission/SubmissionStep3FormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/workflow/WorkflowHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/workflow/SubmissionHeaderHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/reviewer/AdvancedReviewerSearchHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/reviewer/ReadReviewHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/reviewer/form/EditReviewFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/reviewer/form/AddReviewerFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/stageParticipant/form/StageParticipantNotifyHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/controllers/grid/users/stageParticipant/form/AddParticipantFormHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/reviewer/ReviewerTabHandler.js"></script>
<script src="{$baseUrl}/lib/pkp/js/pages/authorDashboard/SubmissionEmailHandler.js"></script>
<script src="{$baseUrl}/js/controllers/grid/catalogEntry/form/PublicationFormatFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/grid/content/spotlights/form/SpotlightFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/modals/editorDecision/ApproveProofsHandler.js"></script>
<script src="{$baseUrl}/js/controllers/modals/catalogEntry/form/RepresentativeFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/modals/catalogEntry/form/PublicationFormatMetadataFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/modals/submissionMetadata/MonographlessCatalogEntryHandler.js"></script>
<script src="{$baseUrl}/js/controllers/grid/files/proof/form/ApprovedProofFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/tab/catalogEntry/CatalogEntryTabHandler.js"></script>
<script src="{$baseUrl}/js/controllers/catalog/form/CatalogMetadataFormHandler.js"></script>
<script src="{$baseUrl}/js/controllers/monographList/MonographListHandler.js"></script>
<script src="{$baseUrl}/js/pages/manageCatalog/ManageCatalogHeaderHandler.js"></script>

{* Our own plug-in (depends on classes) *}
<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.pkp.js"></script>
