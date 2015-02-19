<?php

/**
 * @file tests/functional/setup/PaymentSettingsTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentSettingsTest
 * @ingroup tests_functional_setup
 *
 * @brief Test the payment configuration setup.
 */

import('tests.data.ContentBaseTestCase');

class PaymentSettingsTest extends ContentBaseTestCase {
	
	/**
	 * @copydoc WebTestCase::getAffectedTables()
	 */
	protected function getAffectedTables() {
		return PKP_TEST_ENTIRE_DB;
	}

	/**
	 * Test payment configuration.
	 */
	function testPaymentSetup() {
		$this->open(self::$baseUrl);

		// The payment settings are not defined by default.
		// Check that NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD
		// is present.
		$submissionId = $this->findSubmissionAsEditor('dbarnes', null, $fullTitle = 'Wild Words: Essays on Alberta Literature');
		$this->addPublicationFormat($title = 'PDF production test');
		$this->openPublicationFormatTab($title);
		$this->waitForInPlaceNotification('publicationMetadataEntryForm-1-notification', null, 'No payment method configured.');

		// Configure the payment settings.
		$this->open(self::$baseUrl . '/index.php/publicknowledge/management/settings/distribution');
		$this->waitForElementPresent('css=#contextIndexingForm');
		$this->click('link=Payments');
		$this->waitForElementPresent('css=#paymentMethodSelector');
		$this->select('id=pluginSelect', 'label=Manual Fee Payment');
		$this->type('id=manualInstructions', 'Some text for the manual payment process.');
		$this->submitAjaxForm('paymentMethodForm');

		// Check again the publication format tab, expect no notification this time.
		$this->open(self::$baseUrl . '/index.php/publicknowledge/workflow/access/' . $submissionId);
		$this->openPublicationFormatTab($title);
		$this->assertTextNotPresent('No payment method configured.');
		$this->logOut();
	}
}
