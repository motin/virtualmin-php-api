<?php

/* This is a simple demonstration script for use with the
 * Virtualmin API Classes. It will show basic usage of
 * some commands and also how to set it up.
 */

// Passing domain name via get to enable easier domain name changes
if (!isset($_GET['domain'])) {
    die('Please set the GET parameter \'domain\'');
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library')
)));

require_once 'Virtualmin.php';

if (!file_exists('config.php')) {
    die('Please create a config.php file that contains your project defaults');
}
require_once 'config.php';

$vm = new Virtualmin($config);

echo "<pre>";

// List Domains
echo "<h1>List Domains</h1>";
echo print_r($vm->listDomains());

// Create new Domain
echo "<h1>Create Domain</h1>";
echo $vm->createDomain($_GET['domain'], 'passwd', array('default-features' => ''));

// List Domains - Showing new Domain
echo "<h1>List Domains</h1>";
echo $vm->listDomains();

// Disable the Domains
echo "<h1>Disable Domain</h1>";
echo $vm->disableDomain($_GET['domain']);

// Enable the Domain
echo "<h1>Enable Domain</h1>";
echo $vm->enableDomain($_GET['domain']);

// Delete the Domain
echo "<h1>Delete Domain</h1>";
echo $vm->deleteDomain($_GET['domain']);

// List Domains again
echo "<h1>List Domains</h1>";
echo print_r($vm->listDomains());