<?php
/**
 * Script for linking Alfred 3 ActiveCollab workflow
 *
 * @author Harmen Janssen <harmen@whatstyle.net>
 */
require './vendor/autoload.php';

use CFPropertyList\CFPropertyList;

define(
    'ALFRED_PREFS',
    getenv('HOME') . '/Library/Preferences/com.runningwithcrayons.Alfred-Preferences-3.plist'
);

$plist = new CFPropertyList(ALFRED_PREFS, CFPropertyList::FORMAT_BINARY);
$syncfolder = $plist->toArray()['syncfolder'] ?? '';

if (!$syncfolder) {
    echo "Alfred sync folder not found\n";
    exit(1);
}

$target = str_replace('~', getenv('HOME'), $syncfolder) . '/Alfred.alfredpreferences/workflows';
symlink(realpath('./'), $target . '/net.whatstyle.activecollab');
