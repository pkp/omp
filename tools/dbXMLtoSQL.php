<?php

/**
 * @file tools/dbXMLtoSQL.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class dbXMLtoSQL
 * @ingroup tools
 *
 * @brief CLI tool to output the SQL statements corresponding to an XML database schema.
 */

use PKP\cliTool\XmlToSqlTool;

require(dirname(__FILE__) . '/bootstrap.inc.php');

/** Default XML file to parse if none is specified */
define('DATABASE_XML_FILE', 'dbscripts/xml/omp_schema.xml');

class dbXMLtoSQL extends XmlToSqlTool
{
    /**
     * Constructor.
     *
     * @param $argv array command-line arguments
     * 	If specified, the first argument should be the file to parse
     */
    public function __construct($argv = [])
    {
        parent::__construct($argv);
    }
}

$tool = new dbXMLtoSQL($argv ?? []);
$tool->execute();
