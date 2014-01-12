#!/usr/bin/php
<?php
//
//    InstallApp, PHP Applications for Kloxo
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2011     LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// This script can read and write php application versions for InstallApp
//
// Author: Danny Terweij <d.terweij@lxcenter.org>
// Script: To manage "version.list"
// Last Modified: 03-jan-2010 
// 
// Changelog
// 1.1 * Add --list option
// 1.1 * Add massive apps version changer (adding lx.[n] behind version)
// ^^^^^^^^^^^^^^^^^^ NOT FINISHED HAS SOME WEIRD ERROR 
// 1.0 * Initial Start
//
// ###########################################################################

$myver="1.1";

class Remote {}

if (empty($argc) && !strstr($argv[0], basename(__FILE__))) {
echo "Not ran from CLI!\n";
echo "Usage:\n";
echo "php make-version.list.php <switches>\n";
exit; 
}

function List_Help() {
global $myver;
echo "\n(c) LxCenter 2011 version " . $myver . "\n";
echo "Written by Danny Terweij <d.terweij@lxcenter.org> July 16 2010\n";
echo "License terms: AGPL-V3\n\n";
echo "Usage:\n";
echo "--listall                 List all apps and versions\n";
echo "--add appname version             Add a new application\n";
echo "--delete appname          Delete a application\n";
echo "--change appname version  Change version for appname\n";
echo "--list appname		Lists single app version\n";
//echo "--bump version		Bump all apps version number (lx.\[n\])\n";
echo "\n\n";
Footer();
}

function Footer() {
echo "\n--- Finished your actions ---\n";
echo "Thank you for using this script :)\n\n";
}

if (empty($argv[1]) || $argv[1] == "--help" || $argv[1] == "-h" ) {
List_Help();
exit;
}

function read_versionlist($file) {
$handle = fopen($file, "r");
 if ($handle) {
        $buffer = fgets($handle, 4096);
    fclose($handle);
 } else {
	echo "Error: Could not open file ($file)";
	exit;
 }
return $buffer;
}

function List_App($appname) {
 $iaver = "";
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 $iaver = $applist['installapp'];
 $appsn = count($applist) - 1; // minus one, installapp is not an app
  foreach($applist as $app => $ver) {
    if ($app == "$appname") {
     echo "AppName: " . $app . " -- version: ". $ver ."\n";
     break;
    }
  }
  echo "--------\n";
  echo "InstallApp version: " . $iaver . "\n";
  echo $appsn . " active PHP Applications found.\n\n";
return true;
}

function List_All() {
 $iaver = "";
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 $iaver = $applist['installapp'];
 $appsn = count($applist) - 1; // minus one, installapp is not an app
  foreach($applist as $app => $ver) {
   if ($app != "installapp") {
    echo "AppName: ". $app." -- version: ". $ver ."\n";
   }
  }
  echo "--------\n";
  echo "InstallApp version: " . $iaver . "\n";
  echo $appsn . " active PHP Applications found.\n\n";
return true;
}

function Check_Bump($app,$ver) {
 $line = read_versionlist("version.list.new");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist;
 $version = $applist["$app"];
 if ($ver != $version) { return true; }
 return false;
}

function Bump_Lx($lx) {

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Should be changed to check for lx.[n], then change [n]
// This is just for a general onetime bump, it adds -lx.[n]
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 
 if ($lx == "") {$lx = "0"; }
 $iaver = "";
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 $iaver = $applist['installapp'];
 $appsn = count($applist) - 1; // minus one, installapp is not an app
 $newfile = "version.list.new";
 $oldfile = "version.list.org";
 if (file_exists($newfile)) {
    @system("rm -f " . $newfile);
 }
 if (file_exists($oldfile)) {
    @system("rm -f " . $oldfile);
 }

// Copy version.list for backup
@system ("cp version.list ".$oldfile);

  foreach($applist as $app => $ver) {
   if ($app != "installapp") {


// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $checkbump = strpos($ver, "-lx");
    if ($checkbump !== false) {
      echo "Looks like Bumnping already ran.";
      exit;
    }
    $newversion = $ver . "-lx." . $lx;
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $applist[$app] = $newversion;
    echo "AppName: ". $app." -- From: ". $ver ." to ". $newversion ."\n";

    $rmt = new Remote();
    $rmt->applist = $applist;
    $newlist = serialize($rmt);
    $error = Bump_Save_File($newfile, $newlist);

    if ($error) {
     echo "There was an error, could not write to " . $newfile . ". Aborted!\n";
     exit;
    }
    
    if (Check_Bump($app, $newversion)) {
    echo "### Error with " . $app . " -  " . $ver . " -> " . $newversion . "\n";

// Try to re-add the app
//
//    Delete_App($app);
//    @system("cp version.list.new version.list");
//    Add_App($app, $ver);
//    @system("cp version.list.new version.list");
//    echo "Done. Re-run this again\n";
//
// Hmm it keeps looping.. it never ends up in total fix.
//

    exit;
    }

   }
  }
  
  echo "--------\n";
  echo "InstallApp version: " . $iaver . "\n";
  echo $appsn . " active PHP Applications found.\n\n";
  echo "I wrote a new file named " . $newfile . " with the new information\n";
  echo "I copied version.list to " . $oldfile . "\n";
  echo "To release do yourself:\n\n";
  echo "cp " .$newfile . " version.list\n\n";
  echo "and commit it to the svn server.\n";
return true;
}


function Add_App($appname, $version) {
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 $applist[$appname] = $version ;
 $rmt = new Remote();
 $rmt->applist = $applist;
 $newlist = serialize($rmt);
 $newfile = "version.list.new";
 $oldfile = "version.list.org";
 $error = Save_File($newfile, $newlist, $oldfile);
 if ($error) {
 echo "There was an error, could not write to " . $newfile . ". 
Aborted!\n";
 exit;
 }

 echo "Added " . $appname . " with version ". $version . "\n";
 echo "I wrote a new file named " . $newfile . " with the new information\n";
 echo "I copied version.list to " . $oldfile . "\n";
 echo "To release do yourself:\n\n";
 echo "cp " .$newfile . " version.list\n\n";
 echo "and commit it to the svn server.\n";

return true;
}

function Delete_App($appname) {
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 unset($applist[$appname]);
 $rmt = new Remote();
 $rmt->applist = $applist;
 $newlist = serialize($rmt);
 $newfile = "version.list.new";
 $oldfile = "version.list.org";
 $error = Save_File($newfile, $newlist, $oldfile);
 if ($error) {
 echo "There was an error, could not write to " . $newfile . ". Aborted!\n";
 exit;
 }

 echo "Deleted " . $appname . "\n";
 echo "I wrote a new file named " . $newfile . " with the new information\n";
 echo "I copied version.list to " . $oldfile . "\n";
 echo "To release do yourself:\n\n";
 echo "cp " .$newfile . " version.list\n\n";
 echo "and commit it to the svn server.\n";
return true;
}

function Change_App($appname, $version) {
 $line = read_versionlist("version.list");
 $fapplist = unserialize($line);
 $applist = $fapplist->applist ;
 $applist["$appname"] = $version ;
 $rmt = new Remote();
 $rmt->applist = $applist;
 $newlist = serialize($rmt);
 $newfile = "version.list.new";
 $oldfile = "version.list.org";
 $error = Save_File($newfile, $newlist, $oldfile);
 if ($error) {
 echo "There was an error, could not write to " . $newfile . ". Aborted!\n";
 exit;
 }
 
 echo "Changed " . $appname . " to have a new version ". $version . "\n"; 
 echo "I wrote a new file named " . $newfile . " with the new information\n"; 
 echo "I copied version.list to " . $oldfile . "\n";
 echo "To release do yourself:\n\n";
 echo "cp " .$newfile . " version.list\n\n";
 echo "and commit it to the svn server.\n";
return true;
}

function Save_File($file, $alist, $ofile) {
$error = false;

if (file_exists($file)) {
    @system("rm -f " . $file);
}
if (file_exists($ofile)) {
    @system("rm -f " . $ofile);
}

// Copy version.list for backup
@system ("cp version.list ".$ofile);

$handle = fopen($file, 'w');
 if ($handle) {
  fwrite($handle, $alist."\n");
  fclose($handle);
 } else {
  $error = true;
 }
return $error;
}

function Bump_Save_File($file, $alist) {
$error = false;
$handle = fopen($file, 'w');
 if ($handle) {
  fwrite($handle, $alist."\n");
  fclose($handle);
 } else {
  $error = true;
 }
return $error;
}


switch ($argv[1]) {
    case "--listall":
        List_All();
	Footer();
        break;
    case "--add":
	Add_App($argv[2], $argv[3]);
	Footer();
        break;
    case "--delete":
	Delete_App($argv[2]);
	Footer();
        break;
    case "--change":
	Change_App($argv[2], $argv[3]);
	Footer();
	break;
    case "--list":
	List_App($argv[2]);
	Footer();
	break;
    case "--bump":
	Bump_Lx($argv[2]);
	Footer();
	break;
    default:
	List_Help();
}
exit;

