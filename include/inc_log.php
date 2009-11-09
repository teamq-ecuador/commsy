<?php
// $Id$
//
// Release $Name$
//
// Copyright (c)2009 Iver Jackewitz
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

// Log information to database.
if ( !isset($environment)
     and !empty($this->_environment)
   ) {
   $environment = $this->_environment;
}
$l_current_user = $environment->getCurrentUserItem();
$array = array();
if ( isset($_GET['iid']) ) {
   $array['iid'] = $_GET['iid'];
} elseif ( isset($_POST['iid']) ) {
   $array['iid'] = $_POST['iid'];
}
if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
   $array['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
   $array['user_agent'] = 'No Info';
}

if ( isset($_POST) ) {
   $post_content = array2XML($_POST);
} else {
   $post_content = '';
}
$array['remote_addr']      = $_SERVER['REMOTE_ADDR'];
$array['script_name']      = $_SERVER['SCRIPT_NAME'];
$array['query_string']     = $_SERVER['QUERY_STRING'];
$array['request_method']   = $_SERVER['REQUEST_METHOD'];
$array['post_content']     = $post_content;
if ( !empty($l_current_user) ) {
   $array['user_item_id']     = $l_current_user->getItemID();
   $array['user_user_id']     = $l_current_user->getUserID();
}
$array['context_id']       = $environment->getCurrentContextID();
$array['module']           = $environment->getCurrentModule();
$array['function']         = $environment->getCurrentFunction();
$array['parameter_string'] = $environment->getCurrentParameterString();

$log_manager = $environment->getLogManager();
$log_manager->saveArray($array);
unset($log_manager);
unset($l_current_user);
?>