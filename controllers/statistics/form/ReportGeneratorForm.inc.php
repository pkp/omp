<?php

/**
 * @file controllers/statistics/form/ReportGeneratorForm.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReportGeneratorForm
 * @ingroup controllers_statistics_form
 * @see Form
 *
 * @brief Form to generate custom statistics reports.
 */

import('lib.pkp.controllers.statistics.form.PKPReportGeneratorForm');

class ReportGeneratorForm extends PKPReportGeneratorForm {

	/**
	 * Constructor
	 */
	function ReportGeneratorForm($columns, $objects, $fileTypes, $metricType, $defaultReportTemplates, $reportTemplateIndex = null) {
		parent::PKPReportGeneratorForm($columns, $objects, $fileTypes, $metricType, $defaultReportTemplates, $reportTemplateIndex);
	}

	/**
	 * @see PKPReportGeneratorForm::getFileAssocTypes()
	 */
	function getFileAssocTypes() {
		return array(ASSOC_TYPE_SUBMISSION_FILE);
	}
}

?>
