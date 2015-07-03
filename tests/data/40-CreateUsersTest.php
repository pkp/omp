<?php

/**
 * @file tests/data/40-CreateUsersTest.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreateUsersTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create test users
 */

import('lib.pkp.tests.data.PKPCreateUsersTest');

class CreateUsersTest extends PKPCreateUsersTest {
	/**
	 * Create a user
	 */
	function testCreateRvaca() {
		$this->createUser(array(
			'username' => 'rvaca',
			'firstName' => 'Ramiro',
			'lastName' => 'Vaca',
			'country' => 'Mexico',
			'affiliation' => 'Universidad Nacional Autónoma de México',
			'roles' => array('Press manager'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateDbarnes() {
		$this->createUser(array(
			'username' => 'dbarnes',
			'firstName' => 'Daniel',
			'lastName' => 'Barnes',
			'country' => 'Australia',
			'affiliation' => 'University of Melbourne',
			'roles' => array('Press editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateDbuskins() {
		$this->createUser(array(
			'username' => 'dbuskins',
			'firstName' => 'David',
			'lastName' => 'Buskins',
			'country' => 'United States',
			'affiliation' => 'University of Chicago',
			'roles' => array('Series editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateSberardo() {
		$this->createUser(array(
			'username' => 'sberardo',
			'firstName' => 'Stephanie',
			'lastName' => 'Berardo',
			'country' => 'Canada',
			'affiliation' => 'University of Toronto',
			'roles' => array('Series editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateMinoue() {
		$this->createUser(array(
			'username' => 'minoue',
			'firstName' => 'Minoti',
			'lastName' => 'Inoue',
			'country' => 'Japan',
			'affiliation' => 'Kyoto University',
			'roles' => array('Series editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateJjanssen() {
		$this->createUser(array(
			'username' => 'jjanssen',
			'firstName' => 'Julie',
			'lastName' => 'Janssen',
			'country' => 'Netherlands',
			'affiliation' => 'Utrecht University',
			'roles' => array('Internal Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreatePhudson() {
		$this->createUser(array(
			'username' => 'phudson',
			'firstName' => 'Paul',
			'lastName' => 'Hudson',
			'country' => 'Canada',
			'affiliation' => 'McGill University',
			'roles' => array('Internal Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateAmccrae() {
		$this->createUser(array(
			'username' => 'amccrae',
			'firstName' => 'Aisla',
			'lastName' => 'McCrae',
			'country' => 'Canada',
			'affiliation' => 'University of Manitoba',
			'roles' => array('Internal Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateAgallego() {
		$this->createUser(array(
			'username' => 'agallego',
			'firstName' => 'Adela',
			'lastName' => 'Gallego',
			'country' => 'United States',
			'affiliation' => 'State University of New York',
			'roles' => array('External Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateAlzacharia() {
		$this->createUser(array(
			'username' => 'alzacharia',
			'firstName' => 'Al',
			'lastName' => 'Zacharia',
			'country' => 'Ghana',
			'affiliation' => 'KNUST',
			'roles' => array('External Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateGfavio() {
		$this->createUser(array(
			'username' => 'gfavio',
			'firstName' => 'Gonzalo',
			'lastName' => 'Favio',
			'country' => 'Spain',
			'affiliation' => 'Madrid',
			'roles' => array('External Reviewer'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateMfritz() {
		$this->createUser(array(
			'username' => 'mfritz',
			'firstName' => 'Maria',
			'lastName' => 'Fritz',
			'country' => 'Belgium',
			'affiliation' => 'Ghent University',
			'roles' => array('Copyeditor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateSvogt() {
		$this->createUser(array(
			'username' => 'svogt',
			'firstName' => 'Sarah',
			'lastName' => 'Vogt',
			'country' => 'Chile',
			'affiliation' => 'Universidad de Chile',
			'roles' => array('Copyeditor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateGcox() {
		$this->createUser(array(
			'username' => 'gcox',
			'firstName' => 'Graham',
			'lastName' => 'Cox',
			'country' => 'United States',
			'affiliation' => 'Duke University',
			'roles' => array('Layout Editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateShellier() {
		$this->createUser(array(
			'username' => 'shellier',
			'firstName' => 'Stephen',
			'lastName' => 'Hellier',
			'country' => 'South Africa',
			'affiliation' => 'University of Cape Town',
			'roles' => array('Layout Editor'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateCturner() {
		$this->createUser(array(
			'username' => 'cturner',
			'firstName' => 'Catherine',
			'lastName' => 'Turner',
			'country' => 'United Kingdom',
			'affiliation' => 'Imperial College London',
			'roles' => array('Proofreader'),
		));
	}

	/**
	 * Create a user
	 */
	function testCreateSkumar() {
		$this->createUser(array(
			'username' => 'skumar',
			'firstName' => 'Sabine',
			'lastName' => 'Kumar',
			'country' => 'Singapore',
			'affiliation' => 'National University of Singapore',
			'roles' => array('Proofreader'),
		));
	}
}
