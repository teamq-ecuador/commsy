<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, José Manuel González Vázquez
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

if (!empty($_POST['option'])) {
   $command = $_POST['option'];
} else {
   $command = '';
}

if (!empty($_GET['back_tool'])) {
   $back_file = $_GET['back_tool'].'.php';
} else {
   $back_file = '';
}

// case: login with CommSy
if ( isset($session) ) {
   $history = $session->getValue('history');
   $cookie = $session->getValue('cookie');
   $javascript = $session->getValue('javascript');
   $https = $session->getValue('https');
   $flash = $session->getValue('flash');
}
// case: login with external login box
else {
   $history = array();
   $cookie = '';
   $javascript = '';
   $https = '';
   $flash = '';
}

// user_id and password
$user_id = '';
if ( !empty($_POST['user_id']) ) {
   $user_id = $_POST['user_id'];
} elseif ( !empty($_GET['user_id']) ) {
   $user_id = $_GET['user_id'];
}

$password = '';
if ( !empty($_POST['password']) ) {
   $password = $_POST['password'];
} elseif ( !empty($_GET['password']) ) {
   $password = $_GET['password'];
}

if (!empty($user_id) and !empty($password) ) {
   $authentication = $environment->getAuthenticationObject();
   if ( isset($_POST['auth_source']) and !empty($_POST['auth_source']) ) {
      $auth_source = $_POST['auth_source'];
   } else {
      $auth_source = '';
   }
   if(!empty($auth_source)){
   	$auth_manager = $environment->getAuthSourceManager();
   	$auth_item = $auth_manager->getItem($auth_source);
   	unset($auth_manager);
   }
   $portal_item = $environment->getCurrentContextItem();
   // get user item if temporary lock is enabled
   $userExists = false;
   $locked_temp = false;
   $locked = false;
   $login_status = $authentication->isAccountGranted($user_id,$password,$auth_source);
   if(isset($auth_item) AND !empty($auth_item)){
   	if($portal_item->isTemporaryLockActivated() or $portal_item->getInactivityLockDays() > 0){
   		$user_manager = $environment->getUserManager();
   		$userExists = $user_manager->exists($user_id);
   		unset($user_manager);
   		if($userExists){
   			$user_locked = $authentication->_getPortalUserItem($user_id,$authentication->_auth_source_granted);
	   		if(isset($user_locked)){
	   			$locked = $user_locked->isLocked();
		   		$locked_temp = $user_locked->isTemporaryLocked();
	   		}

   		}
   	}
   }
   // user access granted
   if ($login_status AND !$locked_temp AND !$locked) {
      $session = new cs_session_item();
      $session->createSessionID($user_id);
      if ( $cookie == '1' ) {
         $session->setValue('cookie',2);
      } elseif ( empty($cookie) ) {
         // do nothing, so CommSy will try to save cookie
      } else {
         $session->setValue('cookie',0);
      }
      if ($javascript == '1') {
         $session->setValue('javascript',1);
      } elseif ($javascript == '-1') {
         $session->setValue('javascript',-1);
      }
      if ($https == '1') {
         $session->setValue('https',1);
      } elseif ($https == '-1') {
         $session->setValue('https',-1);
      }
      if ($flash == '1') {
         $session->setValue('flash',1);
      } elseif ($flash == '-1') {
         $session->setValue('flash',-1);
      }

      // save portal id in session to be sure, that user didn't
      // switch between portals
      if ( $environment->inServer() ) {
         $session->setValue('commsy_id',$environment->getServerID());
      } else {
         $session->setValue('commsy_id',$environment->getCurrentPortalID());
      }

      // external tool
      if ( mb_stristr($_SERVER['PHP_SELF'],'homepage.php') ) {
         $session->setToolName('homepage');
      }

      // auth_source
      if ( empty($auth_source) ) {
         $auth_source = $authentication->getAuthSourceItemID();
      }
      $session->setValue('auth_source',$auth_source);

   } else {
   	  // user access is not granted 
   	  // Datenschutz
   	  $current_context = $environment->getCurrentContextItem();
      $error_array = $authentication->getErrorArray();
      
      if ( isset($_POST['auth_source']) and !empty($_POST['auth_source']) ) {
      	$auth_source = $_POST['auth_source'];
      } else {
      	$auth_source = '';
      }
      // auth_source
      if ( empty($auth_source) ) {
      	$auth_source = $authentication->getAuthSourceItemID();
      }
      if(!empty($auth_source)){
      	$auth_manager = $environment->getAuthSourceManager();
      	$auth_item = $auth_manager->getItem($auth_source);
      	unset($auth_manager);
      }
            
      if($portal_item->isTemporaryLockActivated()){
      	// Erster Fehlversuch // Timestamp in session speichern und
	      // Password tempLock
	      $userExists = false;
	      $user_manager = $environment->getUserManager();
	      $userExists = $user_manager->exists($user_id);
	      $tempUser = $session->getValue('userid');
	      if(!isset($tempUser)){
	      	$session->setValue('userid', $user_id);
	      	$tempUser = $user_id;
	      }
	      if(!$session->issetValue('TMSP_'.$user_id) or $session->getValue('TMSP_'.$user_id) < getCurrentDateTimeMinusSecondsInMySQL($current_context->getLockTimeInterval())){
	      	$session->setValue('TMSP_'.$user_id, getCurrentDateTimeInMySQL());
	      }
	      $count = $session->getValue('countWrongPassword');
	      // Password tempLock ende
      }
      if ( !isset($session) ) {
         $session = new cs_session_item();
         $session->createSessionID('guest');
         //Password tempLock
         $session->setValue('countWrongPassword', 1);
      } else {
      	if($portal_item->isTemporaryLockActivated()){
	       	$count = $session->getValue('countWrongPassword');
	       	if(!isset($count) AND empty($count)){
	       		$session->setValue('countWrongPassword', 1);
	       	}
	       	if(!isset($count)){
	       		$count = 0;
	       	}
	       	if($user_id == $tempUser){
	       		$count++;
	       	} else {
	       		$count = 0;
	       		$session->setValue('countWrongPassword', 0);
	       		$session->setValue('userid', $user_id);
	       	}
	       	$trys_login = $current_context->getTryUntilLock();
	       	if(empty($trys_login)){
	       		$trys_login = 3;
	       	}
// 	       	pr($count >= $trys_login);
// 	       	pr($userExists);
// 	       	pr(!$locked);
// 	       	pr(!$locked_temp);
// 	       	pr($session->getValue('TMSP_'.$session->getValue('userid')) >= getCurrentDateTimeMinusSecondsInMySQL($current_context->getLockTimeInterval()));
// 	       	pr($session);
	       	#break;
	       	if($count >= $trys_login AND $userExists AND !$locked AND !$locked_temp AND $session->getValue('TMSP_'.$session->getValue('userid')) >= getCurrentDateTimeMinusSecondsInMySQL($current_context->getLockTimeInterval())){
       			$user = $authentication->_getPortalUserItem($tempUser,$authentication->_auth_source_granted);
       			$user->setTemporaryLock();
       			$user->save();
       			$count = 0;
       			$session->setValue('countWrongPassword', 0);
	       	}
      	}
       	#$count++;
       	$session->setValue('countWrongPassword', $count);
      }
      // Password tempLock ende 
      $session->setValue('error_array',$error_array);
      unset($user_manager);
   } 
   if($locked){
   	$translator = $environment->getTranslationObject();
   	$error_array = array();
   	$error_array[] = $translator->getMessage('COMMON_TEMPORARY_LOCKED_DAYS');#'Kennung ist vorübergehend gesperrt';
   	$session->setValue('error_array',$error_array);
   }
   if($locked_temp){
   	$translator = $environment->getTranslationObject();
   	$error_array = array();
   	$error_array[] = $translator->getMessage('COMMON_TEMPORARY_LOCKED', $current_context->getLockTime());#'Kennung ist vorübergehend gesperrt';
   	$session->setValue('error_array',$error_array);
   }
} elseif ( empty($user_id) or empty($password) ) {
   $translator = $environment->getTranslationObject();
   $error_array = array();
   if ( empty($user_id) ) {
      $error_array[] = $translator->getMessage('COMMON_ERROR_FIELD',$translator->getMessage('COMMON_ACCOUNT'));
   }
   if ( empty($password) ) {
      $error_array[] = $translator->getMessage('COMMON_ERROR_FIELD',$translator->getMessage('COMMON_PASSWORD'));
   }
   if ( !isset($session) ) {
      $session = new cs_session_item();
      $session->createSessionID('guest');
   }
   $session->setValue('error_array',$error_array);
}

if ( isset($session) ) {
   $environment->setSessionItem($session);
}

// redirect
if ( !empty($_POST['login_redirect']) ) {
   $cid = $environment->getCurrentContextID();
   if ( !empty($_POST['login_redirect']['cid']) ) {
      $cid = $_POST['login_redirect']['cid'];
   }
   $mod = 'home';
   if ( !empty($_POST['login_redirect']['mod']) ) {
      $mod = $_POST['login_redirect']['mod'];
   }
   $fct = 'index';
   if ( !empty($_POST['login_redirect']['fct']) ) {
      $fct = $_POST['login_redirect']['fct'];
   }
   $params = $_POST['login_redirect'];
   unset($params['cid']);
   unset($params['mod']);
   unset($params['fct']);
   redirect($cid,$mod,$fct,$params);
} elseif ( !empty($_GET['target_cid']) ) {
   $mod = 'home';
   $fct = 'index';
   $params = array();
   redirect($_GET['target_cid'],$mod,$fct,$params);
} else {
   if ( !empty($history[0]['context']) ) {
      $cid = $history[0]['context'];
   } else {
      $cid = $environment->getCurrentContextID();
   }

   if ( !empty($history[0]['module']) ) {
      $mod = $history[0]['module'];
   } else {
      $mod = $environment->getCurrentModule();
   }

   if ( !empty($history[0]['function']) ) {
      $fct = $history[0]['function'];
   } else {
      $fct = $environment->getCurrentFunction();
   }

   if ( !isset($history[0]['parameter']) ) {
      $params = $environment->getCurrentParameterArray();
   } else {
      $params = $history[0]['parameter'];
   }

   if ( isset($error_array) and !empty($error_array) ) {
      if ( isset($auth_source) and !empty($auth_source) ) {
         $params['auth_source'] = $auth_source;
      }
   }
   if ( $mod == 'context'
        and $fct == 'login'
      ) {
      $mod = 'home';
      $fct = 'index';
   }
   redirect($cid,$mod,$fct,$params,'','',$back_file);
}
?>