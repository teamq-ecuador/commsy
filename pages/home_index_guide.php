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


// Verify parameters for this page
if (!empty($_GET['account'])) {
   $account_mode = $_GET['account'];
} else {
   $account_mode = 'none';
}

// Get the translator object
$translator = $environment->getTranslationObject();


$room_id_env = $environment->getValueOfParameter('room_id');
if ( !empty($room_id_env) ) {
   $_GET['room_id'] = $room_id_env;
}

if (!empty($_GET['room_id'])) {
   $current_item_id = $_GET['room_id'];

   // redirect user into room, if s/he is member allready
   if ($account_mode == 'member') {
      $current_user = $environment->getCurrentUserItem();
      if (isset($current_user) and $current_user->getUserID() != 'guest') {
         $room_manager = $environment->getRoomManager();
         $room_item = $room_manager->getItem($current_item_id);
         if (isset($room_item) and $room_item->isUser($current_user)) {
            redirect($current_item_id,'home','index','');
         }
      }
   }
} else {
   $current_item_id ='';
}

if (isset($_POST['option'])){
   $option = $_POST['option'];
} elseif (isset($_GET['option'])){
   $option = $_GET['option'];
} else {
   $option= 'none';
}

// Find out what to do
if ( isset($_POST['delete_option']) ) {
   $delete_command = $_POST['delete_option'];
}elseif ( isset($_GET['delete_option']) ) {
   $delete_command = $_GET['delete_option'];
} else {
   $delete_command = '';
}
if ( isset($_GET['action']) and $_GET['action'] == 'delete' ) {
   $current_user_item = $environment->getCurrentUserItem();
   $room_manager = $environment->getRoomManager();
   if ( !empty($current_item_id) ) {
      $room_item = $room_manager->getItem($current_item_id);
      if ( $current_user_item->isModerator()
           or ( isset($room_item)
                and $room_item->isModeratorByUserID($current_user_item->getUserID(),$current_user_item->getAuthSource())
              )
         ) {
         $params = $environment->getCurrentParameterArray();
         $page->addDeleteBox(curl($environment->getCurrentContextID(),'home','index',$params));
      }
      unset($room_item);
   }
   unset($room_manager);
   unset($current_user_item);
}

// Cancel editing
if ( isOption($delete_command, $translator->getMessage('COMMON_CANCEL_BUTTON')) ) {
   $params = $environment->getCurrentParameterArray();
   $anchor = '';
   $params['room_id'] = $current_item_id;
   unset($params['action']);
   unset($params['iid']);
   redirect($environment->getCurrentContextID(), 'home', 'index', $params);
}

// Delete item
elseif ( isOption($delete_command, $translator->getMessage('COMMON_DELETE_BUTTON')) ) {
   $manager = $environment->getRoomManager();
   $item = $manager->getItem($current_item_id);
   $current_user_item = $environment->getCurrentUserItem();
   if ( $current_user_item->isModerator()
        or ( isset($item)
             and $item->isModeratorByUserID($current_user_item->getUserID(),$current_user_item->getAuthSource())
           )
      ) {
      $item->delete();
   }
   unset($item);
   unset($manager);
   unset($current_user_item);
   redirect($environment->getCurrentContextID(), 'home', 'index', '');
}

// Archiv item
elseif ( isOption($delete_command, $translator->getMessage('ROOM_ARCHIV_BUTTON')) ) {
   $manager = $environment->getRoomManager();
   $item = $manager->getItem($current_item_id);
   $current_user_item = $environment->getCurrentUserItem();
   if ( $current_user_item->isModerator()
        or ( isset($item)
             and $item->isModeratorByUserID($current_user_item->getUserID(),$current_user_item->getAuthSource())
           )
      ) {
      $item->close();
      $item->save();
   }
   unset($item);
   unset($manager);
   unset($current_user_item);

   $params = $environment->getCurrentParameterArray();
   $anchor = '';
   $params['room_id'] = $current_item_id;
   unset($params['action']);
   unset($params['iid']);
   redirect($environment->getCurrentContextID(), 'home', 'index', $params);
}

// get translation object
$translator = $environment->getTranslationObject();

if (isOption($option, $translator->getMessage('CONTACT_MAIL_SEND_BUTTON'))){
   include_once('classes/cs_mail.php');
   $params['room_id']= $current_item_id;
   $user_manager = $environment->getUserManager();
   $user_item = $environment->getCurrentUserItem();
   $room_manager = $environment->getRoomManager();
   $room_item = $room_manager->getItem($current_item_id);
   $user_list = $room_item->getContactModeratorList();
   $email_addresses = array();
   $moderator_item = $user_list->getFirst();
   $recipients = '';
   while ($moderator_item) {
      $email_addresses[] = $moderator_item->getEmail();
      $recipients .= $moderator_item->getFullname().LF;
      $moderator_item = $user_list->getNext();
   }

   // language
   $language = $room_item->getLanguage();
   if ($language == 'user') {
      $language = $user_item->getLanguage();
      if ($language == 'browser') {
         $language = $environment->getSelectedLanguage();
      }
   }


   if (count($email_addresses) > 0) {
      $old_lang = $translator->getSelectedLanguage();
      $translator->getSelectedLanguage($language);
      $subject = $translator->getMessage('USER_ASK_MAIL_SUBJECT',$user_item->getFullname(),$room_item->getTitle());
      $body  = '';
      if (!empty($_POST['description_user'])) {
          $body .= $_POST['description_user'];
          $body .= LF.LF;
          $body .= '---'.LF;
      }
      $body .= $translator->getMessage('MAIL_SEND_TO',$recipients);
      $body .= LF;
      $mail = new cs_mail();
      $mail->set_to(implode(',',$email_addresses));
      $mail->set_from_email($user_item->getEmail());
      $mail->set_from_name($user_item->getFullname());
      $mail->set_reply_to_name($user_item->getFullname());
      $mail->set_reply_to_email($user_item->getEmail());
      $mail->set_subject($subject);
      $mail->set_message($body);
      $mail->send();
      $translator->getSelectedLanguage($old_lang);
   }
   $get_params = $environment->getCurrentParameterArray();
   if (isset($get_params['sort'])){
      $params['sort'] = $get_params['sort'];
   }
   if (isset($get_params['search'])){
      $params['search'] = $get_params['search'];
   }
   if (isset($get_params['seltime'])){
      $params['seltime'] = $get_params['seltime'];
   }
   if (isset($get_params['selroom'])){
      $params['selroom'] = $get_params['selroom'];
   }
   if (isset($get_params['sel_archive_room'])){
      $params['sel_archive_room'] = $get_params['sel_archive_room'];
   }
   redirect($environment->getCurrentContextID(), 'home', 'index', $params);
}

if (isOption($option, $translator->getMessage('ACCOUNT_GET_MEMBERSHIP_BUTTON'))) {
   include_once('classes/cs_mail.php');
   $room_manager = $environment->getRoomManager();
   $room_item = $room_manager->getItem($current_item_id);
   $session = $environment->getSessionItem();
   $get_params = $environment->getCurrentParameterArray();
   if (isset($get_params['sort'])){
      $params['sort'] = $get_params['sort'];
   }
   if (isset($get_params['search'])){
      $params['search'] = $get_params['search'];
   }
   if (isset($get_params['seltime'])){
      $params['seltime'] = $get_params['seltime'];
   }
   if (isset($get_params['selroom'])){
      $params['selroom'] = $get_params['selroom'];
   }
   if (isset($get_params['sel_archive_room'])){
      $params['sel_archive_room'] = $get_params['sel_archive_room'];
   }
   $params['room_id'] = $current_item_id;

   // build new user_item
   if ( !$room_item->checkNewMembersWithCode()
        or $room_item->checkNewMembersWithCode()
            and ( $room_item->getCheckNewMemberCode() == $_POST['code'])
        or $room_item->checkNewMembersWithCode()
            and (empty($_POST['code'])
        	and isset($_POST['description_user']))
      ) {

      if ($room_item->getCheckNewMemberCode() == $_POST['code']) {
         unset($_POST['description_user']);
      }
       $user_manager = $environment->getUserManager();
       $current_user = $environment->getCurrentUserItem();
       $private_room_user_item = $current_user->getRelatedPrivateRoomUserItem();
       if ( isset($private_room_user_item) ) {
          $user_item = $private_room_user_item->cloneData();
          $picture = $private_room_user_item->getPicture();
       } else {
          $user_item = $current_user->cloneData();
          $picture = $current_user->getPicture();
       }
       $user_item->setVisibleToLoggedIn();
       $user_item->setContextID($current_item_id);
       if (!empty($picture)) {
          $value_array = explode('_',$picture);
          $value_array[0] = 'cid'.$user_item->getContextID();
          $new_picture_name = implode('_',$value_array);

          $disc_manager = $environment->getDiscManager();
          $disc_manager->copyImageFromRoomToRoom($picture,$user_item->getContextID());
          $user_item->setPicture($new_picture_name);
       }
       if (isset($_POST['description_user'])) {
          $user_item->setUserComment($_POST['description_user']);
       }

       //check room_settings
       if ( (!$room_item->checkNewMembersNever()
            and !$room_item->checkNewMembersWithCode())
           or $room_item->checkNewMembersWithCode()
            and (empty($_POST['code'])
        	and isset($_POST['description_user']))
          ) {
          $user_item->request();
          $check_message = 'YES'; // for mail body
          $account_mode = 'info';
       } else {
          $user_item->makeUser(); // for mail body
          $check_message = 'NO';
          $account_mode = 'to_room';
          // save link to the group ALL
          $group_manager = $environment->getLabelManager();
          $group_manager->setExactNameLimit('ALL');
          $group_manager->setContextLimit($current_item_id);
          $group_manager->select();
          $group_list = $group_manager->get();
          if ($group_list->getCount() == 1) {
             $group = $group_list->getFirst();
             $group->setTitle('ALL');
             $user_item->setGroupByID($group->getItemID());
          }
       }

       // test if user id allready exist (reload page)
       $user_id = $user_item->getUserID();
       $user_test_item = $room_item->getUserByUserID($user_id,$user_item->getAuthSource());
       if ( !isset($user_test_item)
            and mb_strtoupper($user_id, 'UTF-8') != 'GUEST'
            and mb_strtoupper($user_id, 'UTF-8') != 'ROOT'
          ) {
          $user_item->save();

          if ( !$room_item->checkNewMembersNever()
               and !$room_item->checkNewMembersWithCode()
             or $room_item->checkNewMembersWithCode()
               and (empty($_POST['code'])
        	   and isset($_POST['description_user']))
             ) {
             // save task
             $task_manager = $environment->getTaskManager();
             $task_item = $task_manager->getNewItem();
             $current_user = $environment->getCurrentUserItem();
             $task_item->setCreatorItem($current_user);
             $task_item->setContextID($room_item->getItemID());
             $task_item->setTitle('TASK_USER_REQUEST');
             $task_item->setStatus('REQUEST');
             $task_item->setItem($user_item);
             $task_item->save();
          }

          // send email to moderators if necessary
          $user_manager = $environment->getUserManager();
          $user_manager->resetLimits();
          $user_manager->setModeratorLimit();
          $user_manager->setContextLimit($current_item_id);
          $user_manager->select();
          $user_list = $user_manager->get();
          $email_addresses = array();
          $moderator_item = $user_list->getFirst();
          $recipients = '';
          $language = $room_item->getLanguage();
          while ($moderator_item) {
             $want_mail = $moderator_item->getAccountWantMail();
             if (!empty($want_mail) and $want_mail == 'yes') {
                if ($language == 'user' and $moderator_item->getLanguage() == 'browser') {
                   $email_addresses[$environment->getSelectedLanguage()][] = $moderator_item->getEmail();
                } elseif ($language == 'user' and $moderator_item->getLanguage() != 'browser') {
                   $email_addresses[$moderator_item->getLanguage()][] = $moderator_item->getEmail();
                } else {
                   $email_addresses[$room_item->getLanguage()][] = $moderator_item->getEmail();
                }
                $recipients .= $moderator_item->getFullname().LF;
             }
             $moderator_item = $user_list->getNext();
          }
          foreach ($email_addresses as $language => $email_array) {
             if (count($email_array) > 0) {
                $old_lang = $translator->getSelectedLanguage();
                $translator->setSelectedLanguage($language);
                $subject = $translator->getMessage('USER_JOIN_CONTEXT_MAIL_SUBJECT',$user_item->getFullname(),$room_item->getTitle());
                $body  = $translator->getMessage('MAIL_AUTO',$translator->getDateInLang(getCurrentDateTimeInMySQL()),getTimeInLang(getCurrentDateTimeInMySQL()));
                $body .= LF.LF;
                if ( $room_item->isCommunityRoom() ) {
                   $body .= $translator->getMessage('USER_JOIN_COMMUNITY_MAIL_BODY',$user_item->getFullname(),$user_item->getUserID(),$user_item->getEmail(),$room_item->getTitle());
                } else {
                   $body .= $translator->getMessage('USER_JOIN_CONTEXT_MAIL_BODY',$user_item->getFullname(),$user_item->getUserID(),$user_item->getEmail(),$room_item->getTitle());
                }
                $body .= LF.LF;
                if ($check_message == 'YES') {
                   $body .= $translator->getMessage('USER_GET_MAIL_STATUS_YES');
                } else {
                   $body .= $translator->getMessage('USER_GET_MAIL_STATUS_NO');
                }
                $body .= LF.LF;
                if (!empty($_POST['description_user'])) {
                   $body .= $translator->getMessage('MAIL_COMMENT_BY',$user_item->getFullname(),$_POST['description_user']);
                   $body .= LF.LF;
                }
                $body .= $translator->getMessage('MAIL_SEND_TO',$recipients);
                $body .= LF;
                if ($check_message == 'YES') {
                   $body .= $translator->getMessage('MAIL_USER_FREE_LINK').LF;
                   $body .= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?cid='.$current_item_id.'&mod=account&fct=index'.'&selstatus=1';
                } else {
                   $body .= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?cid='.$current_item_id;
                }
                $mail = new cs_mail();
                $mail->set_to(implode(',',$email_array));
                $server_item = $environment->getServerItem();
                $default_sender_address = $server_item->getDefaultSenderAddress();
                if (!empty($default_sender_address)) {
                   $mail->set_from_email($default_sender_address);
                } else {
                   $mail->set_from_email('@');
                }
                $current_context = $environment->getCurrentContextItem();
                $mail->set_from_name($translator->getMessage('SYSTEM_MAIL_MESSAGE',$current_context->getTitle()));
                $mail->set_from_name($room_item->getTitle());
                $mail->set_reply_to_name($user_item->getFullname());
                $mail->set_reply_to_email($user_item->getEmail());
                $mail->set_subject($subject);
                $mail->set_message($body);
                $mail->send();
                $translator->setSelectedLanguage($old_lang);
             }
          }

          // send email to user when account is free automatically
          if ($user_item->isUser()) {

             // get contact moderator (TBD) now first moderator
             $user_list = $room_item->getModeratorList();
             $contact_moderator = $user_list->getFirst();

             // change context
             $translator->setEmailTextArray($room_item->getEmailTextArray());
             if ($room_item->isProjectRoom()) {
                $translator->setContext('project');
             } else {
                $translator->setContext('community');
             }
             $save_language = $translator->getSelectedLanguage();
             $translator->setSelectedLanguage($room_item->getLanguage());

             // email texts
             $subject = $translator->getMessage('MAIL_SUBJECT_USER_STATUS_USER',$room_item->getTitle());
             $body  = $translator->getMessage('MAIL_AUTO',$translator->getDateInLang(getCurrentDateTimeInMySQL()),$translator->getTimeInLang(getCurrentDateTimeInMySQL()));
             $body .= LF.LF;
             $body .= $translator->getEmailMessage('MAIL_BODY_HELLO',$user_item->getFullname());
             $body .= LF.LF;
             $body .= $translator->getEmailMessage('MAIL_BODY_USER_STATUS_USER',$user_item->getUserID(),$room_item->getTitle());
             $body .= LF.LF;
             $body .= $translator->getEmailMessage('MAIL_BODY_CIAO',$contact_moderator->getFullname(),$room_item->getTitle());
             $body .= LF.LF;
             $body .= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?cid='.$environment->getCurrentContextID();

             // send mail to user
             $mail = new cs_mail();
             $mail->set_to($user_item->getEmail());
             $mail->set_from_name($translator->getMessage('SYSTEM_MAIL_MESSAGE',$room_item->getTitle()));
            $server_item = $environment->getServerItem();
           $default_sender_address = $server_item->getDefaultSenderAddress();
           if (!empty($default_sender_address)) {
                $mail->set_from_email($default_sender_address);
           } else {
             $mail->set_from_email('@');
           }
             $mail->set_reply_to_email($contact_moderator->getEmail());
             $mail->set_reply_to_name($contact_moderator->getFullname());
             $mail->set_subject($subject);
             $mail->set_message($body);
             $mail->send();
          }
       }
   } elseif ( $room_item->checkNewMembersWithCode()
              and $room_item->getCheckNewMemberCode() != $_POST['code']
              and isset($_POST['code'])
            ) {
      $account_mode = 'member';
      $error = 'code';
   }
   if ($account_mode == 'to_room'){
      redirect($current_item_id, 'home', 'index', '');
   } else {
      $get_params = $environment->getCurrentParameterArray();
      if (isset($get_params['sort'])){
         $params['sort'] = $get_params['sort'];
      }
      if (isset($get_params['search'])){
         $params['search'] = $get_params['search'];
      }
      if (isset($get_params['seltime'])){
         $params['seltime'] = $get_params['seltime'];
      }
      if (isset($get_params['selroom'])){
         $params['selroom'] = $get_params['selroom'];
      }
      if (isset($get_params['sel_archive_room'])){
         $params['sel_archive_room'] = $get_params['sel_archive_room'];
      }
      $params['account'] = $account_mode;
      if ( isset($error) and !empty($error) ) {
         $params['error'] = $error;
      }
      redirect($environment->getCurrentContextID(), 'home', 'index', $params);
   }
}

if ( $environment->inServer() ) {

   $params = array();
   $params['environment'] = $environment;
   $params['with_modifying_actions'] = true;
   $context_detail_view = $class_factory->getClass(CONTEXT_GUIDE_DETAIL_VIEW,$params);
   unset($params);
   $context_detail_view->setItem($context_item);
   $page->addRoomDetail($context_detail_view);
}


// room list on the left side
include_once('classes/cs_guide_room_list_page.php');
$current_context = $environment->getCurrentContextItem();
$guide_room_list_page = new cs_guide_room_list_page($environment,$current_context->isOpen());
unset($current_context);
$page->addRoomList($guide_room_list_page->getViewObject());
unset($guide_room_list_page)
?>