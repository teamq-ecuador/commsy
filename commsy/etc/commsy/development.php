<?php
// $Id$
//
// Release $Name$
//
// Copyright (c)2008 Iver Jackewitz
//
//    This file is part of CommSy.
//
//    CommSy is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    CommSy is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You have received a copy of the GNU General Public License
//    along with CommSy.

// error reporting and debugging
// Log errors to this database.
// Only errors that are reported through the CommSy
// error handler go to this log database.
$c_error_log_db = true;

// Support for debugging message-tags
// if set, message-tags will not be resolved
// $dont_resolve_messagetags = true;
// if set, used messagetag without translation will be logged to the db
$c_messagetag_log_db = true;

// message tags in configuration (default == false) --- only for developers !!!!
$c_message_management = true;

// send emails (default == true) --- only for developers !!!!
// true = send emails
// false = don't send emails
// print = don't send emails but echo them in the htmlpage (usefull for debugging)
$c_send_email = 'print';

// support for debugging
$c_show_debug_infos = false;

// enable and configure logging to file
$c_enable_logging_to_file = false;
$c_logging_file = '';

// enable security for HTML textarea
// this feature is old and not stable
// please don't turn this on
// IJ, 16.08.2010
$c_enable_htmltextarea_security = false;

// use cron new
// save cron result in log file
// don't show it in HTML
// ATTENTION: maybe the newsletter will be send twice
// default = false -> use old cron
// $c_cron_use_new = true;
// if you use the new cron mechanism, you can force portals (use portal id) to the old one
// this will only work if you uses cron.php?cid=XYZ with get param cid
// $c_cron_use_old_array = array();
// $c_cron_use_old_array[] = 160;

// use project ids stored in community room items
// default = false -> use LEFT JOIN room -> link_items -> room
//$c_cache_cr_pr = true;

// count room redundancy
// turn this on, if you have performance problems with
// room list on portal
// $c_room_count_redundancy = true;

// use minimized javascript - CommSy 7
// $c_minimized_js = false;

// use new private room
//$c_use_new_private_room = true;

// media integration
// use true for all or an array of community room id's for restricted access
//$c_media_integration = true;

// use scorm in following room id's
//$c_scorm = array();

// use flash file upload with ssl
//$c_enable_flash_upload_with_ssl = true;

// enable upload via e-mail
//$c_email_upload = false;
//$c_email_upload_server = 'mail.example.com';
//$c_email_upload_server_port = '143';
//$c_email_upload_email_account = 'email@example.com';
//$c_email_upload_email_password = 'secret';

// enable new indexed search
//$c_indexed_search = true;

// enable workflow support
//$c_workflow = false;

// smarty
$c_smarty = true;
$c_theme = 'default';
$c_smarty_always = false;

// smarty caching
$c_smarty_caching = false;

// send xhr error reports to developers
//$c_xhr_error_reporting = array("dev1@commsy.dev", "dev2@commsy.dev");

// setup javascript mode
$c_js_mode = "source";

// debug mode
$c_debug = false;

// show rooms on portal for guests
// default: true
// at performance problems you should try false
// $c_portal_show_rooms_for_guests = false;
?>