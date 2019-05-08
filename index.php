<?php

/**
 * @mainpage OMP API Reference
 *
 * Welcome to the OMP API Reference. This resource contains documentation
 * generated automatically from the OMP source code.
 *
 * The design of Open %Monograph press is heavily structured for
 * maintainability, flexibility and robustness. Those familiar with Sun's
 * Enterprise Java Beans technology or the Model-View-Controller (MVC) pattern
 * will note many similarities.
 *
 * As in a MVC structure, data storage and representation, user interface
 * presentation, and control are separated into different layers. The major
 * categories, roughly ordered from "front-end" to "back-end," follow:
 * - Smarty templates, which are responsible for assembling HTML pages to
 *   display to users;
 * - Page classes, which receive requests from users' web browsers, delegate
 *   any required processing to various other classes, and call up the
 *   appropriate Smarty template to generate a response;
 * - Controllers, which implement reusable pieces of content e.g. for AJAX
 *   subrequests.
 * - Action classes, which are used by the Page classes to perform non-trivial
 *   processing of user requests;
 * - Model classes, which implement PHP objects representing the system's
 *   various entities, such as Users, Monographs, and Presses;
 * - Data Access Objects (DAOs), which generally provide (amongst others)
 *   update, create, and delete functions for their associated Model classes,
 *   are responsible for all database interaction;
 * - Support classes, which provide core functionalities, miscellaneous common
 *
 * As the system makes use of inheritance and has consistent class naming
 * conventions, it is generally easy to tell what category a particular class
 * falls into.
 * For example, a Data Access Object class always inherits from the DAO class,
 * has a Class name of the form [Something]%DAO, and has a filename of the form
 * [Something]%DAO.inc.php.
 *
 * To learn more about developing OMP, there are several additional resources
 * that may be useful:
 * - The docs/README document
 * - The PKP support forum at http://forum.pkp.sfu.ca
 * - Documentation available at http://pkp.sfu.ca/omp_documentation
 *
 * @file index.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup index
 *
 * Bootstrap code for OMP site. Loads required files and then calls the
 * dispatcher to delegate to the appropriate request handler.
 */


// Initialize global environment
define('INDEX_FILE_LOCATION', __FILE__);
$application = require('./lib/pkp/includes/bootstrap.inc.php');

// Serve the request
$application->execute();


