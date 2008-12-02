<?PHP
//
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Jos� Manuel Gonz�lez V�zquez
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

// get room item and current user
$room_item = $environment->getCurrentContextItem();
$current_user = $environment->getCurrentUserItem();
$is_saved = false;

if (!$current_user->isModerator()) {
   include_once('classes/cs_errorbox_view.php');
   $errorbox = new cs_errorbox_view( $environment,
                                      true );
   $errorbox->setText(getMessage('ACCESS_NOT_GRANTED'));
   $page->add($errorbox);
}
// Access granted
else {

   // Find out what to do
   if ( isset($_POST['option']) ) {
      $command = $_POST['option'];
   } else {
      $command = '';
   }

   // Initialize the form
   $class_params= array();
   $class_params['environment'] = $environment;
   $form = $class_factory->getClass(CONFIGURATION_STRUCTURE_OPTIONS_FORM,$class_params);
   unset($class_params);
   $params = array();
   $params['environment'] = $environment;
   $params['with_modifying_actions'] = true;
   $form_view = $class_factory->getClass(CONFIGURATION_FORM_VIEW,$params);
   unset($params);

   // Save item
   if ( !empty($command) and isOption($command, getMessage('COMMON_CANCEL_BUTTON')) ) {
     redirect($environment->getCurrentContextID(),'configuration', 'index', '');
   }
   elseif ( !empty($command)
        and ( isOption($command, getMessage('COMMON_SAVE_BUTTON'))
              or isOption($command, getMessage('PREFERENCES_SAVE_BUTTON'))
             )
      ) {

      if ( $form->check() ) {

         /*********save buzzword options******/
         if ( isset($_POST['buzzword']) and !empty($_POST['buzzword']) and $_POST['buzzword'] == 'yes') {
            $room_item->setWithBuzzwords();
         } else {
           $room_item->setWithoutBuzzwords();
         }
         if ( isset($_POST['buzzword_mandatory']) and !empty($_POST['buzzword_mandatory']) and $_POST['buzzword_mandatory'] == 'yes' ) {
            $room_item->setBuzzwordMandatory();
         } else {
            $room_item->unsetBuzzwordMandatory();
         }
         if ( isset($_POST['buzzword_show']) and !empty($_POST['buzzword_show']) and $_POST['buzzword_show'] == 'yes' ) {
            $room_item->setBuzzwordShowExpanded();
         } else {
            $room_item->unsetBuzzwordShowExpanded();
         }


         /**********save tag options*******/
         if ( isset($_POST['tags']) and !empty($_POST['tags']) and $_POST['tags'] == 'yes') {
            $room_item->setWithTags();
         } else {
            $room_item->setWithoutTags();
         }
         if ( isset($_POST['tags_mandatory']) and !empty($_POST['tags_mandatory']) and $_POST['tags_mandatory'] == 'yes' ) {
            $room_item->setTagMandatory();
         } else {
            $room_item->unsetTagMandatory();
         }
         if ( isset($_POST['tags_edit']) and !empty($_POST['tags_edit']) and $_POST['tags_edit'] == 'yes' ) {
            $room_item->setTagEditedByModerator();
         } else {
            $room_item->setTagEditedByAll();
         }
         if ( isset($_POST['tags_show']) and !empty($_POST['tags_show']) and $_POST['tags_show'] == 'yes' ) {
            $room_item->setTagsShowExpanded();
         } else {
            $room_item->unsetTagsShowExpanded();
         }

         /**********save netnavigation options*******/
         if ( isset($_POST['netnavigation']) and !empty($_POST['netnavigation']) and $_POST['netnavigation'] == 'yes') {
            $room_item->setWithNetnavigation();
         } else {
            $room_item->setWithoutNetnavigation();
         }
         if ( isset($_POST['netnavigation_show']) and !empty($_POST['netnavigation_show']) and $_POST['netnavigation_show'] == 'yes' ) {
            $room_item->setNetnavigationShowExpanded();
         } else {
            $room_item->unsetNetnavigationShowExpanded();
         }


         // Save item
         $room_item->save();
#         redirect($environment->getCurrentContextID(),'configuration', 'index', '');
         $form_view->setItemIsSaved();
         $is_saved = true;

      }
   }

   // Load form data from postvars
   if ( !empty($_POST) and !$is_saved) {
      $form->setFormPost($_POST);
   }

   // Load form data from database
   elseif ( isset($room_item) ) {
      $form->setItem($room_item);
   }

   $form->prepareForm();
   $form->loadValues();


   include_once('functions/curl_functions.php');
   $form_view->setAction(curl($environment->getCurrentContextID(),$environment->getCurrentModule(),$environment->getCurrentFunction(),''));
   $form_view->setForm($form);
   if ( $environment->inPortal() or $environment->inServer() ){
      $page->addForm($form_view);
   } else {
      $page->add($form_view);
   }
}
?>