{**
 * minifiedScripts.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
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
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.imgpreview.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.cookie.js"></script>

{* Our own functions (depend on plug-ins) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/fontController.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/general.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/modal.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/listbuilder.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/functions/jqueryValidatorI18n.js"></script>

{* Our own classes (depend on plug-ins) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/Helper.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/ObjectProxy.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/Handler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/LinkActionRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/RedirectRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/AjaxRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/classes/linkAction/ModalRequest.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/FormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/TabbedHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/WizardHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/UploaderHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/grid/GridHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/ModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/ConfirmationModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/AjaxModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/modal/WizardModalHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/linkAction/LinkActionHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/files/submissionFiles/FileUploadWizardHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/files/submissionFiles/form/FileUploadFormHandler.js"></script>
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/controllers/files/submissionFiles/form/RevisionConfirmationHandler.js"></script>

<script type="text/javascript" src="{$baseUrl}/js/controllers/InformationCenterHandler.js"></script>

{* Our own plug-in (depends on classes) *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.pkp.js"></script>
