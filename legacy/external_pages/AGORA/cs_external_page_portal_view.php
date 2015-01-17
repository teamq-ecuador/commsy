<?PHP
// $Id: cs_page_guide_view.php,v 1.6 2008/07/21 10:23:48 jackewitz Exp $
//
// Release $Name:  $
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Jose Manuel Gonzalez Vazquez
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

/** upper class of the detail view
 */
global $class_factory;
$class_factory->includeClass(PAGE_VIEW);


/** language_functions are needed for language specific display
 */
include_once('functions/language_functions.php');

/** curl_functions are needed for actions
 */
include_once('functions/curl_functions.php');

/** date_functions are needed for language specific display
 */
include_once('functions/date_functions.php');

/** misc_functions are needed for display the commsy version
 */
include_once('functions/misc_functions.php');
include_once('functions/text_functions.php');

/** class for a page view of commsy
 * this class implements a page view of commsy
 */
class cs_external_page_portal_view extends cs_page_view {



   var $_current_parameter = '';

   var $_form_tags =false;

   var $_form_action= '';


   var $_with_room_list = true;

   /**
    * array - containing the hyperlinks for the page
    */
   var $_links = array();

   var $_space_between_views=true;

   var $_blank_page = false;

   var $_blank_page_content ='';

   var $_room_list_view = NULL;

   var $_room_detail_view = NULL;

   var $_configuration_list_view = NULL;

   var $_configuration_preferences_view = NULL;

   var $_mail_to_moderator_view = NULL;

   var $_form_view = NULL;

   var $_show_agbs = false;

   var $_warning = NULL;

   var $_agb_view = NULL;

   var $_with_delete_box = false;

   var $_delete_box_action_url = '';

   var $_delete_box_mode = 'detail';

   var $_delete_box_ids = NULL;
   /**
    * boolean - containing the flag for displaying a personal area for root (e.g. page commsy overview)
    * standard = false
    */
   var $_with_root_personal_area = false;

   /**
    * boolean - containing the flag for displaying a navigation bar for root (e.g. page commsy overview)
    * standard = false
    */
   var $_with_root_navigation_links = false;


   var $_bold_rubric = '';

   var $_shown_as_printable = false;

   var $_with_agb_link = true;

   var $_with_announcements = false;

   var $_style_image_path = 'images/layout/';


   private $_navigation_bar = NULL;

   public  $_login_redirect = NULL;

   protected $_has_to_change_email = false;
   
   /** constructor
    * the only available constructor, initial values for internal variables
    *
    * @param object  environment            environment of the context
    * @param boolean with_modifying_actions true: display with modifying functions
    *                                       false: display without modifying functions
    */
   function cs_external_page_portal_view ($params) {
      $this->cs_page_view($params);
      $this->_translator = $this->_environment->getTranslationObject();
      $this->_translator->addMessageDatFolder('external_pages/'.$this->_environment->getCurrentPortalID().'/externalmessages');
      $lang = '';
      if ( (isset($_GET['external_language']) and !empty($_GET['external_language']))){
          $lang = $_GET['external_language'];
      }elseif((isset($_POST['external_language']) and !empty($_POST['external_language']))){
          $lang = $_POST['external_language'];
      }
      if (!empty($lang)){
         $current_user = $this->_environment->getCurrentUserItem();
         if ( $current_user->isUser() ) {
            if ( !empty($lang) ) {
               $current_user->setLanguage($lang);
               $current_user->setChangeModificationOnSave(false);
               $current_user->save();
            }
         } else {
            $session_item = $this->_environment->getSessionItem();
            $session_item->setValue('message_language_select',$lang);
         }
         $this->_translator->setSelectedLanguage($lang);
         $this->_environment->setSelectedLanguage($lang);
         $params = $this->_environment->getCurrentParameterArray();
         unset($params['external_language']);
         $parameter_array = $this->_environment->_getCurrentParameterArray();
         $retour = array();
         if ( count($parameter_array) > 0 ) {
            foreach ($parameter_array as $parameter) {
               $temp_parameter_array = explode('=',$parameter);
               if ('external_language' != $temp_parameter_array[0]) {
                  $retour[] = $temp_parameter_array[0].'='.$temp_parameter_array[1];
               }
            }
         }
         $this->_environment->_current_parameter_array = $retour;
      }
   }

   public function setHasToChangeEmail () {
   	$this->_has_to_change_email = true;
   }
   
   public function setLoginRedirect () {
      $this->_login_redirect = true;
   }

   public function setNavigationBar ($value) {
      $this->_navigation_bar = $value;
   }

   function setBlankPage () {
      $this->_blank_page = true;
   }

   function setBlankPageContent ($content) {
      $this->_blank_page_content = $content;
   }

   function unsetBlankPage () {
      $this->_blank_page = false;
   }

   function setShowAGBs () {
      $this->_show_agbs = true;
   }

   function withAnnouncements(){
      $boolean = true;
      if ($this->_with_announcements == false){
         $boolean = false;
      }
      return $boolean;
   }

   /** adds a view on the left
    * this method adds a view to the page on the left hand side
    *
    * @param object cs_view a commsy view
    */
   function addRoomList ($view) {
      $this->_room_list_view = $view;
   }

   function addForm ($view) {
      $this->_form_view = $view;
   }

   function addAGBView ($view) {
      $this->_agb_view = $view;
   }

   function addWarning ($view) {
      $this->_warning = $view;
   }

   function addRoomDetail ($view) {
      $this->_room_detail_view = $view;
   }

   function addConfigurationListView ($view) {
      $this->_configuration_list_view = $view;
   }

   function addConfigurationPreferencesView ($view) {
      $this->_configuration_preferences_view = $view;
   }

   function addMailToModeratorFormView($view) {
      $this->_mail_to_moderator_view = $view;
   }


   function setSpace () {
      $this->_space_between_views = true;
   }

   function unsetSpace () {
      $this->_space_between_views = false;
   }

   function setContextID ($value) {
      $this->_context_id = (int)($value);
   }

   function setBoldRubric($value){
      $this->_bold_rubric = $value;
   }


   /** so page will be displayed without the personal area
    */
   function setWithoutPersonalArea () {
      $this->_with_personal_area = false;
   }

   /** so page will be displayed with the personal area for root user
    */
   function setWithRootPersonalArea () {
      $this->_with_root_personal_area = true;
   }

   /** so page will be displayed without the navigation links
    * this method skip a flag, so that the navigation links will not be shown
    */
   function setWithoutNavigationLinks () {
      $this->_with_navigation_links = false;
   }

   /** so page will be displayed with the navigation bar for root user
    */
   function setWithRootNavigationLinks () {
      $this->_with_root_navigation_links = true;
   }

   function addFormTags($action){
      $this->_form_tags = true;
      $this->_form_action = $action;
   }

   /** add an action to the page
    * this method adds an action (hyperlink) to the page view
    *
    * @param string  title        title of the action
    * @param string  explanantion explanation of the action
    * @param string  module       module of the action
    * @param string  function     function in module of the action
    * @param string  parameter    get parameter of the action
    */
   function addAction ($title, $explanation = '', $module = '', $function = '', $parameter = '') {
      $action['title'] = $title;
      $action['module'] = $module;
      $action['function'] = $function;
      $action['parameter'] = $parameter;
      $action['explanation'] = $explanation;
      $this->_links[] = $action;
   }

   /** get the linkbar as HTML
    * this method returns the linkbar as HTML - internal, do not use
    *
    * @return string linkbar as HTML
    *
    * @author CommSy Development Group
    */
   function _getLinkRowAsHTML () {

      $html = LF.'<!-- FADE LEFT MENUE -->'.LF;
      $session = $this->_environment->getSession();
      $left_menue_status = $session->getValue('left_menue_status');
      if ( $this->_without_left_menue or (isset($_GET['mode']) and $_GET['mode']=='print') ) {
   // do nothing
      } elseif ( $left_menue_status == 'disapear' ) {
         $html .=       '<div style="vertical-align:bottom;">';
         $params = $this->_environment->getCurrentParameterArray();
         $params['left_menue'] = 'apear';
         $html .= '<div style=" margin:0px; padding-left:5px;">'.LF;
         $html .= ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,'<span class="required">'.'> '.'</span>'.'<span style="font-size:8pt; color:black;">'.$this->_translator->getMessage('COMMON_FADE_IN').'</span>', '', '', '', '');
         $html .= '</div>'.LF;
         unset($params);
         $html .='</div>'.LF;
      } else {
         #$params = $this->_environment->getCurrentParameterArray();
         #$params['left_menue'] = 'disapear';
         #$html .=       '<div style="width:58.3em; vertical-align:bottom; padding-top:0px;">';
         #$html .= '<div style=" margin:0px; padding-top:0px; padding-left:5px;">'.LF;
         #$html .= ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,'<span class="required">'.'< '.'</span>'.'<span style="font-size:8pt; color:black;">'.$this->_translator->getMessage('COMMON_FADE_OUT').'</span>', '', '', '', '');
         #unset($params);
         #$html .= '</div>'.LF;
         #$html .='</div>'.LF;
      }

      $html .= LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<div class="portal_tabs_frame">'.LF;
      $html .= '<div class="portal-tabs">'.LF;
      $html .= '<div style="float:right; margin:0px; padding:0px;">'.LF;

      // language options
      $language_array = $this->_environment->getAvailableLanguageArray();

      $lang = $this->_environment->getSelectedLanguage();
      $params = array();
      $params['language'] = $lang;
      $html = '>> ';
      if ( $lang == 'en' ) {
         $flag_lang = 'de';
         $link_lang = 'de';
         $html .= ahref_curl($this->_environment->getCurrentContextID(),'language','change',$params,'<img src="images/flags/'.$flag_lang.'.gif" style="float: left; padding-top: 3px; padding-right: 2px;" alt="'.$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG').'"/>',$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG')).LF;
      }  else {
         $flag_lang = 'gb';
         $link_lang = 'en';
         $html .= ahref_curl($this->_environment->getCurrentContextID(),'language','change',$params,'<img src="images/flags/'.$flag_lang.'.gif" style="float: left; padding-top: 3px; padding-right: 2px;" alt="'.$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG').'"/>',$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG')).LF;
      }
      unset($params);

      // Always show context sensitive help
      $params = array();
      $params['module'] = $this->_module;
      $params['function'] = $this->_function;
      $html .= ahref_curl($this->_environment->getCurrentContextID(), 'help', 'context',
                          $params,
                          '?', '', 'help', '', '',
                          'onclick="window.open(href, target, \'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes, width=600, height=400\');"','','class="navlist_help"').LF;
      unset($params);
      $html .= '  '."\n";
      $html .= '</div>'."\n";
      $html .= '<div style="margin:0px; padding:0px;">'."\n";
      $html .= '<span class="navlist">&nbsp;</span>'."\n";
      $html .= '</div>'."\n";
      $html .= '<div style="position:absolute; top:-4px; left:-5px;"><img src="'.$this->_style_image_path.'ecke_portal_oben_links.gif" alt="" border="0"/></div>';
      $html .= '<div style="position:absolute; top:-4px; right:-5px;"><img src="'.$this->_style_image_path.'ecke_portal_oben_rechts.gif" alt="" border="0"/></div>';
      $html .= '</div>'."\n";
      $html .= '</div>'."\n";
      return $html;
   }

   function _getBlankLinkRowAsHTML () {
      $html  = LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<div id="tabs_frame">'.LF;
      $html .= '<div class="tabs">'.LF;
      $html .= '<div style="float:right; margin:0px; padding:0px;">'.LF;

      // Always show context sensitive help
      $params = array();
      $params['module'] = $this->_environment->getCurrentModule();
      $params['function'] = $this->_environment->getCurrentFunction();
      $html .= ahref_curl($this->_environment->getCurrentContextID(), 'help', 'context',
                             $params,
                              '?', '', '', '', '',
                             'onclick="window.open(href, target, \'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes, width=600, height=400\');"','','class="navlist_help"').LF;
      unset($params);
      $html .= '  '.LF;
      $html .= '</div>'.LF;
      $html .= '<div style="margin:0px; padding:0px;">'.LF;
      $html .= '<span class="navlist">&nbsp;</span>'.LF;
      $html .= '</div>'.LF;
      $html .= '<div style="position:absolute; top:-8px; left:-8px;"><img src="'.$this->_style_image_path.'ecke_portal_oben_links.gif" alt="" border="0"/></div>';
      $html .= '<div style="position:absolute; top:-8px; right:-8px;"><img src="'.$this->_style_image_path.'ecke_portal_oben_rechts.gif" alt="" border="0"/></div>';
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      $html .= '<!-- END TABS -->'.LF;
  }

   function getWelcomeTextAsHTML () {
      $html ='<div id="portal_wellcome_text">';

      $html .= '<table style="width:100%; margin:0px; padding:0px; border-collapse:collapse;" summary="Layout">'."\n";
      $html .= '<tr>'."\n";
      $html .= '<td style="width:35%; vertical-align:top; margin:0px; padding-top:0px; padding-left:0px; padding-bottom:5px;">'."\n";
      $current_portal = $this->_environment->getCurrentPortalItem();
      $logo_filename = $current_portal->getPictureFilename();
      $disc_manager = $this->_environment->getDiscManager();
      $disc_manager->setContextID($current_portal->getItemID());
      if ( !empty($logo_filename) and $disc_manager->existsFile($logo_filename) ) {
         $params = array();
         $params['picture'] = $current_portal->getPictureFilename();
         $curl = curl($current_portal->getItemID(), 'picture', 'getfile', $params,'');
         unset($params);
         if ($current_portal->isShowAnnouncementsOnHome()){
            $html .= '<img class="logo" style="width:200px;" src="'.$curl.'" alt="'.$this->_translator->getMessage('LOGO').'" border="0"/>';
         }else{
            $html .= '<img class="logo" style="width:300px; height:268px;" src="'.$curl.'" alt="'.$this->_translator->getMessage('LOGO').'" border="0"/>';
         }
      }
      $disc_manager->setContextID($this->_environment->getCurrentContextID());

      $html .= '</td>'."\n";
      if ($current_portal->isShowAnnouncementsOnHome()){
         $html .= '<td style="text-align:left; vertical-align:top; padding-top:5px; padding-bottom:5px; padding-left: 5px; font-weight: normal;">'."\n";
      }else{
         $html .= '<td style="text-align:left; vertical-align:top; padding-top:5px; padding-bottom:5px; padding-left: 15px; font-weight: normal;">'."\n";
      }
      $text = $current_portal->getDescriptionWellcome1();
      if ( !empty($text) ) {
         $html .= '<div style="width:99%; text-align:left; padding-top:10px; padding-bottom:5px;"><h1 class="portal_title">'.$current_portal->getDescriptionWellcome1().'</h1></div>'.LF;
      }
      $text = $current_portal->getDescriptionWellcome2();
      if ( !empty($text) ) {
         $html .= '<div style="width:99%; text-align:right; padding-bottom:10px;"><h1 class="portal_main_title">'.$current_portal->getDescriptionWellcome2().'</h1></div>'.LF;
      }
      if ($current_portal->isShowAnnouncementsOnHome()){
         $html .= '</td>'."\n";
         $html .= '</tr>'."\n";
         $html .= '<tr>'."\n";
         $html .= '<td colspan="2" style="text-align:left; vertical-align:top; padding-top:5px; padding-bottom:5px; padding-left: 5px; font-weight: normal;">'."\n";
      }
      $html .= $this->_text_as_html_long($current_portal->getDescription());
      if ($current_portal->isShowAnnouncementsOnHome()){
         $html .= '</td>'."\n";
      }
      $html .= '</tr>'."\n";
      $html .= '</table>'."\n";

      $html .= '</div>'."\n";
      return $html;
   }

   function _getModeratorMailTextAsHTML(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'."\n";

      $html .= '<div style="font-weight:normal; padding:5px;">'."\n";
      if ( isset($this->_mail_to_moderator_view) ){
         $html .= $this->_mail_to_moderator_view->asHTML();
      }
      $html .= '</div>'."\n";
      return $html;
   }

   function _getServerWelcomeTextAsHTML(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'."\n";
      $html .= '<div class="welcome_frame" style="width: 100%;">'.LF;
      $html .= '<div class="content_without_fader">';
      $html .= '<div style="margin:0px; padding:0px 0px;">'."\n";

      $html .= '<table style="width:100%; margin:0px; padding:0px; border-collapse:collapse;" summary="Layout">'."\n";
      $html .= '<tr>'."\n";
      $current_portal = $this->_environment->getServerItem();
      $html .= '<td style="text-align:left; vertical-align:top; padding-top:5px; padding-bottom:5px; padding-left: 5px; font-weight: normal;">'."\n";
      $html .= '</td>'."\n";
      $html .= '</tr>'."\n";
      $html .= '<tr>'."\n";
      $html .= '<td  style="text-align:left; vertical-align:top; padding-top:5px; padding-bottom:5px; padding-left: 5px; font-weight: normal;">'."\n";
      $html .= $this->_text_as_html_long($current_portal->getDescription());
      $current_user = $this->_environment->getCurrentUser();
      if ( $current_user->isRoot() ){
         $html .= '<div class="search_link" style="padding-left:0px; padding-top: 5px;">'.LF;
         $html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','index','',$this->_translator->getMessage('SERVER_CONFIGURATION_ACTION')).BRLF;
         $html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','preferences',array('iid' => 'NEW'),$this->_translator->getMessage('PORTAL_ENTER_NEW')).BRLF;
         $html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'context','logout','',$this->_translator->getMessage('LOGOUT')).BRLF;
         $html .= '</div>'.LF;
      }
      $html .= '</td>'."\n";
      $html .= '</tr>'."\n";
      $html .= '</table>'."\n";



      $html .= '</div>'."\n";
      $html .= '<div style="position:absolute; top:-4px; left:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_links.gif" alt="" border="0"/></div>'.LF;
      $html .= '<div style="position:absolute; top:-4px; right:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_rechts.gif" alt="" border="0"/></div>'.LF;

      $html .= '</div>'.LF;
      $html .= '<div class="frame_bottom">'.LF;
      $html .= '<div class="content_bottom">'.LF;
      $html .= '<div style="position:absolute; top:-11px; left:-7px;"><img src="'.$this->_style_image_path.'ecke_unten_links.gif" alt=""/></div>';
      $html .= '<div style="position:absolute; top:-11px; right:-7px;"><img src="'.$this->_style_image_path.'ecke_unten_rechts.gif" alt=""/></div>';
      $html .= '</div>'."\n";
      $html .= '</div>'."\n";
      $html .= '</div>'.LF;
      return $html;
   }


   function _getSystemInfoAsHTML(){
      $html ='';
      $html .='<div style="font-size:8pt; padding-top:0px; margin-top:3px;">'.LF;
      $html .= '<div class="footer" style="text-align:left; padding-left:0px; padding-right:0px; padding-top:0px; padding-bottom:10px;">'.LF;
      #$html .= '<a href="http://tidy.sourceforge.net/" target="_top" title="HTML Tidy">'.'<img src="images/checked_by_tidy.gif" style="height:14px; vertical-align: bottom;" alt="Tidy"/></a>';
      $html .= '&nbsp;&nbsp;<a href="http://www.commsy.net" target="_top" title="'.$this->_translator->getMessage('COMMON_COMMSY_LINK_TITLE').'">CommSy '.getCommSyVersion().'</a>';
      $version_addon = $this->_environment->getConfiguration('c_version_addon');
      if ( !empty($version_addon) ) {
         $html .= ' - '.$version_addon;
      }
      $html .= '</div>'.LF;
      $html .='</div>'.LF;
      return $html;
   }


   function _getAGBTextAsHTML(){
      $html ='';
      $html .= '<div style="width: 43em; padding-left:10px; font-weight:normal;">'.LF;
      $html .= LF.'<table style="border-collapse:collapse; padding:0px;  margin-top:5px; width:100%;" summary="Layout">'.LF;
      $html .='<tr>'.LF;
      $html .= '<td style="width:100%;">'.LF;
      $html .= $this->_getLogoAsHTML().LF;
      $html .= '</td>'.LF;
      $html .= '</tr>'.LF;
      $html .= '<tr>'.LF;
      $html .= '<td>'.LF;
      $html .= $this->_getAGBViewAsHTML().LF;
      $html .= '</td>'.LF;
      $html .= '</tr>'.LF;
      $html .= '</table>'.LF;
      $html .= '</div>'.LF;
      $html .= '<div style="padding-left:0px;">'.LF;
      $html .= $this->_getSystemInfoAsHTML();
      $html .= '</div>'.LF;
      return $html;
   }

   function _getAGBViewAsHTML () {
      #$html  = LF.'<!-- BEGIN TABS -->'.LF;
      #$html .= '<div id="tabs_frame" >'.LF;
      #$html .= '<div class="tabs">'.LF;
      #$html .= '<div style="float:right; margin:0px; padding:0px;">'.LF;

         // Always show context sensitive help
      #   $params = array();
      #   $params['module'] = $this->_module;
      #   $params['function'] = $this->_function;
      #   $html .= ahref_curl($this->_environment->getCurrentContextID(), 'help', 'context',
      #                       $params,
      #                       '?', '', 'help', '', '',
      #                       'onclick="window.open(href, target, \'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes, width=600, height=400\');"','','class="navlist_help"').LF;
      #   unset($params);
      #$html .= '</div>'.LF;

      if ( isset($this->_agb_view) and $this->_agb_view instanceof cs_form_view_plain ) {
         $title = $this->_agb_view->getTitle();
      }
      if ( empty($title) ) {
         $title = $this->_translator->getMessage('AGB_CONFIRMATION');
      }
      if ( !empty($this->_navigation_bar) ) {
         $title = $this->_navigation_bar;
      }

      #$html .= '<div style="margin:0px; padding:0px;">'.LF;
      $html .= '<h2>'.$title.'</h2>'.LF;
      #$html .= '</div>'.LF;
      #$html .= '<div style="position:absolute; top:-4px; left:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_links.gif" alt="" border="0"/></div>';
      #$html .= '<div style="position:absolute; top:-4px; right:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_rechts.gif" alt="" border="0"/></div>';
      #$html .= '</div>'.LF;
      #$html .= '</div>'.LF;
      #$html .= '<!-- END TABS -->'.LF;
      #$html .= '<div style="border-left: 2px solid #C3C3C3; border-right: 2px solid #C3C3C3; padding:0px 0px; margin:0px;">'.LF;
      $html .= '<div>'.LF;
      #$html .= '<div class="content_fader">';
      $html .= '<a name="top"></a>'.LF;
      if ( isset($this->_agb_view) ) {
         $html .= $this->_agb_view->asHTML();
      }
      $html .='</div>';
      #$html .= '<div class="top_of_page">'.LF;
      #$html .= '<div>'.LF;
      #$html .= '<a href="#top">'.'<img src="images/browse_left2.gif" alt="&lt;" border="0"/></a>&nbsp;<a href="#top">'.$this->_translator->getMessage('COMMON_TOP_OF_PAGE').'</a>';
      #$html .= '</div>'.LF;
      #$html .= '</div>'.LF;
      #$html .= '</div>'.LF;
      #$html .= '</div>'.LF;
      #$html .= '<div class="frame_bottom">'.LF;
      #$html .= '<div class="content_bottom">'.LF;
      #$html .= '<div style="position:absolute; top:-11px; left:-5px;"><img src="'.$this->_style_image_path.'ecke_unten_links.gif" alt=""/></div>';
      #$html .= '<div style="position:absolute; top:-11px; right:-5px;"><img src="'.$this->_style_image_path.'ecke_unten_rechts.gif" alt=""/></div>';
      #$html .= '</div>'.LF;
      #$html .= '</div>'.LF;
      return $html;
   }

   /** get room window as html
    *
    * param cs_project_item project room item
    */

   
   function _getRoomAccessAsHTML ($item, $mode = 'none') {
   	$current_user = $this->_environment->getCurrentUserItem();
   	$may_enter = $item->mayEnter($current_user);
   	$html ='';
   	//Projektraum User
   	$user_manager = $this->_environment->getUserManager();
   	$user_manager->setUserIDLimit($current_user->getUserID());
   	$user_manager->setAuthSourceLimit($current_user->getAuthSource());
   	$user_manager->setContextLimit($item->getItemID());
   	$user_manager->select();
   	$user_list = $user_manager->get();
   	if (!empty($user_list)) {
   		$room_user = $user_list->getFirst();
   	} else {
   		$room_user = '';
   	}
   	 
   	// archive
   	if ( $may_enter
   			and empty($room_user)
   			and $item->isClosed()
   			and !$this->_environment->isArchiveMode()
   	) {
   		$user_manager = $this->_environment->getZzzUserManager();
   		$user_manager->setUserIDLimit($current_user->getUserID());
   		$user_manager->setAuthSourceLimit($current_user->getAuthSource());
   		$user_manager->setContextLimit($item->getItemID());
   		$user_manager->select();
   		$user_list = $user_manager->get();
   		if (!empty($user_list)) {
   			$room_user = $user_list->getFirst();
   		} else {
   			$room_user = '';
   		}
   		unset($user_list);
   	}
   
   	$current_user = $this->_environment->getCurrentUserItem();
   
   	//Anzeige außerhalb des Anmeldeprozesses
   	if ($mode !='member' and $mode !='info' and $mode !='email'){
   		$current_user = $this->_environment->getCurrentUserItem();
   		$may_enter = $item->mayEnter($current_user);
   		// Eintritt erlaubt
   		if ( $may_enter and ( ( !empty($room_user) and $room_user->isUser() ) or $current_user->isRoot() ) ) {
   			$actionCurl = curl( $item->getItemID(),
   					'home',
   					'index',
   					'');
   			$html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
   			$actionCurl = curl( $item->getItemID(),
   					'home',
   					'index',
   					'');
   			$html .= '<div style="padding-top:8px;">&nbsp;</div>'.BRLF;
   			//als Gast Zutritt erlaubt, aber kein Mitglied
   		} elseif ( $item->isLocked() ) {
   			$html .= '<img src="images/door_closed_large.gif" alt="door closed" />'.LF;
   		} elseif ( $item->isOpenForGuests()
   				and empty($room_user)
   		) {
   			$actionCurl = curl( $item->getItemID(),
   					'home',
   					'index',
   					'');
   			$html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
   			$actionCurl = curl( $item->getItemID(),
   					'home',
   					'index',
   					'');
   			$html .= '<div style="padding-top:5px;">'.'> <a href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
   			if ( $item->isOpen()
   					and !$this->_current_user->isOnlyReadUser()
   			) {
   				$params = array();
   				$params = $this->_environment->getCurrentParameterArray();
   				$params['account'] = 'member';
   				$params['room_id'] = $item->getItemID();
   				$actionCurl = curl( $this->_environment->getCurrentContextID(),
   						'home',
   						'index',
   						$params,
   						'');
   				$html .= '<div style="padding-top:3px;">'.'> <a href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a></div>'.LF;
   				unset($params);
   			} else {
   				$html .= '<div style="padding-top:3px;">> <span class="disabled">'.$this->_translator->getMessage('CONTEXT_JOIN').'</span></div>'.LF;
   			}
   
   			//Um Erlaubnis gefragt
   		} elseif ( !empty($room_user) and $room_user->isRequested() ) {
   			if ( $item->isOpenForGuests() ) {
   				$actionCurl = curl( $item->getItemID(),
   						'home',
   						'index',
   						'');
   				$html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
   				$actionCurl = curl( $item->getItemID(),
   						'home',
   						'index',
   						'');
   				$html .= '<div style="padding-top:7px; text-align: center;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
   			} else {
   				$html .= '<img src="images/door_closed_large.gif" alt="door closed"/>'.LF;
   			}
   			$html .= '<div style="padding-top:7px;"><p style="margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">'.$this->_translator->getMessage('ACCOUNT_NOT_ACCEPTED_YET').'</p></div>'.LF;
   			//Erlaubnis verweigert
   		} elseif ( !empty($room_user) and $room_user->isRejected() ) {
   			if ( $item->isOpenForGuests() ) {
   				$actionCurl = curl( $item->getItemID(),
   						'home',
   						'index',
   						'');
   				$html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open"/></a>'.BRLF;
   				$actionCurl = curl( $item->getItemID(),
   						'home',
   						'index',
   						'');
   				$html .= '<div style="padding-top:7px;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
   			} else {
   				$html .= '<img src="images/door_closed_large.gif" alt="door closed"/>'.LF;
   			}
   			$html .= '<div style="padding-top:7px;"><p style=" margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">'.$this->_translator->getMessage('ACCOUNT_NOT_ACCEPTED').'</p></div>'.LF;
   
   			// noch nicht angemeldet als Mitglied im Raum
   		} else {
   			$html .= '<img src="images/door_closed_large.gif" alt="door closed" style="vertical-align: middle; "/>'.BRLF;
   			if ( $item->isOpen()
   					and !$this->_current_user->isOnlyReadUser()
   			) {
   				$params = array();
   				$params = $this->_environment->getCurrentParameterArray();
   				$params['account'] = 'member';
   				$params['room_id'] = $item->getItemID();
   				$actionCurl = curl( $this->_environment->getCurrentContextID(),
   						'home',
   						'index',
   						$params,
   						'');
   				$session_item = $this->_environment->getSessionItem();
   				if ($session_item->issetValue('login_redirect')) {
   					$html .= '<div style="padding-top:7px;"><p style="margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">';
   					if ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
   						$html .= $this->_translator->getMessage('CONTEXT_ENTER_LOGIN','<a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a>');
   					} else {
   						$current_user_item = $this->_environment->getCurrentUserItem();
   						$current_user_item = $current_user_item->getRelatedCommSyUserItem();
   						if ( isset($current_user_item) and $current_user_item->isUser() ) {
   							$html .= $this->_translator->getMessage('CONTEXT_ENTER_LOGIN_NOT_ALLOWED').LF;
   							if($item->isGroupRoom()){
   								$linked_project_item = $item->getLinkedProjectItem();
   								$user_related_project_list = $current_user_item->getRelatedProjectList();
   								$user_is_room_member = false;
   								if ( $user_related_project_list->isNotEmpty() ) {
   									$room_item = $user_related_project_list->getFirst();
   									while ($room_item) {
   										if($room_item->getItemID() == $linked_project_item->getItemID()){
   											$user_is_room_member = true;
   											break;
   										}
   										$room_item = $user_related_project_list->getNext();
   									}
   								}
   								$html .= '<br/><br/>';
   								if($user_is_room_member){
   									$html .= $this->_translator->getMessage('CONTEXT_ENTER_NEED_TO_BECOME_GROUP_MEMBER', $item->getTitle(), $linked_project_item->getTitle());
   									$html .= '<br/><br/>';
   									$actionCurl = curl($linked_project_item->getItemID(),
   											'group',
   											'detail',
   											array('account' => 'member', 'iid' => $item->getLinkedGroupItemID()),
   											'');
   									$html .= '<a href="'.$actionCurl.'">' . $this->_translator->getMessage('COMMON_REGISTER_HERE') . '</a>'.LF;
   								} else {
   									$user_manager->setUserIDLimit($current_user->getUserID());
   									$user_manager->setAuthSourceLimit($current_user->getAuthSource());
   									$user_manager->setContextLimit($linked_project_item->getItemID());
   									$user_manager->select();
   									$user_list = $user_manager->get();
   									if (!empty($user_list)) {
   										$room_user = $user_list->getFirst();
   									} else {
   										$room_user = '';
   									}
   									if(!$room_user->isRejected()){
   										$html .= $this->_translator->getMessage('CONTEXT_ENTER_NEED_TO_BECOME_ROOM_MEMBER', $linked_project_item->getTitle(), $item->getTitle());
   										$html .= '<br/><br/>';
   										$actionCurl = curl($this->_environment->getCurrentContextID(),
   												'home',
   												'index',
   												array('room_id' => $linked_project_item->getItemID(), 'account' => 'member'),
   												'');
   										$html .= '<a href="'.$actionCurl.'">' . $this->_translator->getMessage('COMMON_REGISTER_HERE') . '</a>'.LF;
   									} else {
   										$html .= $this->_translator->getMessage('ACCOUNT_NOT_ACCEPTED');
   									}
   								}
   							}
   						} else {
   							$html .= $this->_translator->getMessage('CONTEXT_ENTER_LOGIN2');
   						}
   						unset($current_user_item);
   					}
   					$html .= '</p></div>'.LF;
   					unset($session_item);
   				} elseif ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
   					$html .= '<div style="padding-top:5px;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a></div>'.LF;
   				}
   				unset($params);
   			} elseif ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
   				$html .= '<div style="padding-top:5px;">> <span class="disabled">'.$this->_translator->getMessage('CONTEXT_JOIN').'</span></div>'.LF;
   			}
   			$html .= '<div style="padding-top:6px;">&nbsp;</div>'.LF;
   		}
   	}
   	return $html;
   }
   
    
   
   function _getRoomAccessAsHTMLOld ($item, $mode = 'none') {
      $current_user = $this->_environment->getCurrentUserItem();
      $may_enter = $item->mayEnter($current_user);
      $html ='';
      //Projektraum User
      $user_manager = $this->_environment->getUserManager();
      $user_manager->setUserIDLimit($current_user->getUserID());
      $user_manager->setAuthSourceLimit($current_user->getAuthSource());
      $user_manager->setContextLimit($item->getItemID());
      $user_manager->select();
      $user_list = $user_manager->get();
      if (!empty($user_list)) {
         $room_user = $user_list->getFirst();
      } else {
         $room_user = '';
      }
      $current_user = $this->_environment->getCurrentUserItem();

      //Anzeige außerhalb des Anmeldeprozesses
      if ($mode !='member' and $mode !='info' and $mode !='email'){
         $current_user = $this->_environment->getCurrentUserItem();
         $may_enter = $item->mayEnter($current_user);
         // Eintritt erlaubt
         if ( $may_enter and ( ( !empty($room_user) and $room_user->isUser() ) or $current_user->isRoot() ) ) {
            $actionCurl = curl( $item->getItemID(),
                             'home',
                             'index',
                             '');
            $html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
            $actionCurl = curl( $item->getItemID(),
                             'home',
                             'index',
                             '');
         $html .= '<div style="padding-top:8px;">&nbsp;</div>'.BRLF;
         //als Gast Zutritt erlaubt, aber kein Mitglied
         } elseif ( $item->isLocked() ) {
            $html .= '<img src="images/door_closed_large.gif" alt="door closed" />'.LF;
         } elseif ( $item->isOpenForGuests()
                    and empty($room_user)
                  ) {
            $actionCurl = curl( $item->getItemID(),
                             'home',
                             'index',
                             '');
            $html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
            $actionCurl = curl( $item->getItemID(),
                             'home',
                             'index',
                             '');
            $html .= '<div style="padding-top:5px;">'.'> <a href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
            if ($item->isOpen()) {
               $params = array();
               $params = $this->_environment->getCurrentParameterArray();
               $params['account'] = 'member';
               $params['room_id'] = $item->getItemID();
               $actionCurl = curl( $this->_environment->getCurrentContextID(),
                                  'home',
                                  'index',
                                  $params,
                                  '');
               $html .= '<div style="padding-top:3px;">'.'> <a href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a></div>'.LF;
              unset($params);
           } else {
              $html .= '<div style="padding-top:3px;"><span class="disabled">'.$this->_translator->getMessage('CONTEXT_JOIN').'</span></div>'.LF;
           }

         //Um Erlaubnis gefragt
         } elseif ( !empty($room_user) and $room_user->isRequested() ) {
            if ( $item->isOpenForGuests() ) {
               $actionCurl = curl( $item->getItemID(),
                                   'home',
                                   'index',
                                   '');
               $html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open" /></a>'.BRLF;
               $actionCurl = curl( $item->getItemID(),
                                   'home',
                                   'index',
                                   '');
               $html .= '<div style="padding-top:7px; text-align: center;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
            } else {
               $html .= '<img src="images/door_closed_large.gif" alt="door closed"/>'.LF;
            }
            $html .= '<div style="padding-top:7px;"><p style="margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">'.$this->_translator->getMessage('ACCOUNT_NOT_ACCEPTED_YET').'</p></div>'.LF;
         //Erlaubnis verweigert
         } elseif ( !empty($room_user) and $room_user->isRejected() ) {
            if ( $item->isOpenForGuests() ) {
               $actionCurl = curl( $item->getItemID(),
                                   'home',
                                   'index',
                                   '');
               $html .= '<a class="room_window" href="'.$actionCurl.'"><img src="images/door_open_large.gif" alt="door open"/></a>'.BRLF;
               $actionCurl = curl( $item->getItemID(),
                                   'home',
                                   'index',
                                   '');
                $html .= '<div style="padding-top:7px;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_ENTER_AS_GUEST').'</a></div>'.LF;
         } else {
               $html .= '<img src="images/door_closed_large.gif" alt="door closed"/>'.LF;
         }
            $html .= '<div style="padding-top:7px;"><p style=" margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">'.$this->_translator->getMessage('ACCOUNT_NOT_ACCEPTED').'</p></div>'.LF;

         // noch nicht angemeldet als Mitglied im Raum
         } else {
            $html .= '<img src="images/door_closed_large.gif" alt="door closed" style="vertical-align: middle; "/>'.BRLF;
            if ( $item->isOpen() ) {
               $params = array();
               $params = $this->_environment->getCurrentParameterArray();
               $params['account'] = 'member';
               $params['room_id'] = $item->getItemID();
               $actionCurl = curl( $this->_environment->getCurrentContextID(),
                                  'home',
                                  'index',
                                  $params,
                                  '');
               $session_item = $this->_environment->getSessionItem();
               if ($session_item->issetValue('login_redirect')) {
                  $html .= '<div style="padding-top:7px;"><p style="margin-top:0px; margin-bottom:0px;text-align:left;" class="disabled">';
                  if ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
                     $html .= $this->_translator->getMessage('CONTEXT_ENTER_LOGIN_EXTERNAL_PAGES','<a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a>');
                  } else {
                     $html .= $this->_translator->getMessage('CONTEXT_ENTER_LOGIN_EXTERNAL_PAGES2');
                  }
                  $html .= '</p></div>'.LF;
                  $session_item->unsetValue('login_redirect');
                  unset($session_item);
               } elseif ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
                  $html .= '<div style="padding-top:5px;">'.'> <a class="room_window" href="'.$actionCurl.'">'.$this->_translator->getMessage('CONTEXT_JOIN').'</a></div>'.LF;
               }
               unset($params);
            } elseif ( !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
               $html .= '<div style="padding-top:5px;">> <span class="disabled">'.$this->_translator->getMessage('CONTEXT_JOIN').'</span></div>'.LF;
            }
            $html .= '<div style="padding-top:6px;">&nbsp;</div>'.LF;
         }
      }
      return $html;
   }

   function _getRoomFacts($item){
      $html ='';
      // prepare moderator
      $html_temp='';
      $moda = array();
      $moda_list = $item->getContactModeratorList();
      $current_user = $this->_environment->getCurrentUser();
      $moda_item = $moda_list->getFirst();
      while ($moda_item) {
         $html_temp .= '<li style="font-weight:normal; font-size:8pt;">'.$this->_text_as_html_short($moda_item->getFullName()).'</li>';
         $moda_item = $moda_list->getNext();
      }
      $html .= '<span style="font-weight:bold;">'.$this->_translator->getMessage('ROOM_CONTACT').':</span>'.LF;
      $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
      if (!empty($html_temp) ) {
         $temp_array = array();
         $html .= $html_temp;
         $params = $this->_environment->getCurrentParameterArray();
         $params['account'] = 'email';
         $params['room_id'] = $item->getItemID();
         $actionCurl = curl( $this->_environment->getCurrentContextID(),
                             'home',
                             'index',
                             $params,
                             '');
         unset($params);
         if ($current_user->isUser() and $this->_with_modifying_actions ) {
            $html .= '<li style="font-weight:bold; font-size:8pt;">'.'<a href="'.$actionCurl.'">'.$this->_translator->getMessage('EMAIL_CONTACT_MODERATOR').'</a></li>';
         }else{
            $html .= '<li style="font-weight:bold; font-size:8pt;">'.'<span class="disabled">'.$this->_translator->getMessage('EMAIL_CONTACT_MODERATOR').'</span></li>';
         }
      }else{
         $html .= '<li style="font-weight:bold; font-size:8pt;">'.'<span class="disabled">'.$this->_translator->getMessage('COMMON_NO_CONTACTS').'</span></li>';
      }
      $html .= '</ul>'.LF;
      // prepare time (clock pulses)
      $current_context = $this->_environment->getCurrentContextItem();
      if ( $current_context->showTime() and ( $item->isProjectRoom() or $item->isCommunityRoom() ) ) {
         $time_list = $item->getTimeList();
         if ($time_list->isNotEmpty()) {
            $this->translatorChangeToPortal();
            $html .= '<span style="font-weight:bold;">'.$this->_translator->getMessage('COMMON_TIME_NAME').':</span>'.LF;
            $this->translatorChangeToCurrentContext();
            if ($item->isContinuous()) {
               $time_item = $time_list->getFirst();
               if ($item->isClosed()) {
                  $time_item_last = $time_list->getLast();
                  if ($time_item_last->getItemID() == $time_item->getItemID()) {
                     $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
          $html .= '   <li style="font-weight:normal; font-size:8pt;">'.LF;
          $html .= $this->_translator->getTimeMessage($time_item->getTitle()).LF;
          $html .= '   </li>'.LF;
          $html .= '</ul>'.LF;
       } else {
          $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
          $html .= '   <li style="font-weight:normal; font-size:8pt;">'.LF;
          $html .= $this->_translator->getMessage('COMMON_FROM2').' '.$this->_translator->getTimeMessage($time_item->getTitle()).LF;
          $html .= '   </li>'.LF;
          $html .= '   <li style="font-weight:normal; font-size:8pt;">'.LF;
          $html .= $this->_translator->getMessage('COMMON_TO').' '.$this->_translator->getTimeMessage($time_item_last->getTitle()).LF;
          $html .= '   </li>'.LF;
          $html .= '</ul>'.LF;
       }
               } else {
                  $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
       $html .= '   <li style="font-weight:normal; font-size:8pt;">'.LF;
       $html .= $this->_translator->getMessage('ROOM_CONTINUOUS_SINCE').' '.BRLF.$this->_translator->getTimeMessage($time_item->getTitle()).LF;
       $html .= '   </li>'.LF;
       $html .= '</ul>'.LF;
               }
            } else {
               $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
               $time_item = $time_list->getFirst();
               while ($time_item) {
                  $html .= '<li style="font-weight:normal; font-size:8pt;">'.$this->_translator->getTimeMessage($time_item->getTitle()).'</li>'.LF;
       $time_item = $time_list->getNext();
               }
               $html .= '</ul>'.LF;
            }
         } else {
           $this->translatorChangeToPortal();
           $html .= '<span style="font-weight:bold;">'.$this->_translator->getMessage('COMMON_TIME_NAME').':</span>'.LF;
           $this->translatorChangeToCurrentContext();
           $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
           $html .= '   <li style="font-weight:normal; font-size:8pt;"><span class="disabled">'.LF;
           $html .= $this->_translator->getMessage('ROOM_NOT_LINKED').LF;
           $html .= '   </span></li>'.LF;
           $html .= '</ul>'.LF;
         }
      }

      // community list
      if ($item->isProjectRoom()) {
         $community_list = $item->getCommunityList();
         $html .= '<span style="font-weight:bold;">'.$this->_translator->getMessage('COMMUNITYS').':</span>'.LF;
         $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;
         if ($community_list->isNotEmpty()) {
            $community_item = $community_list->getFirst();
            while ($community_item) {
               $html .= '<li style="font-weight:normal; font-size:8pt;">'.LF;
               $params = $this->_environment->getCurrentParameterArray();
               $params['room_id'] = $community_item->getItemID();
               $link = ahref_curl($this->_environment->getCurrentContextID(),'home','index',$params,$community_item->getTitle());
               $html .= $link.LF;
               $html .= '</li>'.LF;
               $community_item = $community_list->getNext();
            }
            $html .= '</ul>'.LF;
         } else {
            $html .= '<li style="font-weight:normal; font-size:8pt;" ><span class="disabled">'.LF;
            $html .= $this->_translator->getMessage('ROOM_NOT_LINKED').LF;
            $html .= '</span></li>'.LF;
            $html .= '</ul>'.LF;
               }
      }


      // add-ons
      if ( $item->showHomepageDescLink() or
            ( $item->showWikiLink()
              and $item->existWiki()
              and $item->issetWikiPortalLink()
            )
         ) {
         $html .= '<span style="font-weight:bold;">'.$this->_translator->getMessage('COMMON_PORTAL_LINKS').':</span>'.LF;
         $html .= '<ul style="margin-left:0px;margin-top:0em; margin-bottom:0.5em; padding-top:0px;padding-left:1em;">'.LF;

         if (
               ( $item->showWikiLink()
                 and $item->existWiki()
                 and $item->issetWikiPortalLink()
               )
            ) {
            $html .= '<li style="font-weight:normal; font-size:8pt;">'.LF;
            global $c_pmwiki_path_url;
            $html .= '<span style="white-space:nowrap;"> <a href="'.$c_pmwiki_path_url.'/wikis/'.$item->getContextID().'/'.$item->getItemID().'" target="_blank">'.$item->getWikiTitle().'</a> ('.$this->_translator->getMessage('COMMON_WIKI_LINK').')</span>';
            $html .= '</li>'.LF;
         }

         if ( $item->showHomepageDescLink() ) {
            $html .= '<li style="font-weight:normal; font-size:8pt;">'.LF;
            $link = ahref_curl( $item->getitemID(),
                                'context',
                                'forward',
                                array('tool' => 'homepage'),
                                $this->_translator->getMessage('HOMEPAGE_HOMEPAGE'),'','_blank');
            $html .= '<span style="white-space:nowrap;"> '.$link.'</span>';
            $html .= '</li>'.LF;
         }

         $html .= '</ul>'.LF;
      }
      return $html;
   }

   function _getRoomForm($item, $mode){
     $html ='';
     $current_user = $this->_environment->getCurrentUser();
     // Person ist User und will Mitglied werden
     if ($mode=='member' and $current_user->isUser()) {
        $translator = $this->_environment->getTranslationObject();
        $html .= '<div>'.LF;
        $formal_data = array();
        $get_params = $this->_environment->getCurrentParameterArray();
        if (isset($get_params['sort'])){
           $params['sort'] = $get_params['sort'];
        }elseif (isset($_POST['sort'])){
           $params['sort'] = $get_params['sort'];
        }
        if (isset($get_params['search'])){
           $params['search'] = $get_params['search'];
        }elseif (isset($_POST['search'])){
           $params['search'] = $get_params['search'];
        }
        if (isset($get_params['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }elseif (isset($_POST['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }
        if (isset($get_params['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }elseif (isset($_POST['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }
        if (isset($get_params['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }elseif (isset($_POST['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }
        $params['room_id'] = $item->getItemID();
        $html .= '<form method="post" action="'.curl($this->_environment->getCurrentContextID(),'home','index',$params).'" name="member">'.LF;
        if (isset($get_params['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$get_params['sort'].'"/>'.LF;
        }elseif (isset($_POST['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['sort'].'"/>'.LF;
        }
        if (isset($get_params['search'])){
           $html .= '   <input type="hidden" name="search" value="'.$get_params['search'].'"/>'.LF;
        }elseif (isset($_POST['search'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['search'].'"/>'.LF;
        }
        if (isset($get_params['seltime'])){
           $html .= '   <input type="hidden" name="seltime" value="'.$get_params['seltime'].'"/>'.LF;
        }elseif (isset($_POST['seltime'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['seltime'].'"/>'.LF;
        }
        if (isset($get_params['selroom'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['selroom'].'"/>'.LF;
        }elseif (isset($_POST['selroom'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['selroom'].'"/>'.LF;
        }
        if (isset($get_params['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }elseif (isset($_POST['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }

        if ($item->checkNewMembersWithCode()) {
           $html .= $this->_translator->getMessage('ACCOUNT_GET_CODE_TEXT');
           if ( isset($get_params['error']) and !empty($get_params['error']) ) {
              $temp_array[0] = $this->_translator->getMessage('COMMON_ATTENTION').': ';
              $temp_array[1] = $this->_translator->getMessage('EXTERNALMESSAGES_ACCOUNT_PROCESS_ROOM_CODE_ERROR');
              $formal_data[] = $temp_array;
           }
           $temp_array[0] = $this->_translator->getMessage('ACCOUNT_PROCESS_ROOM_CODE').': ';
           $temp_array[1] = '<input type="text" name="code" tabindex="14" size="30"/>'.LF;
           $formal_data[] = $temp_array;
        } else {
           $html .= $this->_translator->getMessage('ACCOUNT_GET_4_TEXT');
           $temp_array[0] = $this->_translator->getMessage('ACCOUNT_PROCESS_ROOM_REASON').': ';
           $value = '';
           if (!empty($get_params['description_user'])) {
              $value = $get_params['description_user'];
              $value = str_replace('%20',' ',$value);
           }
           $temp_array[1] = '<textarea name="description_user" cols="31" rows="10" tabindex="14">'.$value.'</textarea>'.LF;
           $formal_data[] = $temp_array;
        }

        $temp_array = array();
        $temp_array[0] = '&nbsp;';
        $temp_array[1] = '<input type="submit" name="option" tabindex="15" value="'.$this->_translator->getMessage('ACCOUNT_GET_MEMBERSHIP_BUTTON').'"/>'.
                         '&nbsp;&nbsp;'.'<input type="submit" name="option" tabindex="16" value="'.$this->_translator->getMessage('COMMON_BACK_BUTTON').'"/>'.LF;
        $formal_data[] = $temp_array;
        if ( !empty($formal_data) ) {
           $html .= $this->_getFormalDataAsHTML2($formal_data);
           $html .= BRLF;
        }
        unset($params);

        // Normale Raumanmeldung trotz Passwort
/*
        if ($item->checkNewMembersWithCode()) {
           $formal_data = array();
           $temp_array = array();
           $title = $this->_translator->getMessage('ACCOUNT_GET_CODE_TEXT_2');
           $html .= '<div>';
           $html .='<img id="toggle'.$toggle_id.'" src="images/more.gif"/>';
           $html .= '&nbsp;'.$title.'';
           $html .= '</div>';
           $html .= '<div id="creator_information'.$toggle_id.'">'.LF;
           $html .= $this->_translator->getMessage('ACCOUNT_GET_4_TEXT');
           $html .='<script type="text/javascript">initTextFormatingInformation("'.$toggle_id.'",false);</script>';
           $temp_array[0] = $this->_translator->getMessage('ACCOUNT_PROCESS_ROOM_REASON').': ';
           $value = '';
           if (!empty($get_params['description_user'])) {
              $value = $get_params['description_user'];
              $value = str_replace('%20',' ',$value);
           }
           $temp_array[1] = '<textarea name="description_user" cols="31" rows="10" tabindex="14">'.$value.'</textarea>'.LF;
           $formal_data[] = $temp_array;
           if ( !empty($formal_data) ) {
              $html .= $this->_getFormalDataAsHTML2($formal_data);
              $html .= BRLF;
           }
           unset($formal_data);
           $html .= '</div>'.LF;
        }
*/
        $html .= '</form>'.LF;
        $html .= '</div>'.LF;
     }

     // person is guest und will Mitglied werden
     elseif ($mode=='member' and $current_user->isGuest()) {
        $translator = $this->_environment->getTranslationObject();
        $html .= '<div>'.LF;
        $params = $this->_environment->getCurrentParameterArray();
        $params['cs_modus'] = 'portalmember';
        $link = ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,$this->_translator->getMessage('ACCOUNT_GET_GUEST_CHOICE_LINK'));
        $html .= $this->_translator->getMessage('ACCOUNT_GET_GUEST_CHOICE',$link);
        $html .= '</div>'.LF;
     }
     elseif ( $mode=='email') {
        $translator = $this->_environment->getTranslationObject();
        $html .= '<div>'.LF;
        $formal_data = array();

        $get_params = $this->_environment->getCurrentParameterArray();
        if (isset($get_params['sort'])){
           $params['sort'] = $get_params['sort'];
        }elseif (isset($_POST['sort'])){
           $params['sort'] = $get_params['sort'];
        }
        if (isset($get_params['search'])){
           $params['search'] = $get_params['search'];
        }elseif (isset($_POST['search'])){
           $params['search'] = $get_params['search'];
        }
        if (isset($get_params['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }elseif (isset($_POST['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }
        if (isset($get_params['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }elseif (isset($_POST['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }
        if (isset($get_params['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }elseif (isset($_POST['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }
        $params['room_id'] = $item->getItemID();
        $html.= $this->_translator->getMessage('EMAIL_CONTACT_MODERATOR_TEXT');
        $html .= '<form method="post" action="'.curl($this->_environment->getCurrentContextID(),'home','index',$params).'" name="member">'.LF;
        if (isset($get_params['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$get_params['sort'].'"/>'.LF;
        }elseif (isset($_POST['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['sort'].'"/>'.LF;
        }
        if (isset($get_params['search'])){
           $html .= '   <input type="hidden" name="search" value="'.$get_params['search'].'"/>'.LF;
        }elseif (isset($_POST['search'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['search'].'"/>'.LF;
        }
        if (isset($get_params['seltime'])){
           $html .= '   <input type="hidden" name="seltime" value="'.$get_params['seltime'].'"/>'.LF;
        }elseif (isset($_POST['seltime'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['seltime'].'"/>'.LF;
        }
        if (isset($get_params['selroom'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['selroom'].'"/>'.LF;
        }elseif (isset($_POST['selroom'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['selroom'].'"/>'.LF;
        }
        if (isset($get_params['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }elseif (isset($_POST['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }
        $temp_array[0] = $this->_translator->getMessage('EMAIL_CONTACT_MODERATOR_TEXT_DESC').': ';
        $temp_array[1]= '<textarea name="description_user" cols="31" rows="10" wrap="virtual" tabindex="14" ></textarea>'.LF;
        $formal_data[] = $temp_array;
        $temp_array = array();
        $temp_array[0] = '&nbsp;';
        $temp_array[1]= '<input type="submit" name="option"  value="'.$this->_translator->getMessage('CONTACT_MAIL_SEND_BUTTON').'"/>'.
                      '&nbsp;&nbsp;'.'<input type="submit" name="option" value="'.$this->_translator->getMessage('COMMON_BACK_BUTTON').'"/>'.LF;
        $formal_data[] = $temp_array;
        if ( !empty($formal_data) ) {
           $html .= $this->_getFormalDataAsHTML2($formal_data);
           $html .= BRLF;
        }
        unset($params);
        $html .= '</form>'.LF;
        $html .= '</div>'.LF;
     }
      // Person ist User und hat sich angemeldet; wurde aber nicht automatisch freigschaltet
     elseif ($mode =='info') {
        $translator = $this->_environment->getTranslationObject();
        $html .= '<div>'.LF;
        $formal_data = array();
        $get_params = $this->_environment->getCurrentParameterArray();
        if (isset($get_params['sort'])){
           $params['sort'] = $get_params['sort'];
        }elseif (isset($_POST['sort'])){
           $params['sort'] = $get_params['sort'];
        }
        if (isset($get_params['search'])){
           $params['search'] = $get_params['search'];
        }elseif (isset($_POST['search'])){
           $params['search'] = $get_params['search'];
        }
        if (isset($get_params['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }elseif (isset($_POST['seltime'])){
           $params['seltime'] = $get_params['seltime'];
        }
        if (isset($get_params['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }elseif (isset($_POST['selroom'])){
           $params['selroom'] = $get_params['selroom'];
        }
        if (isset($get_params['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }elseif (isset($_POST['sel_archive_room'])){
           $params['sel_archive_room'] = $get_params['sel_archive_room'];
        }
        $params['room_id'] = $item->getItemID();
        $html .= '<form method="post" action="'.curl($this->_environment->getCurrentContextID(),'home','index',$params).'" name="member">'.LF;
        if (isset($get_params['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$get_params['sort'].'"/>'.LF;
        }elseif (isset($_POST['sort'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['sort'].'"/>'.LF;
        }
        if (isset($get_params['search'])){
           $html .= '   <input type="hidden" name="search" value="'.$get_params['search'].'"/>'.LF;
        }elseif (isset($_POST['search'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['search'].'"/>'.LF;
        }
        if (isset($get_params['seltime'])){
           $html .= '   <input type="hidden" name="seltime" value="'.$get_params['seltime'].'"/>'.LF;
        }elseif (isset($_POST['seltime'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['seltime'].'"/>'.LF;
        }
        if (isset($get_params['selroom'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['selroom'].'"/>'.LF;
        }elseif (isset($_POST['selroom'])){
           $html .= '   <input type="hidden" name="sort" value="'.$_POST['selroom'].'"/>'.LF;
        }
        if (isset($get_params['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }elseif (isset($_POST['sel_archive_room'])){
           $html .= '   <input type="hidden" name="selroom" value="'.$get_params['sel_archive_room'].'"/>'.LF;
        }
        $temp_array = array();
        $temp_array[0] = $this->_translator->getMessage('ACCOUNT_PROCESS_CONFIRMATION').': ';
        $temp_array[1]= $this->_translator->getMessage('ACCOUNT_GET_6_TEXT_2',$item->getTitle());
        $formal_data[] = $temp_array;
        $temp_array = array();
        $temp_array[0] = '&nbsp;';
        $temp_array[1]= '<input type="submit" name="option"  value="'.$this->_translator->getMessage('Weiter').'"/>'.LF;
        $formal_data[] = $temp_array;
        if ( !empty($formal_data) ) {
           $html .= $this->_getFormalDataAsHTML2($formal_data);
           $html .= BRLF;
        }
        unset($params);
        $html .= '</form>'.LF;
        $html .= '</div>'.LF;
     }

     return $html;
   }

   function _getFormalDataAsHTML2($data, $spacecount=0, $clear=false) {
      $prefix = str_repeat(' ', $spacecount);
      $html  = $prefix.'<table class="detail" style="width: 100%;" summary="Layout" ';
      if ( $clear ) {
         $html .= 'style="clear:both"';
      }
      $html .= '>'."\n";
      foreach ($data as $value) {
         if ( !empty($value[0]) ) {
            $html .= $prefix.'   <tr>'.LF;
            $html .= $prefix.'      <td style="padding: 10px 2px 10px 0px; color: #666; vertical-align: top; width: 1%;">'.LF;
            $html .= $prefix.'         '.$value[0].'&nbsp;'.LF;
         } else {
            $html .= $prefix.'         &nbsp;';
         }
         $html .= $prefix.'      </td><td style="margin: 0px; padding: 10px 2px 10px 0px;">'.LF;
         if ( !empty($value[1]) ) {
            $html .= $prefix.'         '.$value[1].LF;
         }
         $html .= $prefix.'      </td>'.LF;
         $html .= $prefix.'   </tr>'.LF;
      }
      $html .= $prefix.'</table>'.LF;
      return $html;
   }

   function _getRoomFormAsHTML($item){
     $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<div class="welcome_frame" style="width:100%;">'.LF;
      $html .= '<div class="content_without_fader" style="padding:0px 0px 5px 0px;">'.LF;
      $html .= '<div style="margin:0px;width:100%; font-weight:normal; font-size:10pt;">'.LF;
      $html .= '<div style="padding-left:5px; padding-right:5px;">'.LF;
      if (isset($this->_warning)) {
         $html .= $this->_warning->asHTML();
      }
      if ( isset($this->_form_view) and !empty($this->_form_view) ) {
         $html .= $this->_form_view->asHTML();
      }
      $html .= '</div>'.LF;

      $html .= '</div>'.LF;

      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      return $html;
   }


   function _getLanguageIndexAsHTML () {
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<div class="welcome_frame" style="width:100%;">'.LF;
      $html .= '<div class="content_without_fader">';
      $html .= '<div style="margin:0px;width:100%; font-weight:normal;">'.LF;
      $html .= '<div style="padding-left:5px; padding-right:5px;">'.LF;

      if ( $this->_environment->getCurrentFunction() == 'index'
           and isset($this->_configuration_list_view)
           and !empty($this->_configuration_list_view)
         ) {
         $html .= $this->_configuration_list_view->asHTML();
      } elseif ( isset($this->_form_view) and !empty($this->_form_view) ) {
         $html .= '<div>'.LF;
         $html .= $this->_form_view->asHTML();
         $html .= '</div>'.LF;
      }

      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      $html .= '<div style="position:absolute; top:-4px; left:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_links.gif" alt="" border="0"/></div>'.LF;
      $html .= '<div style="position:absolute; top:-4px; right:-5px;"><img src="'.$this->_style_image_path.'ecke_oben_rechts.gif" alt="" border="0"/></div>'.LF;

      $html .= '</div>'.LF;
      $html .= '<div class="frame_bottom">'.LF;
      $html .= '<div class="content_bottom">'.LF;
      $html .= '<div style="position:absolute; top:-11px; left:-7px;"><img src="'.$this->_style_image_path.'ecke_unten_links.gif" alt=""/></div>'.LF;
      $html .= '<div style="position:absolute; top:-11px; right:-7px;"><img src="'.$this->_style_image_path.'ecke_unten_rechts.gif" alt=""/></div>'.LF;
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      return $html;
   }


   function getDeleteBoxAsHTML ($type='room') {
      $session = $this->_environment->getSession();
      $left_menue_status = $session->getValue('left_menue_status');
      $left = '0em';
      $width = '50em';
      $html  = '<div style="position: absolute; z-index:100;  top:-3px; left:-3px; width:'.$width.'; height: 300px;">'.LF;
      $html .= '<center>';
      $html .= '<div style="position:fixed; z-index:100; margin-top:0px; margin-left:50px; width:400px; padding:20px; background-color:#FFF; border:2px solid red;">';
      $html .= '<form style="margin-bottom:0px; padding:0px;" method="post" action="'.$this->_delete_box_action_url.'">';
      if ( $type == 'portal' ) {
         $html .= '<h2>'.$this->_translator->getMessage('COMMON_DELETE_BOX_TITLE_PORTAL');
      } else {
         $html .= '<h2>'.$this->_translator->getMessage('COMMON_DELETE_BOX_TITLE_ROOM');
      }
      $html .= '</h2>';
      if ( $type == 'portal' ) {
         $html .= '<p style="text-align:left; font-weight:normal;">'.$this->_translator->getMessage('COMMON_DELETE_BOX_DESCRIPTION_PORTAL');
      } else {
         $html .= '<p style="text-align:left; font-weight:normal;">'.$this->_translator->getMessage('COMMON_DELETE_BOX_DESCRIPTION_ROOM');
      }
      $html .= '</p>';
      $html .= '<div style="height:20px;">';
      $html .= '<input style="float:right;" type="submit" name="delete_option" value="'.$this->_translator->getMessage('COMMON_DELETE_BUTTON').'" tabindex="2"/>';
      $html .= '<input style="float:left;" type="submit" name="delete_option" value="'.$this->_translator->getMessage('COMMON_CANCEL_BUTTON').'" tabindex="2"/>';
      if ( $type != 'portal' ) {
         $html .= '<input style="float:left;" type="submit" name="delete_option" value="'.$this->_translator->getMessage('ROOM_ARCHIV_BUTTON').'" tabindex="2"/>';
      }
      $html .= '</div>';
      $html .= '</form>';
      $html .= '</div>';
      $html .= '</center>';
      $html .= '</div>';
      $html .= '<div id="delete" style="position: absolute; z-index:90; top:-3px; left:-3px; width:'.$width.'; height: 350px; background-color:#FFF; opacity:0.7; filter:Alpha(opacity=70);">'.LF;
      $html .= '</div>';
      return $html;
   }

   function addDeleteBox($url,$mode='detail',$selected_ids = NULL){
      $this->_with_delete_box = true;
      $this->_delete_box_action_url = $url;
      $this->_delete_box_mode = $mode;
      $this->_delete_box_ids = $selected_ids;
   }

   function getRoomItemAsHTML($item) {
      $html  = '';
      $html .= LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<div id="room_detail">'.LF;

      // actions
      $html .= '<div>'.LF;
      $html .= '<div id="room_detail_actions">'.LF;
      $current_context = $this->_environment->getCurrentContextItem();
      $current_user = $this->_environment->getCurrentUser();
      if ( !$item->isDeleted() and !$item->isPrivateRoom() and !$item->isGroupRoom() ) {
         $params = array();
         $params['iid'] = $item->getItemID();
         if ( ($current_user->isModerator() or $item->mayEdit($current_user)) and $this->_with_modifying_actions) {
            $params = array();
            $params['iid'] = $item->getItemID();
            $html .=  ahref_curl($this->_environment->getCurrentContextID(),'configuration','common',$params,$this->_translator->getMessage('PORTAL_EDIT_ROOM'),'','','','','','','class="room_detail_link"').''.LF;
            unset($params);
            $params = $this->_environment->getCurrentParameterArray();
            $params['iid'] = $item->getItemID();
            $params['room_id'] = $item->getItemID();
            $params['action'] = 'delete';
            $html .= ' | '. ahref_curl( $this->_environment->getCurrentContextID(),
                                          $this->_environment->getCurrentModule(),
                                          'index',
                                          $params,
                                          $this->_translator->getMessage('COMMON_DELETE_ROOM'),
                                          '','','','','','','class="room_detail_link"').''.LF;
            unset($params);
         } else {
#           $html .=  ' | <span class="room_detail_disabled"> '.$this->_translator->getMessage('PORTAL_EDIT_ROOM').'</span> '.LF;
#           $html .=  ' | <span class="room_detail_disabled"> '.$this->_translator->getMessage('COMMON_DELETE_ROOM').'</span> '.LF;
         }
         $html .= LF;

         if ( $current_user->isModerator()
              and $this->_with_modifying_actions
              and !$item->isLocked()
            ) {
            $params['iid'] = $item->getItemID();
            $params['automatic'] = 'lock';
            $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','room',$params,$this->_translator->getMessage('CONTEXT_ROOM_LOCK'),'','','','','','','class="room_detail_link"').' '.LF;
            unset($params);
         } elseif ( $current_user->isModerator()
                    and $this->_with_modifying_actions
                    and $item->isLocked()
                  ) {
            $params = array();
            $params['automatic'] = 'unlock';
            $params['iid'] = $item->getItemID();
            $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','room',$params,$this->_translator->getMessage('CONTEXT_ROOM_UNLOCK'),'','','','','','','class="room_detail_link"').' '.LF;
            unset($params);
         }
         if ( $current_user->isModerator()
              and $this->_with_modifying_actions
              and !$item->isClosed()
            ) {
            $params = array();
            $params['iid'] = $item->getItemID();
            $params['automatic'] = 'archive';
            $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','room',$params,$this->_translator->getMessage('CONTEXT_ROOM_ARCHIVE'),'','','','','','','class="room_detail_link"').''.LF;
            unset($params);
         }elseif( $current_user->isModerator()
              and $this->_with_modifying_actions
              and $item->isClosed()
            ) {
            $params = array();
            $params['iid'] = $item->getItemID();
            $params['automatic'] = 'open';
            $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','room',$params,$this->_translator->getMessage('CONTEXT_ROOM_OPEN'),'','','','','','','class="room_detail_link"').''.LF;
            unset($params);
         }
         $server_item = $this->_environment->getServerItem();
         $portal_list = $server_item->getPortalList();
         if ( $portal_list->getCount() > 1 and !$item->isGroupRoom() ) {
            if ( $current_user->isModerator()
                 and $this->_with_modifying_actions
                 and !$item->isLockedForMove() ) {
               $params = array();
               $params['iid'] = $item->getItemID();
               $html .= ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','move',$params,$this->_translator->getMessage('PORTAL_MOVE_ROOM'),'','','','','','','class="room_detail_link"').''.LF;
               unset($params);
            } elseif ( $current_user->isModerator()
                       and $this->_with_modifying_actions
                       and $item->isLockedForMove() ) {
               $html .= ' | <span class="room_detail_disabled"> '.$this->_translator->getMessage('PORTAL_MOVE_ROOM').'</span> '.LF;
            }
         }

         if ( $current_user->isRoot()
              and $this->_with_modifying_actions
            ) {
            $params = array();
            $params['iid'] = $item->getItemID();
            $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','export',$params,$this->_translator->getMessage('PORTAL_EXPORT_ROOM'),'','','','','','','class="room_detail_link"').''.LF;
            unset($params);
         }
      } elseif ( $current_user->isRoot() ) {
         $params = array();
         $params['iid'] = $item->getItemID();
         $params['automatic'] = 'undelete';
         $html .=  ' | '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','room',$params,$this->_translator->getMessage('CONTEXT_ROOM_UNDELETE'),'','','','','','','class="room_detail_link"').''.LF;
         unset($params);
      }
      // end actions

      $html .= '</div>'.LF;
      $html .= '</div>'.LF;


      $html .= '<div id="room_detail_headline">'.LF;
      $html .= $this->_getRoomHeaderAsHTML($item);
      $html .= '</div>'.LF;

      $html .= '<div id="room_detail_content">'.LF;
      $mode = '';
      if (isset($_GET['account'])){
         $mode = $_GET['account'];
      }
      if (empty($mode)){
         $html .= '<table style="border-collapse:collapse; border:0px solid black;" summary="Layout">'.LF;
         $html .= '<tr>'.LF;
         $html .= '<td style="width:1%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<img src="'.$this->_style_image_path.'portal_key.gif" alt="" border="0"/>';
         $html .= '</td>'.LF;
         $html .= '<td style="width:30%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<span class="search_title">'.$this->_translator->getMessage('COMMON_ACCESS_POINT').':'.'</span>';
         $html .= '</td>'.LF;
/*
         $html .= '<td style="width:1%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<img src="'.$this->_style_image_path.'portal_info.gif" alt="" border="0"/>'.LF;
         $html .= '</td>'.LF;
         $html .= '<td style="width:45%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<span class="search_title">'.$this->_translator->getMessage('COMMON_DESCRIPTION').':'.'</span>';
         $html .= '</td>'.LF;
*/
         $html .= '<td style="width:1%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<img src="'.$this->_style_image_path.'portal_info2.gif" alt="" border="0"/>'.LF;
         $html .= '</td>'.LF;
         $html .= '<td style="width:25%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
         $html .= '<span class="search_title">'.$this->_translator->getMessage('COMMON_FACTS').':'.'</span>';
         $html .= '</td>'.LF;

         $html .= '</tr>'.LF;
         $html .= '<tr>'.LF;
         $html .= '<td colspan="2" style="vertical-align:top; font-weight:normal;">'.LF;
         $html .= $this->_getRoomAccessAsHTML($item);
         $html .= '</td>'.LF;
/*
         $html .= '<td colspan="2" style="font-weight:normal; font-size:8pt; vertical-align:top; text-align:left;">'.LF;
         $desc = $item->getDescription();
         if (!empty($desc)){
            $html .= $this->_text_as_html_long($item->getDescription());
         }else{
            $html .= '<span class="disabled">'.$this->_translator->getMessage('COMMON_NO_DESCRIPTION').'</span>'.LF;
         }
         $html .= '</td>'.LF;
*/

         $html .= '<td colspan="2" style="vertical-align:top; text-align:left;">'.LF;
         $html .= $this->_getRoomFacts($item);
         $html .= '</td>'.LF;
         $html .= '</tr>'.LF;

        $html .= '<tr>'.LF;
        $html .= '<td style="width:1%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
        $html .= '<img src="'.$this->_style_image_path.'portal_info.gif" alt="" border="0"/>'.LF;
        $html .= '</td>'.LF;
        $html .= '<td colspan="3" style="width:99%; vertical-align:middle; padding:0px; margin:0px;">'.LF;
        $html .= '<span class="search_title">'.getMessage('COMMON_DESCRIPTION').':'.'</span>';
        $html .= '</td>'.LF;
        $html .= '</tr>'.LF;
        $html .= '<tr>'.LF;
        $html .= '<td colspan="4" style="font-weight:normal; font-size:8pt; vertical-align:top; text-align:left;">'.LF;
        $desc = $item->getDescription();
        if (!empty($desc)){
           $html .= $this->_text_as_html_long($item->getDescription());
        }else{
           $html .= '<span class="disabled">'.getMessage('COMMON_NO_DESCRIPTION').'</span>'.LF;
        }
        $html .= '</td>'.LF;
        $html .= '</tr>'.LF;

         $html .= '</table>'.LF;
      } else{
         $html .= '<table style="border-collapse:collapse; border:0px solid black;" summary="Layout">'.LF;
         $html .= '<tr>'.LF;
         $html .= '<td colspan="4" rowspan="2" style="width:71%; vertical-align:top; font-weight:normal;">'.LF;
         $html .= $this->_getRoomForm($item, $mode);
         $html .= '</td>'.LF;
         $html .= '</tr>'.LF;
         $html .= '</table>'.LF;
      }
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      if ($this->_with_delete_box) {
         $html .= $this->getDeleteBoxAsHTML();
      }
      return $html;
   }

   /** get the header as HTML
    * this method returns the commsy header as HTML - internal, do not use
    *
    * @return string header as HTML
    */
   function _getRoomHeaderAsHTML($item) {
      $html  = LF.'<!-- BEGIN HEADER -->'.LF;
      // title
      $html .='<table style=" width:100%; padding:0px; margin:0px;" summary="Layout">';
      $html .='<tr>';
      $html .='<td style="width: 1%; vertical-align:bottom;">';
      $logo_filename = $item->getLogoFilename();
      $current_user = $this->_environment->getCurrentUserItem();
      if ( !empty($logo_filename) ) {
         $params = array();
         $params['picture'] = $item->getLogoFilename();
         $curl = curl($item->getItemID(), 'picture', 'getfile', $params,'');
         unset($params);
         $html .= '      <img class="logo" style="height:48px; padding-right:10px; " src="'.$curl.'" alt="'.$this->_translator->getMessage('LOGO').'" border="0"/>';
      }
      $html .= '</td>';
      // logo
      $html .=       '<td style="width: 99%; vertical-align:middle; padding-top:12px; padding-bottom:13px; padding-right:0px; text-align:left;">';
      $html .= '      <span style="padding-bottom:0px; font-size: 20px; font-weight: bold;">';
      if ( !$item->isPrivateRoom() ) {
         $html .= $this->_text_as_html_short($item->getTitle());
      } else {
         $owner = $item->getOwnerUserItem();
         if ( !empty($owner) ) {
            $html .= $this->_text_as_html_short($this->_translator->getMessage('PRIVATE_ROOM_TITLE').' '.$owner->getFullname());
         }
         unset($owner);
      }
      $html .= '</span>'.LF;
      if ($item->isDeleted()) {
         $html .= '      <span style="padding-bottom:0px; font-size: 20px; font-weight: normal;">';
         $html .= ' ('.$this->_translator->getMessage('ROOM_STATUS_DELETED').')';
         $html .= '</span>'.LF;
      } elseif ($item->isLocked()) {
         $html .= '      <span style="padding-bottom:0px; font-size: 20px; font-weight: normal;">';
         $html .= ' ('.$this->_translator->getMessage('PROJECTROOM_LOCKED').')'.LF;
         $html .= '</span>'.LF;
      } elseif ($item->isProjectroom() and $item->isTemplate()) {
         $html .= '      <span style="padding-bottom:0px; font-size: 20px; font-weight: normal;">';
         $html .= ' ('.$this->_translator->getMessage('PROJECTROOM_TEMPLATE').')'.LF;
         $html .= '</span>'.LF;
      } elseif ($item->isClosed()) {
         $html .= '      <span style="padding-bottom:0px; font-size: 20px; font-weight: normal;">';
         $html .= ' ('.$this->_translator->getMessage('PROJECTROOM_CLOSED').')'.LF;
         $html .= '</span>'.LF;
      }
      $html .='</td>';
      $html .='</tr>';
      $html .='</table>';
      $html .= '<!-- END HEADER -->'.LF;
      return $html;
   }
















   function _getHTMLHeadAsHTML () {
      global $c_commsy_url_path;
      $module   = $this->_environment->getCurrentModule();
      $function = $this->_environment->getCurrentFunction();
      $url_addon = '';
      if ( isset($_GET['mode']) and $_GET['mode']=='print' ) {
         $this->_is_print_page = true;
      }
      $session = $this->_environment->getSession();
      $left_menue_status = $session->getValue('left_menue_status');
      $retour  = '';
#      $retour .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.LF;
#      $retour .= '<html xmlns="http://www.w3.org/1999/xhtml">'.LF;
      $retour .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'.LF;
      $retour .= '<html>'.LF;
      $retour .= '<head>'.LF;
      $retour .= '   <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'.LF;
      $retour .= '   <meta http-equiv="expires" content="-1"/>'.LF;
      $retour .= '   <meta http-equiv="cache-control" content="no-cache"/>'.LF;
      $retour .= '   <meta http-equiv="pragma" content="no-cache"/>'.LF;
      $retour .= '   <meta name="MSSmartTagsPreventParsing" content="TRUE"/>'.LF;
      $retour .= '   <meta name="CommsyBaseURL" content="'.$c_commsy_url_path.'"/>'.LF;
      $current_browser = strtolower($this->_environment->getCurrentBrowser());
      $current_browser_version = $this->_environment->getCurrentBrowserVersion();
      if ( !($current_browser == 'msie' and strstr($current_browser_version,'5.')) ){
         $retour .= $this->_getIncludedCSSAsHTML();
         $retour .= $this->_includedJavascriptAsHTML();
      }else{
         $retour .= $this->_getIncludedCSSIE5AsHTML();
         $retour .= $this->_includedJavascriptIE5AsHTML();
      }

      $current_context_item = $this->_environment->getCurrentContextItem();
      $current_user_item = $this->_environment->getCurrentUserItem();
      $show_rss_link = false;
      if ( $current_context_item->isLocked()
           or $current_context_item->isServer()
           or $current_context_item->isPortal()
         ) {
         // do nothing
      } elseif ( $current_context_item->isOpenForGuests() ) {
         $show_rss_link =  true;
      } elseif ( $current_user_item->isUser() ) {
         $show_rss_link =  true;
      }
      $hash_string = '';
      if ( !$current_context_item->isOpenForGuests()
           and $current_user_item->isUser()
         ) {
         $hash_manager = $this->_environment->getHashManager();
         $hash_string = '&amp;hid='.$hash_manager->getRSSHashForUser($current_user_item->getItemID());
      }
      if ( $show_rss_link ) {
         $retour .= '   <link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php?cid='.$current_context_item->getItemID().$hash_string.'" />'.LF;
      }
      unset($current_user_item);
      unset($current_context_item);

      $between = '';
      if ( !empty($this->_name_room) and !empty($this->_name_page)) {
         $between .= ' - ';
      }
      $retour .= '   <title>'.$this->_text_as_html_short($this->_name_room).$between.$this->_text_as_html_short($this->_name_page).'</title>'.LF;
      if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
         $views = array_merge($this->_views, $this->_views_left, $this->_views_right, $this->_views_overlay);
         if ( isset($this->_form_view) ) {
            $views[] = $this->_form_view;
         }
         $view = reset($views);
         while ($view) {
            $retour .= $view->getInfoForHeaderAsHTML();
            $view = next($views);
         }
         unset($views);
         unset($view);
         $session = $this->_environment->getSession();
         $left_menue_status = $session->getValue('left_menue_status');
         if ( $left_menue_status != 'disapear'
              and $this->_environment->getCurrentModule() != 'help'
              and !$this->_environment->inServer()
            ) {

            //Set Focus to login field
            $retour .= '   <script type="text/javascript">'.LF;
            $retour .= '      <!--'.LF;
            $retour .= '         function setfocus() {';
            $retour .= 'document.login.user_id.focus(); ';
            $retour .= '}'.LF;
            $retour .= '      -->'.LF;
            $retour .= '   </script>'.LF;
            $this->_focus_onload = true;
         }
      } else {
         $views = array_merge($this->_views, $this->_views_left, $this->_views_right, $this->_views_overlay);
         if ( isset($this->_form_view) ) {
            $views[] = $this->_form_view;
         }
         $view = reset($views);
         while ($view) {
            $retour .= $view->getInfoForHeaderAsHTML();
            $view = next($views);
         }
         unset($views);
         unset($view);
      }
      $retour .= '</head>'.LF;
      return $retour;
   }



   function _getIncludedCSSAsHTML(){
      global $c_commsy_url_path;
      $module   = $this->_environment->getCurrentModule();
      $current_user   = $this->_environment->getCurrentUserItem();
      $function = $this->_environment->getCurrentFunction();
      $url_addon = '';
      if ( isset($_GET['mode']) and $_GET['mode']=='print' ) {
         $this->_is_print_page = true;
      }
      $session = $this->_environment->getSession();
      $left_menue_status = $session->getValue('left_menue_status');
      $retour  = '';
      $show_agb_again = false;
      if ( $current_user->isUser() and !$current_user->isRoot() ) {
         $current_context = $this->_environment->getCurrentContextItem();
         if ( $current_context->withAGB() ) {
            $user_agb_date = $current_user->getAGBAcceptanceDate();
            $context_agb_date = $current_context->getAGBChangeDate();
            if ($user_agb_date < $context_agb_date) {
               $show_agb_again = true;
            }
         }
      }

            
      
/*      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/main/base.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/modifications/basemod.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/modifications/commsy.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/navigation/nav_slidingdoorII.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/navigation/nav_vlist.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/main/content.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/navigation/print/print_003.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<!--[if lte IE 7]>'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/explorer/iehacks_3col_vlines.css" rel="stylesheet" type="text/css" /> <![endif]-->'.LF;
      $retour .= '<!--[if lte IE 6]>'.LF;
      $retour .= '<script type="text/javascript" src="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/hilfsmittel/javascript/minmax.js"></script> <![endif]-->'.LF;
      $retour .= '<link rel="shortcut icon" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/favicon.ico" />'.LF;
*/

      
      $retour .= $this->_includeDojoAsHTML();
      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="templates/themes/default/styles.css"/>'.LF;
      
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/reset.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/960_24_col.css" rel="stylesheet" type="text/css" />'.LF;
      $retour .= '<link href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/style.css" rel="stylesheet" type="text/css" />'.LF;
      
      
      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/commsy_external_portal_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;
      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/commsy_form_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;
      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/commsy_right_boxes_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;
#      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/commsy_room_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;
#      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/commsy_portal_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;
      $retour .= '   <link rel="stylesheet" media="screen" type="text/css" href="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/css/commsy_profile_css.php?cid='.$this->_environment->getCurrentContextID().$url_addon.'"/>'.LF;

      
      return $retour;
   }

   private function _includeDojoAsHTML() {
   	$html = "";
   
   	$current_user = $this->_environment->getCurrentUser();
   	$ownRoomItem = $current_user->getOwnRoom();
   	$templateEngine = $this->_environment->getTemplateEngine();
   	$translator = $this->_environment->getTranslationObject();
   
   	if($templateEngine->getTheme() !== 'default') {
   		$tpl_path = substr($templateEngine->getTemplateDir(1), 7);
   	} else {
   		$tpl_path = substr($templateEngine->getTemplateDir(0), 7);
   	}
   
   
   
   	global $c_js_mode;
   	$mode = (isset($c_js_mode) && ($c_js_mode === "build" || $c_js_mode === "layer")) ? $c_js_mode : "source";
   
   	$to_javascript = array();
   
   	$to_javascript['template']['tpl_path'] = $tpl_path;
   	$to_javascript['environment']['lang'] = $this->_environment->getSelectedLanguage();
   	$to_javascript['environment']['single_entry_point'] = $this->_environment->getConfiguration('c_single_entry_point');
   	$to_javascript['environment']['max_upload_size'] = $this->_environment->getCurrentContextItem()->getMaxUploadSizeInBytes();
    $to_javascript['environment']['isPortal'] = $this->_environment->getCurrentContextItem()->isPortal();
    
   	$to_javascript['i18n']['COMMON_NEW_BLOCK'] = $translator->getMessage('COMMON_NEW_BLOCK');
   	$to_javascript['i18n']['COMMON_SAVE_BUTTON'] = $translator->getMessage('COMMON_SAVE_BUTTON');
   	$to_javascript['security']['token'] = getToken();
   	$to_javascript['autosave']['mode'] = 0;
   	$to_javascript['autosave']['limit'] = 0;
   
   	if ($ownRoomItem) {
   		$to_javascript['ownRoom']['id'] = $ownRoomItem->getItemId();
   		$to_javascript['own']['id'] = $ownRoomItem->getItemId();
   		$to_javascript['ownRoom']['withPortfolio'] = $ownRoomItem->getCSBarShowPortfolio();
   	}
   
   	// translations - should be managed elsewhere soon
   	$to_javascript["translations"]["common_hide"] = $translator->getMessage("COMMON_HIDE");
   	$to_javascript["translations"]["common_show"] = $translator->getMessage("COMMON_SHOW");
   	
   	$portal_item = $this->_environment->getCurrentPortalItem();
   	$current_portal_user = $this->_environment->getPortalUserItem();
   	// password expires soon alert
   	if(!empty($current_portal_user) AND $current_portal_user->getPasswordExpireDate() > getCurrentDateTimeInMySQL()) {
   		$start_date = new DateTime(getCurrentDateTimeInMySQL());
   		$since_start = $start_date->diff(new DateTime($current_portal_user->getPasswordExpireDate()));
   		$days = $since_start->days;
   		if($days == 0){
   			$days = 1;
   		}
   	
   		$days_before_expiring_sendmail = $portal_item->getDaysBeforeExpiringPasswordSendMail();
   		if(isset($days_before_expiring_sendmail) AND $days <= $days_before_expiring_sendmail){
   			$to_javascript["translations"]["password_expire_soon_alert"] = $translator->getMessage("COMMON_PASSWORD_EXPIRE_ALERT", $days);
   			$to_javascript['environment']['password_expire_soon'] = true;
   		} else if(!isset($days_before_expiring_sendmail) AND $days <= 14){
   			$to_javascript["translations"]["password_expire_soon_alert"] = $translator->getMessage("COMMON_PASSWORD_EXPIRE_ALERT", $days);
   			$to_javascript['environment']['password_expire_soon'] = true;
   		}
   	} else {
   		$to_javascript['environment']['password_expire_soon'] = false;
   	}
   	
   	$current_user = $this->_environment->getCurrentUserItem();
   	 
   	$auth_source_manager = $this->_environment->getAuthSourceManager();
   	$auth_source_item = $auth_source_manager->getItem($current_user->getAuthSource());
   	
   	if(isset($auth_source_item)){
   		$show_tooltip = true;
   		// password
   		if($auth_source_item->getPasswordLength() > 0){
   			$to_javascript["password"]["length"] = $translator->getMessage('PASSWORD_INFO2_LENGTH', $auth_source_item->getPasswordLength());
   		} else {
   			$show_tooltip = false;
   		}
   		if($auth_source_item->getPasswordSecureBigchar() == 1){
   			$to_javascript["password"]["big"] = $translator->getMessage('PASSWORD_INFO2_BIG');
   		} else {
   			$show_tooltip = false;
   		}
   		if($auth_source_item->getPasswordSecureSmallchar() == 1){
   			$to_javascript["password"]["small"] = $translator->getMessage('PASSWORD_INFO2_SMALL');
   		} else {
   			$show_tooltip = false;
   		}
   		if($auth_source_item->getPasswordSecureNumber() == 1){
   			$to_javascript["password"]["special"] = $translator->getMessage('PASSWORD_INFO2_SPECIAL');
   		} else {
   			$show_tooltip = false;
   		}
   		if($auth_source_item->getPasswordSecureSpecialchar() == 1){
   			$to_javascript["password"]["number"] = $translator->getMessage('PASSWORD_INFO2_NUMBER');
   		} else {
   			$show_tooltip = false;
   		}
   	} else {
   		$show_tooltip = false;
   	}
   	if($show_tooltip){
   		$to_javascript["password"]["tooltip"] = 1;
   	} else {
   		$to_javascript["password"]["tooltip"] = 0;
   	}
   	
   	// dev
   	global $c_indexed_search;
   	global $c_xhr_error_reporting;
   	$to_javascript['dev']['indexed_search'] = (isset($c_indexed_search) && $c_indexed_search === true) ? true : false;
   	$to_javascript['dev']['xhr_error_reporting'] = (isset($c_xhr_error_reporting) && !empty($c_xhr_error_reporting)) ? true : false;
   
   	if(isset($portal_user) && $portal_user->isAutoSaveOn()) {
   		global $c_autosave_mode;
   		global $c_autosave_limit;
   
   		if(isset($c_autosave_mode) && isset($c_autosave_limit)) {
   			$to_javascript['autosave']['mode'] = $c_autosave_mode;
   			$to_javascript['autosave']['limit'] = $c_autosave_limit;
   		}
   	}
   
      // has to change email (new) at portal
   	if ( isset($this->_has_to_change_email) and $this->_has_to_change_email ) {
   	   $to_javascript['autoOpenPopup']['popup'] = 'tm_user';
   	   $to_javascript['autoOpenPopup']['tab'] = 'user';
   	   $to_javascript['autoOpenPopup']['parameters'] = array();
   	}
   	
   	$html .= '<script src="js/3rdParty/ckeditor_4.4.3/ckeditor.js"></script>';
   	
   	switch ($mode) {
   		case "build":
   			$html .= '<script src="js/src/buildConfig.js"></script>';
   
   			$html .= "
   				<script>
   					var from_php  = '" . json_encode($to_javascript) . "';
   					dojoConfig.locale = '" . $this->_environment->getSelectedLanguage() . "';
   				</script>
   			";
   
   			$html .= '<script src="js/build/release/dojo/dojo.js"></script>';
   			$html .= '<script src="js/build/release/commsy/main.js"></script>';
   
   			break;
   
   		case "layer":
   			$html .= '<script src="js/src/layerConfig.js"></script>';
   
   			$html .= "
   				<script>
   					var from_php  = '" . json_encode($to_javascript) . "';
   					dojoConfig.locale = '" . $this->_environment->getSelectedLanguage() . "';
   				</script>
   			";
   
   			$html .= '<script src="js/src/dojo/dojo.js"></script>';
   			$html .= '
   				<script>
   					require(["layer/commsy"], function() {
				   		require(["commsy/main"], function() {
   
				   		});
				   	});
   				</script>
   			';
   			break;
   			 
   		default:
   			$html .= '<script src="js/src/sourceConfig.js"></script>';
   
   			$html .= "
   				<script>
   					var from_php  = '" . json_encode($to_javascript) . "';
   					dojoConfig.locale = '" . $this->_environment->getSelectedLanguage() . "';
   				</script>
   			";
   
   			$html .= '<script src="js/src/dojo/dojo.js"></script>';
   			$html .= '<script src="js/src/commsy/main.js"></script>';
   	}
   
   	$html .= '
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/dijit/themes/tundra/tundra.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/cbtree/themes/tundra/tundra.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/dojox/form/resources/UploaderFileList.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/dojox/image/resources/Lightbox.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/dojox/widget/ColorPicker/ColorPicker.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="js/src/dojox/calendar/themes/tundra/Calendar.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="templates/themes/default/styles.css" />
   		<link rel="stylesheet" type="text/css" media="screen" href="templates/themes/default/styles_addon.css" />
   	';
   
   	return $html;
   }
    

   function getSearchBoxAsHTML(){
      $html ='';
/*      $html .= '<div >'.LF;*/
      $current_context = $this->_environment->getCurrentContextItem();
      $show_rooms = $current_context->getShowRoomsOnHome();
      $html  = '';
      // Search / select form
      $html .= '<form style="padding:0px;" action="'.curl($this->_environment->getCurrentContextID(), $this->_environment->getCurrentModule(), $this->_environment->getCurrentFunction(),'').'" method="get" name="indexform">'.LF;
      $html .= '   <input type="hidden" name="cid" value="'.$this->_text_as_form($this->_environment->getCurrentContextID()).'"/>'.LF;
      $html .= '   <input type="hidden" name="mod" value="'.$this->_text_as_form($this->_environment->getCurrentModule()).'"/>'.LF;
      $html .= '   <input type="hidden" name="fct" value="'.$this->_text_as_form($this->_environment->getCurrentFunction()).'"/>'.LF;
      if ( isset($this->_room_list_view) ) {
         $html .= '   <input type="hidden" name="sort" value="'.$this->_text_as_form($this->_room_list_view->getSortKey()).'"/>'.LF;
      }
      $session = $this->_environment->getSession();
      if ( !$session->issetValue('cookie')
           or $session->getValue('cookie') == '0' ) {
         $html .= '   <input type="hidden" name="SID" value="'.$this->_text_as_form($session->getSessionID()).'"/>'.LF;
      }
      if ( isset($this->_room_list_view) and !empty($this->_room_list_view->_activity_modus) ) {
         $html .= '   <input type="hidden" name="activitymodus" value="'.$this->_text_as_form($this->_room_list_view->_activity_modus).'"/>'.LF;
      }
      $html .= '<input type="hidden" name="sel_archive_room" value="2"/>'.LF;
      $html .= '<div class="search_box">'.LF;
      $html .= '<div style="text-align:left; font-size: 10pt;">';
      $html .= '<div style="float:right;"><input style="font-size:10pt;" name="option" value="'.$this->_translator->getMessage('COMMON_SHOW_BUTTON').'" type="submit"/></div>'.LF;

      if ( isset($this->_room_list_view) ) {
         $html .= '<div><input style="width:450px; font-size:10pt;" name="search" type="text" size="20" value="'.$this->_text_as_form($this->_room_list_view->getSearchText()).'"/></div>'.LF;
      } else {
         $html .= '<div><input style="width:450px; font-size:10pt;" name="search" type="text" size="20" value=""/></div>'.LF;
      }

      if ( isset($this->_room_list_view) ) {
         $selroom = $this->_room_list_view->getSelectedRoom();
         $sel_archive_room = $this->_room_list_view->getSelectedArchiveRoom();
      } else {
         $selroom = '';
         $sel_archive_room = '';
      }
      if ( $selroom == 5 ) {
         $selroom = 1;
      }
      if ( isset($this->_room_list_view) and !empty($this->_room_list_view->_selected_iid)) {
         $html .= '   <input type="hidden" name="iid" value="'.$this->_text_as_form($this->_room_list_view->_selected_iid).'"/>'.LF;
      }
      if ( isset($this->_room_list_view) and !empty($this->_room_list_view->_selected_context)) {
         $html .= '   <input type="hidden" name="room_id" value="'.$this->_text_as_form($this->_room_list_view->_selected_context).'"/>'.LF;
      }

      #if ($show_rooms !='onlycommunityrooms'){
         $html .= '<div style="float:left; text-align:left; font-size: 10pt; padding-top:5px;">';
      #}else{
      #   $html .= '<div style="text-align:left; font-size: 10pt; padding-bottom:0px; margin-bottom:0px;">'.$this->_translator->getMessage('PORTAL_COMMUNITY_ROOM_LIST_ROOMS').':'.BRLF;
      #}
      $html .= '<div style="text-align:left; font-size: 8pt; font-weight:normal; margin-bottom:10px; margin-top:3px;"> <input type="radio" ';
      if ( !isset($sel_archive_room) || empty($sel_archive_room) || ($sel_archive_room == 2) ) {
		$html .=' checked="checked"';
	 }
	  $html .=' value="2" name="sel_archive_room"> '.LF;
   		$current_user = $this->_environment->getCurrentUserItem();
      $session = $this->_environment->getSessionItem();
	$language_addon = '';
      $lang = $this->_environment->getSelectedLanguage();
	if ($session->issetValue('message_language_select')){
		$lang = $session->getValue('message_language_select');
	}elseif($current_user->isUser()){
		$lang = $current_user->getLanguage();
	}
   	if ($lang == 'en'){
	$html .= 'Usable rooms'.LF;
   					}else{
			$html .= 'Nutzbare Räume'.LF;
				
		}
      $html .= '<input type="radio" ';
	  if ($sel_archive_room == 1) {
		$html .= ' checked="checked"';
	  }
	  $html .=' value="1" name="sel_archive_room"> '.$this->_translator->getMessage('PORTAL_ARCHIVED_ROOMS').'
      </div>'.LF;

      $html .= '   <select style="width: 215px; font-size:10pt;" name="selroom" size="1" onChange="javascript:document.indexform.submit()">'.LF;

      $html .= '      <option value="1"';
      if ( !isset($selroom) || ($selroom == 1 or $selroom == 2) ) {
         $html .= ' selected="selected"';
      }
      $html .= '>*'.$this->_translator->getMessage('COMMON_ALL_ENTRIES').' '.$this->_translator->getMessage('COMMON_ROOMS').'</option>'.LF;

      $current_context = $this->_environment->getCurrentContextItem();
      if ($show_rooms !='onlycommunityrooms'){
         $html .= '      <option class="disabled" disabled="disabled" value="-2">------------------------------</option>'.LF;
         $html .= '      <option value="3"';
         if ( !empty($selroom) and $selroom == 3 ) {
            $html .= ' selected="selected"';
         }
         $html .= '>'.$this->_translator->getMessage('COMMON_PROJECT_PL').'</option>'.LF;

         $html .= '      <option value="4"';
         if ( !empty($selroom) and $selroom == 4 ) {
            $html .= ' selected="selected"';
         }
         $html .= '>'.$this->_translator->getMessage('COMMON_COMMUNITY_PL').'</option>'.LF;
      }

      $html .= '   </select>'.LF;


      $html .= '</div>'.LF;

      if ( (!empty($sel_archive_room) and $sel_archive_room == 1) ) {
         $text = ' checked="checked"';
      } else {
         $text = '';
      }


      $current_context = $this->_environment->getCurrentContextItem();
      if ( $this->_environment->inPortal()
           and $current_context->showTime()
         ) {
         if ( isset($this->_room_list_view) ) {
            $seltime = $this->_room_list_view->getSelectedTime();
         } else {
            $seltime = '';
         }
         $portal_item = $this->_environment->getCurrentContextItem();
         $time_list = $portal_item->getTimeListRev();

         $html .= '<div style="float:right; text-align:left; font-size: 10pt; padding-right:40px;">';
         $html .= '<div style="text-align:left; font-size: 8pt; font-weight:normal; margin-bottom:10px; margin-top:3px;">'.$this->_translator->getMessage('COMMON_TIME_NAME').': </div>'.LF;
         $html .= '   <select style="width: 237px; font-size: 10pt;" name="seltime" size="1" onChange="javascript:document.indexform.submit()">'.LF;
         $html .= '      <option value="-3"';
         if ( !isset($seltime) or $seltime == 0 or $seltime == -3) {
            $html .= ' selected="selected"';
         }
         $time_item = $time_list->getFirst();
         $html .= '>*'.$this->_translator->getMessage('COMMON_ALL_ENTRIES').' '.$this->_translator->getMessage('COMMON_TIME_NAME').'</option>'.LF;
         $html .= '      <option class="disabled" disabled="disabled" value="-2">------------------------------</option>'.LF;
         if ($time_list->isNotEmpty()) {
            $time_item = $time_list->getFirst();
            while ($time_item) {
               $html .= '      <option value="'.$time_item->getItemID().'"';
               if ( !empty($seltime) and $seltime == $time_item->getItemID() ) {
                  $html .= ' selected="selected"';
               }
               $html .= '>'.$this->_translator->getTimeMessage($time_item->getTitle()).'</option>'.LF;
               $time_item = $time_list->getNext();
            }
         }

         $html .= '      <option class="disabled" disabled="disabled" value="-2">------------------------------</option>'.LF;
         $html .= '      <option value="-1"';
         if ( isset($seltime) and $seltime == -1) {
            $html .= ' selected="selected"';
         }
         $html .= '>*'.$this->_translator->getMessage('COMMON_NOT_LINKED').'</option>'.LF;
         $html .= '   </select>'.LF;
         $html .= '</div>'.LF;
      }

      $html .= '<div style="clear:both;">'.LF;
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      $html .= '</form>'.LF;



      /*$html .= '</div>'.LF;*/
      return $html;
   }



   function getConfigurationAsHTML () {
      $html ='';
      if ( $this->_environment->getCurrentFunction() == 'index'
           and isset($this->_configuration_list_view)
           and !empty($this->_configuration_list_view)
         ) {
         $html .= '<div id="portal_config_overview">'.LF;
         $html .= $this->_configuration_list_view->asHTML();
         $html .= '</div>'.LF;
      } elseif ( isset($this->_form_view) and !empty($this->_form_view) ) {
         $html .= '<div id="portal_room_config">'.LF;
         $html .= $this->_form_view->asHTML();
         $html .= '</div>'.LF;
         if ( $this->_with_delete_box ) {
            $html .= $this->getDeleteBoxAsHTML('portal');
         }
      }
      return $html;
   }

   function getPortalFormsAsHTML($cs_mod){
      $current_context = $this->_environment->getCurrentContextItem();
      $current_portal = $this->_environment->getCurrentPortalItem();
      $current_user = $this->_environment->getCurrentUserItem();
      $this->_current_user = $current_user;
      $html = '';
      $html .= '<div id="portal_forms">'.LF;
      if ( $cs_mod == 'portalmember' or $cs_mod == 'portalmember2' ) {
         if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
         } else {
            $params = array();
            $params['iid'] = $this->_current_user->getItemID();
            $fullname = $this->_current_user->getFullname();
        }
        if ( $cs_mod == 'portalmember' ) {
           include_once('classes/cs_home_member_page.php');
           $page = new cs_home_member_page($this->_environment);
        } else {
           include_once('classes/cs_home_member2_page.php');
           $page = new cs_home_member2_page($this->_environment);
        }
        $html .= $page->execute();
        unset($page);
     }


     // change account
     elseif ($cs_mod == 'account_change') {
        if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
        } else {
           $params = array();
           $params['iid'] = $this->_current_user->getItemID();
           if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
              $portal_user = $this->_environment->getPortalUserItem();
              $fullname = $portal_user->getFullname();
              unset($portal_user);
           } else {
              $fullname = $this->_current_user->getFullname();
           }
        }
        $current_portal_item = $this->_environment->getCurrentPortalItem();
        $current_auth_source_item = $current_portal_item->getAuthSource($this->_current_user->getAuthSource());
        unset($current_portal_item);
        if ( $current_auth_source_item->allowChangeUserID() ) {
           include_once('classes/cs_account_change_page.php');
           $page = new cs_account_change_page($this->_environment);
           $html .= $page->execute();
           $html .= BRLF;
        }
        unset($current_auth_source_item);
        include_once('classes/cs_account_merge_page.php');
        $page = new cs_account_merge_page($this->_environment);
        $html .= $page->execute();
        unset($page);
     }

     // forget account
     elseif ($cs_mod == 'account_forget') {
        if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
        } else {
           $params = array();
           $params['iid'] = $this->_current_user->getItemID();
           if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
              $portal_user = $this->_environment->getPortalUserItem();
              $fullname = $portal_user->getFullname();
              unset($portal_user);
           } else {
              $fullname = $this->_current_user->getFullname();
           }
        }
        include_once('classes/cs_account_forget_page.php');
        $page = new cs_account_forget_page($this->_environment);
        $html .= $page->execute();
        unset($page);
     }

     // forget password
     elseif ($cs_mod == 'password_forget') {
        if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
        } else {
           $params = array();
           $params['iid'] = $this->_current_user->getItemID();
           if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
              $portal_user = $this->_environment->getPortalUserItem();
              $fullname = $portal_user->getFullname();
              unset($portal_user);
           } else {
              $fullname = $this->_current_user->getFullname();
           }
        }
        include_once('classes/cs_password_forget_page.php');
        $page = new cs_password_forget_page($this->_environment);
        $html .= $page->execute();
        unset($page);
     }

     // become member
     elseif ( $cs_mod == 'become_member' ) {
        if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
        } else {
           $params = array();
           $params['iid'] = $this->_current_user->getItemID();
           if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
              $portal_user = $this->_environment->getPortalUserItem();
              $fullname = $portal_user->getFullname();
              unset($portal_user);
           } else {
              $fullname = $this->_current_user->getFullname();
           }
       }
       include_once('classes/cs_become_member_page.php');
       $page = new cs_become_member_page($this->_environment);
       $html .= $page->execute();
       unset($page);
     }
     $html .= '</div>'.LF;
     return $html;
  }

   function _getUserPersonalAreaAsHTML () {
      $retour  = '';
      $retour .= '   <form style="margin:0px; padding:0px;" method="post" action="'.curl($this->_environment->getCurrentContextID(),'room','change','').'" name="room_change">'.LF;
      $retour .= '         <select size="1" style="font-size:10pt; width:100%" name="room_id" onChange="javascript:document.room_change.submit()">'.LF;
      $context_array = array();
      $context_array = $this->_getAllOpenContextsForCurrentUser();
      $current_portal = $this->_environment->getCurrentPortalItem();
      if ( !$this->_environment->inServer() ) {
         $title = $this->_environment->getCurrentPortalItem()->getTitle();
         $title .= ' ('.$this->_translator->getMessage('COMMON_PORTAL').')';
         $additional = '';
         if ($this->_environment->inPortal()){
            $additional = 'selected="selected"';
         }
         $retour .= '            <option value="'.$this->_environment->getCurrentPortalID().'" '.$additional.'>'.$title.'</option>'.LF;

         $current_portal_item = $this->_environment->getCurrentPortalItem();
         if ( $current_portal_item->showAllwaysPrivateRoomLink() ) {
            $link_active = true;
         } else {
            $current_user_item = $this->_environment->getCurrentUserItem();
            if ( $current_user_item->isRoomMember() ) {
               $link_active = true;
            } else {
               $link_active = false;
            }
            unset($current_user_item);
         }
         unset($current_portal_item);

         if ( $link_active ) {
            $retour .= '            <option value="-1" class="disabled" disabled="disabled">------------------------------------</option>'.LF;
            $additional = '';
            $user = $this->_environment->getCurrentUser();
            $private_room_manager = $this->_environment->getPrivateRoomManager();
            $own_room = $private_room_manager->getRelatedOwnRoomForUser($user,$this->_environment->getCurrentPortalID());
            if ( isset($own_room) ) {
               $own_cid = $own_room->getItemID();
               $additional = '';
               if ($own_room->getItemID() == $this->_environment->getCurrentContextID()) {
                  $additional = ' selected="selected"';
               }
               $retour .= '            <option value="'.$own_cid.'"'.$additional.'>'.$this->_translator->getMessage('COMMON_PRIVATEROOM').'</option>'.LF;
            }
            unset($own_room);
            unset($private_room_manager);
         }
      }

      $first_time = true;
      foreach ($context_array as $con) {
         $title = $this->_text_as_html_short($con['title']);
         $additional = '';
         if (isset($con['selected']) and $con['selected']) {
            $additional = ' selected="selected"';
         }
         if ($con['item_id'] == -1) {
            $additional = ' class="disabled" disabled="disabled"';
            if (!empty($con['title'])) {
               $title = '----'.$this->_text_as_html_short($con['title']).'----';
            } else {
               $title = '&nbsp;';
            }
         }
         if ($con['item_id'] == -2) {
            $additional = ' class="disabled" disabled="disabled" style="font-style:italic;"';
            if (!empty($con['title'])) {
               $title = $this->_text_as_html_short($con['title']);
            } else {
               $title = '&nbsp;';
            }
            $con['item_id'] = -1;
            if ($first_time) {
               $first_time = false;
            } else {
               $retour .= '            <option value="'.$con['item_id'].'"'.$additional.'>&nbsp;</option>'.LF;
            }
         }
         $retour .= '            <option value="'.$con['item_id'].'"'.$additional.'>'.$title.'</option>'.LF;
      }

      if (!$this->_current_user->isUser() and $this->_current_user->getUserID() != "guest") {
         $context = $this->_environment->getCurrentContextItem();
         if (!empty($context_array)) {
            $retour .= '            <option value="-1" class="disabled" disabled="disabled">&nbsp;</option>'.LF;
         }
         $retour .= '            <option value="-1" class="disabled" disabled="disabled">----'.$this->_translator->getMessage('MYAREA_CONTEXT_GUEST_IN').'----</option>'.LF;
         $retour .= '            <option value="'.$context->getItemID().'" selected="selected">'.$context->getTitle().'</option>'."\n";
      }
      $retour .= '         </select>'.LF;
      $retour .= '         <noscript><input type="submit" style="margin-top:3px; font-size:10pt; width:12.6em;" name="room_change" value="'.$this->_translator->getMessage('COMMON_GO_BUTTON').'"/></noscript>'.LF;
      $retour .= '   </form>'.LF;
      unset($context_array);
      return $retour;
   }



   function getMyAreaAsHTML() {
      $get_vars  = $this->_environment->getCurrentParameterArray();
      $post_vars = $this->_environment->getCurrentPostParameterArray();
      $current_context = $this->_environment->getCurrentContextItem();
      $current_portal = $this->_environment->getCurrentPortalItem();
      if (!empty($get_vars['cs_modus'])) {
         $cs_mod = $get_vars['cs_modus'];
      } elseif (!empty($post_vars['cs_modus'])) {
         $cs_mod = $post_vars['cs_modus'];
      } else {
         $cs_mod = '';
      }
      unset($get_vars);
      unset($post_vars);
      $html  = LF;

      $error_box = $this->getMyAreaErrorBox();
      if ( isset($error_box) ){
      	$error_box->setWidth('500px');
      	$html .= '<div style="margin-bottom:5px;">'.$error_box->asHTML().'</div>';
      }
      if ($this->_environment->inPortal() or $this->_environment->inServer()) {
      	$context_id = $this->_environment->getCurrentContextID();
      } else {
      	$context_id = $this->_environment->getCurrentPortalID();
      }
      
      $html .='<div id="user-wrapper" class="wrapper">
  <div id="user-container" class="container_24">
    <div class="grid_24">
      <div id="commsy-login-form">
        <div id="heading">
          <h2>AGORA-Login:</h2>
        </div>
        <form style="margin:0px; padding:0px;" method="post" action="'.curl($context_id,'context','login','').'" name="login">'.LF;

      $insert_auth_source_selectbox = false;
      if ( $current_portal->showAuthAtLogin() ) {
      	$auth_source_list = $current_portal->getAuthSourceListEnabled();
      	if ( isset($auth_source_list) and !$auth_source_list->isEmpty() ) {
      		if ($auth_source_list->getCount() == 1) {
      			$auth_source_item = $auth_source_list->getFirst();
      			$html .= '<input type="hidden" name="auth_source" value="'.$auth_source_item->getItemID().'"/>'.LF;
      		} else {
      			$insert_auth_source_selectbox = true;
      		}
      	}
      }
      
      // login redirect
      $session_item = $this->_environment->getSessionItem();
      if ($session_item->issetValue('login_redirect')) {
      	$params = $session_item->getValue('login_redirect');
      	foreach ( $params as $key => $value ) {
      		$html .= '<input type="hidden" name="login_redirect['.$key.']" value="'.$value.'"/>'.LF;
      	}
      }
      

$html .='          <div id="user">
	        <label>'.$this->_translator->getMessage('MYAREA_ACCOUNT').'</label><input type="text" size="20" placeholder="..." name="user_id">
          </div>
          <div id="pass">
	        <label>'.$this->_translator->getMessage('MYAREA_PASSWORD').'</label><input type="password" size="20" placeholder="..." name="password">
          </div>
          <div id="source">
	        <label for="auth_source">'.$this->_translator->getMessage('MYAREA_USER_AUTH_SOURCE_SHORT').'</label>
            <select name="auth_source">'.LF;


$auth_source_item = $auth_source_list->getFirst();
$auth_source_selected = false;
while ( $auth_source_item ) {
	$html .= '   <option value="'.$auth_source_item->getItemID().'"';
	if ( !$auth_source_selected ) {
		if ( isset($_GET['auth_source'])
		and !empty($_GET['auth_source'])
		and $auth_source_item->getItemID() == $_GET['auth_source']) {
			$html .= ' selected="selected"';
			$auth_source_selected = true;
		} elseif ( $auth_source_item->getItemID() == $current_portal->getAuthDefault() ) {
			$html .= ' selected="selected"';
		}
	}
	$html .= '>'.$auth_source_item->getTitle().'</option>'.LF;
	$auth_source_item = $auth_source_list->getNext();
}


    $html.='</select>
          </div>
          <div id="submit">
            <input type="submit" value="'.$this->_translator->getMessage('MYAREA_LOGIN_BUTTON').'" name="option">
          </div>
        </form>
      </div>'.LF;
    $html.='
    <div id="commsy-login-links">
    <ul>'.LF;
    
               // auth source
               $auth_source_list = $current_portal->getAuthSourceListEnabled();
               $count_auth_source_list_add_account = 0;
               if ( isset($auth_source_list) and !$auth_source_list->isEmpty() ) {
                  $auth_source_item = $auth_source_list->getFirst();
                  while ($auth_source_item) {
                     $temp_array = array();
                     if ( $auth_source_item->allowAddAccount() ) {
                        $count_auth_source_list_add_account++;
                     }
                     $auth_source_item = $auth_source_list->getNext();
                  }
               }
               unset($auth_source_list);
        // @segment-end 2240
    // @segment-begin 83516 no_cs_modus/user=guest:links-want_account/account_forget/pasword_forget(log_in_form-end)
    if ( $count_auth_source_list_add_account != 0 ) {
    	$params['cs_modus'] = 'portalmember';
    	$html .= '<li id="apply">'.ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,$this->_translator->getMessage('MYAREA_LOGIN_ACCOUNT_WANT_LINK'),'','','','','','','style="display:inline;"').'</li>'.LF;
    } else {
    	$html .= '<li id="apply">'.$this->_translator->getMessage('MYAREA_LOGIN_ACCOUNT_WANT_LINK').'</li>'.LF;
    }
    $params['cs_modus'] = 'account_forget';
    $html .= '<li>'.ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,$this->_translator->getMessage('MYAREA_LOGIN_ACCOUNT_FORGET_LINK'),'','','','','','','style="display:inline;"').'</li>'.LF;
    if ($count_auth_source_list_add_account != 0) {
    	$params['cs_modus'] = 'password_forget';
    	$html .= '<li>'.ahref_curl($this->_environment->getCurrentContextID(),$this->_environment->getCurrentModule(),$this->_environment->getCurrentFunction(),$params,$this->_translator->getMessage('MYAREA_LOGIN_PASSWORD_FORGET_LINK'),'','','','','','','style="display:inline;"').'</li>'.LF;
    } else {
    	$html .= '<li>'.$this->_translator->getMessage('MYAREA_LOGIN_PASSWORD_FORGET_LINK').'</li>'.LF;
    }
    unset($params);
    
$html .='        </ul>
      </div>
    </div>
  <!-- end .grid_24 -->
  </div>
  <!-- end #user-container -->'.LF;
      
if ( !empty($cs_mod)
         and ( $cs_mod == 'portalmember'
         or $cs_mod == 'portalmember2'
         )
         ) {
         if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
   } else {
              $params = array();
            $params['iid'] = $this->_current_user->getItemID();
      if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
         $portal_user = $this->_environment->getPortalUserItem();
         $fullname = $portal_user->getFullname();
         unset($portal_user);
      } else {
         $fullname = $this->_current_user->getFullname();
      }

        }
        $html .= '<div class="myarea_content" style="font-size:8pt;">'.LF;
        if ( $cs_mod == 'portalmember' ) {
             include_once('classes/cs_home_member_page.php');
     $left_page = new cs_home_member_page($this->_environment);
        } else {
             include_once('classes/cs_home_member2_page.php');
     $left_page = new cs_home_member2_page($this->_environment);
        }
        $html .= $left_page->execute();
        unset($left_page);
#        $html .= '</div>'.LF;
      }

     // change account
     elseif (!empty($cs_mod) and $cs_mod == 'account_change') {
        if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
        } else {
           $params = array();
           $params['iid'] = $this->_current_user->getItemID();
           if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
              $portal_user = $this->_environment->getPortalUserItem();
              $fullname = $portal_user->getFullname();
              unset($portal_user);
           } else {
              $fullname = $this->_current_user->getFullname();
           }
        }
        $html .= '<div class="myarea_content" style="font-size:8pt;">'.LF;
        $current_portal_item = $this->_environment->getCurrentPortalItem();
        $current_auth_source_item = $current_portal_item->getAuthSource($this->_current_user->getAuthSource());
        unset($current_portal_item);
        if ( $current_auth_source_item->allowChangeUserID() ) {
           include_once('classes/cs_account_change_page.php');
           $left_page = new cs_account_change_page($this->_environment);
           $html .= $left_page->execute();
           $html .= BRLF;
        }
        unset($current_auth_source_item);
        include_once('classes/cs_account_merge_page.php');
        $left_page = new cs_account_merge_page($this->_environment);
        $html .= $left_page->execute();
        unset($left_page);
#        $html .= '</div>'.LF;
      }

     // forget account
      elseif (!empty($cs_mod) and $cs_mod == 'account_forget') {
         if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
   } else {
              $params = array();
            $params['iid'] = $this->_current_user->getItemID();
      if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
         $portal_user = $this->_environment->getPortalUserItem();
         $fullname = $portal_user->getFullname();
               unset($portal_user);
      } else {
         $fullname = $this->_current_user->getFullname();
      }
        }
        $html .= '<div class="myarea_content" style="font-size:8pt;">'.LF;
        include_once('classes/cs_account_forget_page.php');
        $left_page = new cs_account_forget_page($this->_environment);
        $html .= $left_page->execute();
        unset($left_page);
#        $html .= '</div>'.LF;
      }

     // forget password
      elseif (!empty($cs_mod) and $cs_mod == 'password_forget') {
         if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
   } else {
            $params = array();
            $params['iid'] = $this->_current_user->getItemID();
      if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
         $portal_user = $this->_environment->getPortalUserItem();
         $fullname = $portal_user->getFullname();
               unset($portal_user);
      } else {
         $fullname = $this->_current_user->getFullname();
      }
   }
         $html .= '<div class="myarea_content" style="font-size:8pt;">'.LF;
         include_once('classes/cs_password_forget_page.php');
   $left_page = new cs_password_forget_page($this->_environment);
   $html .= $left_page->execute();
         unset($left_page);
#         $html .= '</div>'.LF;
      }

      // become member
      elseif ( !empty($cs_mod) and $cs_mod == 'become_member' ) {
         if ( !empty($this->_current_user) and ($this->_current_user->getUserID() == 'guest' and $this->_current_user->isGuest()) ) {
         } else {
            $params = array();
            $params['iid'] = $this->_current_user->getItemID();
            if ( $this->_environment->inProjectRoom() or $this->_environment->inCommunityRoom()) {
               $portal_user = $this->_environment->getPortalUserItem();
               $fullname = $portal_user->getFullname();
               unset($portal_user);
            } else {
               $fullname = $this->_current_user->getFullname();
            }
        }
        $html .= '<div class="myarea_content" style="font-size:8pt;">'.LF;
        include_once('classes/cs_become_member_page.php');
        $left_page = new cs_become_member_page($this->_environment);
        $html .= $left_page->execute();
        unset($left_page);
#        $html .= '</div>'.LF;
      }
      // @segment-end 90042
      // @segment-begin 89418 end-of-my_area_box/down-corner-pictures
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      // @segment-end 89418
$html .='</div>
<!-- end #user-wrapper -->
<!-- ====== END COMMSY LOGIN ====== -->'.LF;      
      return $html;
   }

   function _getServerNewsAsHTML() {
      $server_item = $this->_environment->getServerItem();
      $portal_item = $this->_environment->getCurrentPortalItem();
      $get_vars  = $this->_environment->getCurrentParameterArray();
      $post_vars = $this->_environment->getCurrentPostParameterArray();
      if (!empty($get_vars['cs_modus'])) {
         $cs_mod = $get_vars['cs_modus'];
      } elseif (!empty($post_vars['cs_modus'])) {
         $cs_mod = $post_vars['cs_modus'];
      } else {
         $cs_mod = '';
      }
      $html  = LF;
      if ( $server_item->showServerNews()
           and ( !isset($portal_item)
                 or ( $portal_item->isPortal()
                      and $portal_item->showNewsFromServer()
                     )
               )
         ) {
         $html .= '<h2>'.LF;
         $html .= $this->_translator->getMessage('COMMON_SERVER_NEWS');
         $html .= '</h2>'.LF;
         $link = $server_item->getServerNewsLink();
         if (!empty($link)) {
            $title = '<h3 ><a href="'.$link.'" style="color:#800000" target="_blank">'.$server_item->getServerNewsTitle().'</a></h3>'.LF;
         } else {
            $title = '<h3>'.$server_item->getServerNewsTitle().'</h3>'.LF;
         }
         $html .= '<div style="padding:3px;">'.LF;
         $html .= '<div class="myarea_section_title">'.$title.'</div>';
         $html .= '<div class="myarea_content" style="position:relative; ">'.LF;

         $text = $server_item->getServerNewsText();
         if (!empty($text)) {
            $html .= '<span style="font-size: 8pt;">'.$text.'</span>'.LF;
         }
         if (!empty($link)) {
            $html .= '<span style="font-size: 8pt;"> [<a href="'.$link.'" style="color:#800000" target="_blank">'.'mehr ...'.'</a>]</span>'.LF;
         }

         $html .= '</div>'.LF;
         $html .= '</div>'.LF;
      }

      if ( isset($portal_item)
           and $portal_item->isPortal()
           and $portal_item->showServerNews()
         ) {
         $html .= '<h2>'.LF;
         $html .= $this->_translator->getMessage('COMMON_PORTAL_NEWS');
         $html .= '</h2>'.LF;
         $link = $portal_item->getServerNewsLink();
         if (!empty($link)) {
            $title = '<h3><a href="'.$link.'" style="color:#800000" target="_blank">'.$portal_item->getServerNewsTitle().'</a></h3>'.LF;
         } else {
            $title = '<h3>'.$portal_item->getServerNewsTitle().'</h3>'.LF;
         }
         $html .= '<div style="padding:3px;">'.LF;
         $html .= '<div class="myarea_section_title">'.$title.'</div>';
         $html .= '<div class="myarea_content" style="position:relative; padding-bottom:0em;">'.LF;

         $text = $portal_item->getServerNewsText();
         if (!empty($text)) {
            $html .= '<span style="font-size: 8pt;">'.$text.'</span>'.LF;
         }
         if (!empty($link)) {
            $html .= '<span style="font-size: 8pt;"> [<a href="'.$link.'" style="color:#800000" target="_blank">'.'mehr ...'.'</a>]</span>'.LF;
         }
         $html .= '</div>'.LF;
         $html .= '</div>'.LF;
      }
      unset($portal_item);
      unset($server_item);
      return $html;
   }


   function AnnouncementsAsHTML ($announcement_view) {
      $i =1;
      $retour  = LF.'<!-- BEGIN OF GUIDE COMMUNITY ANNOUNCEMENT VIEW -->'.LF;
      $retour .= '<table style=" width:230px; border-collapse: collapse; border: 0px; padding:0px; margin-left:5px; font-weight:normal;" summary="Layout">'.LF;

      if ( isset($announcement_view->_list) and !$announcement_view->_list->isEmpty() ) {
         $community = $announcement_view->_list->getFirst();
         while ($community) {
         if($community->isOpenForGuests() OR $community->isUser($this->_environment->getCurrentUser()) OR $this->_environment->getCurrentUser()->isRoot()) {
               $text = '';
               $text .= '<tr>'.LF;
               $logo = $announcement_view->_getLogo($community);
               if ( !empty($logo) ) {
                  $text .= '        <td style="vertical-align: middle; width: 10%; padding-top: 0px; padding-bottom: 0px;">'.LF;
                  $text .= $logo.LF;
                  $text .= '        </td>'.LF;
                  $text .= '        <td style="vertical-align: middle; width: 70%; padding-top: 0px; padding-bottom: 0px; text-align:left;">'.LF;
               } else {
                  $text .= '        <td colspan="2" style="vertical-align: middle; width: 80%; padding-top: 3px; padding-bottom:3px; padding-left:3px; text-align:left;">'.LF;
               }
               $current_user = $this->_environment->getCurrentUserItem();
               $params['room_id'] = $community->getItemID();
               $length = mb_strlen($community->getTitle());
               if ( $length > 20 and !mb_stristr($community->getTitle(),' ') ) {
                  $title = mb_substr($community->getTitle(),0,20).'...';
               } else {
                  $title = $community->getTitle();
               }
               $text .= ahref_curl($this->_environment->getCurrentContextID(),'home','index',$params,$title).LF;
               $text .= '</td><td style="width:20%; text-align:right;">';
               if ($community->mayEnter($current_user)) {
                  $text .= '            '.ahref_curl($community->getItemID(),
                              'home',
                              'index',
                              '',
                              '<img src="images/door_open_small.gif" style="vertical-align: middle;" alt="door open"/>');
               } else {
                  $text .= '            <img src="images/door_closed_small.gif" style="vertical-align: middle;" alt="door closed"/>';
               }
               $params = array();
               $text .= '       </td>'.LF;
               $text .= '   </tr>'.LF;
               $text .= '   <tr>'.LF;
               $text .= '       <td class="anouncement_background" colspan="3" style="font-size:8pt; font-weight:normal; padding-bottom:3px; border-bottom: 1px solid #C6C7CD;">'.LF;
               $text .= '               '.$announcement_view->_getAnnouncement($community).LF;
               $text .= '       </td>'.LF;
               $text .= '   </tr>'.LF;
               if ($announcement_view->_with_announcement and $i < 4){
                  $retour .= $text;
                  $i++;
               }
         }
           $community = $announcement_view->_list->getNext();

         }
      }
      $retour .= '</table>';
      $retour .= '<!-- END OF GUIDE COMMUNITY ANNOUNCEMENT VIEW -->'.LF.LF;

      return $retour;
   }



   function _getPortalAnnouncements(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'.LF;
      $html .= '<h2>'.$this->_translator->getMessage('COMMON_ANNOUNCEMENT_INDEX').LF;
      $html .= '</h2>';
      $html .= '<div id="announcement_box">'."\n";

      $params = array();
      $params['environment'] = $this->_environment;
      $announcement_view = $this->_class_factory->getClass(ANNOUNCEMENT_SHORT_COMMUNITY_GUIDE_VIEW,$params);
      unset($params);
      $community_manager = $this->_environment->getCommunityManager();
      $community_manager->setOpenedLimit();
      $community_manager->setOrder('activity_rev');
      $community_manager->select();
      $community_list = $community_manager->get();
      if (!$community_list->isEmpty()) {
         $announcement_view->setList($community_list);
         $html .= $this->AnnouncementsAsHTML($announcement_view);
      } else {
         $html .= $this->AnnouncementsAsHTML($announcement_view);
     }
      $html .= '</div>'.LF;

      return $html;
   }

  function _getTableheadAsHTML($room_list_view) {
      $html = '';
      $current_portal = $room_list_view->_environment->getCurrentPortalItem();
      if ($room_list_view->_environment->inPortal()) {
         $params = $room_list_view->_environment->getCurrentParameterArray();
         $html .= '   <tr class="list">'.LF;
         $html .= '      <td colspan="2" class="portal-head" style="font-size: 10pt; width:45%">'.LF;
         if ( $room_list_view->getSortKey() == 'title' ) {
            $params['sort'] = 'title_rev';
            $text = $room_list_view->_translator->getMessage('COMMON_TITLE');
         } elseif ( $room_list_view->getSortKey() == 'title_rev' ) {
            $params['sort'] = 'title';
            $text = $room_list_view->_translator->getMessage('COMMON_TITLE');
         } else {
            $params['sort'] = 'title';
            $text = $room_list_view->_translator->getMessage('COMMON_TITLE');
         }
         if ( empty($room_list_view->_activity_modus) ) {
            $html .= ahref_curl($room_list_view->_environment->getCurrentContextID(), $room_list_view->_module, $room_list_view->_function,
                                $params, $text, '', '', '','','','','class="head"').LF;
         } else {
            $html .= $text;
         }
         if ( $room_list_view->getSortKey() == 'title' ) {
            $html .= ' <img src="images/sort_up.gif" alt="&lt;" border="0"/>';
         } elseif ( $room_list_view->getSortKey() == 'title_rev' ) {
            $html .= ' <img src="images/sort_down.gif" alt="&lt;" border="0"/>';
         }
         $html .= '      </td>'.LF;
         if ($room_list_view->_environment->inPortal()) {
            $html .= '      <td class="portal-head" style="font-size: 8pt; width:30%">'.LF;
            $html .= '<span class="portal_link">'.$room_list_view->_translator->getMessage('CONTEXT_MODERATOR').'</span>'.LF;
            $html .= '      </td>'.LF;
         }

         $html .= '      <td class="portal-head" style="font-size: 8pt; width:20%">'.LF;
         if ( $room_list_view->getSortKey() == 'activity_rev' ) {
            $params['sort'] = 'activity';
            $text = $room_list_view->_translator->getMessage('CONTEXT_ACTIVITY');
         } elseif ( $room_list_view->getSortKey() == 'activity' ) {
            $params['sort'] = 'activity_rev';
            $text = $room_list_view->_translator->getMessage('CONTEXT_ACTIVITY');
         } else {
            $params['sort'] = 'activity_rev';
            $text = $room_list_view->_translator->getMessage('CONTEXT_ACTIVITY');
         }
         if ( empty($room_list_view->_activity_modus) ) {
            $html .= ahref_curl($room_list_view->_environment->getCurrentContextID(), $room_list_view->_module, $room_list_view->_function,
                                $params, $text, '', '', '','','','','class="head"').LF;
         } else {
            $html .= $text;
         }
         if ( $room_list_view->getSortKey() == 'activity_rev' ) {
            $html .= ' <img src="images/sort_down.gif" alt="&lt;" border="0"/>';
         } elseif ( $room_list_view->getSortKey() == 'activity' ) {
           $html .= ' <img src="images/sort_up.gif" alt="&lt;" border="0"/>';
         }
         $html .= '      </td>'.LF;

         $html .= '   </tr>'.LF;
      }
      return $html;
   }


   function getContentListAsHTML(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'."\n";
      $html .= '<div id="room_list" style="padding:0px;">'."\n";
      if ( isset($this->_room_list_view) ){
         $html .='<table style="width:100%; margin:0px; padding:0px; border-bottom:0px;" summary="Layout">'.LF;
         $html .='<tr>'.LF;
         $html .='<td class="room_list_head" style="width:55%; vertical-align:bottom; white-space:nowrap;">'.LF;
         $html .='<div>'.LF;
         $html .='<div>'.LF;
         if ($this->_environment->inServer()) {
            $html .= '<span class="portal_section_title">'.$this->_translator->getMessage('SERVER_PORTAL_OVERVIEW').'</span>'.LF;
         } else {
            $html .= '<span class="portal_section_title">'.$this->_getRoomListHeadingAsHTML().'</span>'.LF;
         }
         $html .='</div>'.LF;
         $html .='</div>'.LF;
         $html .='</td>'.LF;
         $html .='<td class="room_list_head" colspan="2" style="width:45%; vertical-align:bottom; text-align:right; white-space:nowrap;">'.LF;
         $html .='<div style="float:right;text-align:right;">'.LF;
         $html .= '&nbsp;&nbsp;<span class="portal_forward_links">'.$this->_room_list_view->_getForwardLinkAsHTML().'</span>'.LF;
         $html .='</div>'.LF;
         $html .='</td>'.LF;
         $html .='</tr>'.LF;
         $html .='<tr>'.LF;
         $html .='<td style="vertical-align:bottom; font-size:8pt;">'.LF;
         $html .= $this->_room_list_view->_getDescriptionAsHTML().LF;
         $html .='</td>'.LF;
         $html .='<td colspan="2" style="padding-top:5px; vertical-align:bottom; text-align:right; white-space:nowrap;">'.LF;
         if (!$this->_environment->inServer()) {

            $html .='<div style="float:right;text-align:right; font-size:8pt;">'.LF;
            $html .= '<span class="portal_description">'.$this->_room_list_view->_getIntervalLinksFirstLineAsHTML().'</span>'.LF;
            $html .= '&nbsp;<span class="portal_description">'.$this->_room_list_view->_getIntervalLinksSecondLineAsHTML().'</span>'.LF;
            $html .='</div>'.LF;
         }
         $html .='</td>'.LF;
         $html .='</tr>'.LF;
         $html .='</table>'.LF;
         $html .='<table style="width:100%; margin:0px; padding:0px;" summary="Layout">'.LF;
         $html .='<tr>'.LF;

         $html .='<td colspan="3" style="padding-top:0px; vertical-align:top; ">'.LF;


         $html .= '<table style="border-collapse: collapse; border: 0px; padding:0px;" summary="Layout">'.LF;
         $html .= $this->_getTableheadAsHTML($this->_room_list_view);

      $current_user = $this->_environment->getCurrentUserItem();
      if ( !empty($this->_room_list_view->_list) ) {
         $html .= $this->_room_list_view->_getContentAsHTML();
      } elseif ( !$current_user->isUser() ) {
         $html .= '<tr><td colspan="3">'.$this->_translator->getMessage('PORTAL_LOGIN_TO_SEE_ROOMS').'</td></tr>';
      }
      unset($current_user);

         #$html .= $this->_room_list_view->_getContentAsHTML();
         $html .= '</table>'.LF;
         $html .='</td>'.LF;
         $html .='</tr>'.LF;
         $html .= '</table>'.LF;
         $html .= '<!-- END OF PLAIN LIST VIEW -->'.LF.LF;
      }
      $html .= '</div>'.LF;
      return $html;
   }

   function getPortalActionsAsHTML(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'."\n";
      $html .= '<div id="portal_actions">'."\n";
      if ( isset($this->_room_list_view) ){
#         $html .= $this->_room_list_view->getPortalActionsAsHTML();
      }
      $html .= '</div>'.LF;
      return $html;
   }

   function getPortalModerationLinksAsHTML(){
      $html ='';
      $html .= LF.'<!-- BEGIN TABS -->'."\n";
      $html .= '<div id="portal_moderation_links">'."\n";
      if ( isset($this->_room_list_view) ){
         $html .= $this->_room_list_view->_getConfigurationBoxAsHTML();
      }
      $html .= '</div>'.LF;
      return $html;
   }


   function getLogoAsHTML(){
      $html  = '';
      $logo_filename = '';
      $context_item = $this->_environment->getCurrentContextItem();
      $html .='<table summary="layout">';
      $html .= '<tr>';
       $html .= '<td>';
      $html .= '<div class="logo" style="vertical-align:top;padding-top:5px;">'.LF;
         if ( $this->_environment->inCommunityRoom()
              or $this->_environment->inProjectRoom()
              or $this->_environment->inPrivateRoom()
              or $this->_environment->inGroupRoom()
            ) {
            $logo_filename = $context_item->getLogoFilename();
            if ( !empty($logo_filename) ) {
               $params = array();
               $params['picture'] = $context_item->getLogoFilename();
               $curl = curl($this->_environment->getCurrentContextID(), 'picture', 'getfile', $params,'');
               unset($params);
               $html .= '     <img style="height:4em; padding-top:0px; padding-bottom:0px; padding-left:0px;" src="'.$curl.'" alt="'.$this->_translator->getMessage('COMMON_LOGO').'" border="0"/>';
            }
         }
     $html .= '</div>'.LF;
      $html .= '</td>';
      $html .= '<td style="verticale-align:middle;">';
      $html .= '<span style="font-size:24pt; font-weight:bold;">'.$context_item->getTitle().'</span>';
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>';

      return $html;
   }



   
   
   private function _getCommSyBarBeforeContentAsHTML() {
   	$html = "";
   
   	$currentUser = $this->_environment->getCurrentUserItem();
   	$translator = $this->_environment->getTranslationObject();
   	if ($this->_environment->InPortal() && !$currentUser->isGuest()) {
   		$html .= '
   			<div id="top_menu">
   				<div id="tm_wrapper_outer">
   					<div id="tm_wrapper">
						<div id="tm_icons_bar">
   		';
   		 
   		if ( !$currentUser->isReallyGuest() )
   		{
   			$html .= '
   							<a href="commsy.php?cid=' . $this->_environment->getCurrentContextID() . '&mod=context&fct=logout&iid=' . $currentUser->getItemID() . '" id="tm_logout" title="' . $translator->getMessage("LOGOUT") . '">
   								&nbsp;
   							</a>
   			';
   		}
   		else
   		{
   			$html .= '
   							<a href="commsy.php?cid=' . $this->_environment->getCurrentPortalID() . '&mod=home&fct=index&room_id=' . $this->_environment->getCurrentContextID() . '&login_redirect=1" class="tm_user" style="width:70px;" title="' . $translator->getMessage("MYAREA_LOGIN_BUTTON") . '">
   								' . $translator->getMessage("MYAREA_LOGIN_BUTTON") . '
   							</a>
   			';
   		}
   		 
   		$html .= '
   							<div class="clear"></div>
   						</div>
   		
   						<div id="tm_pers_bar">
   							<a href="#" id="tm_user">
   		';
   		 
   		if ( !$currentUser->isReallyGuest() )
   		{
   			$html .= 			$translator->getMessage("COMMON_WELCOME") . ", " . mb_substr($currentUser->getFullName(), 0, 20);
   		}
   		else
   		{
   			$html .= 			$translator->getMessage("COMMON_WELCOME") . ", " . $translator->getMessage("COMMON_GUEST");
   		}
   		 
   		$html .= '
   							</a>
   						</div>
   		';
   		 
   		if ( !$currentUser->isReallyGuest() )
   		{
   			$ownRoomItem = $currentUser->getOwnRoom();
   
   
   			$return = array(
						'connection'	=> array(
						      'active'	=> false
						),
   					'wiki'		=> array(
   							'active'	=> false
   					),
   					'chat'		=> array(
   							'active'	=> false
   					),
   					'wordpress'	=> array(
   							'active'	=> false
   					),
   					'rss'		=> array(
   							'active'	=> false
   					),
   					'rows'		=> 0
   			);
   
   			$current_context = $this->_environment->getCurrentContextItem();
   			$current_user = $this->_environment->getCurrentUserItem();
   			$count = 0;
   
   			// wiki
   			if($current_context->showWikiLink() && $current_context->existWiki() && $current_context->issetWikiHomeLink()) {
   				global $c_pmwiki_path_url;
   
   				$count++;
   				$return['wiki']['active'] = true;
   				$return['wiki']['title'] = $current_context->getWikiTitle();
   				$return['wiki']['path'] = $c_pmwiki_path_url;
   				$return['wiki']['portal_id'] = $this->_environment->getCurrentPortalID();
   				$return['wiki']['item_id'] = $current_context->getItemID();
   
   				$url_session_id = '';
   				if($current_context->withWikiUseCommSyLogin()) {
   					$session_item = $this->_environment->getSessionItem();
   					$url_session_id = '?commsy_session_id=' . $session_item->getSessionID();
   					unset($session_item);
   				}
   				$return['wiki']['session'] = $url_session_id;
   			}
   
   			// chat
   			if($current_context->showChatLink()) {
   				global $c_etchat_enable;
   				if(!empty($c_etchat_enable) && $c_etchat_enable) {
   					if(isset($current_user) && $current_user->isReallyGuest()) {
   					} else {
   						$count++;
   						$return['chat']['active'] = true;
   					}
   				}
   			}
   
   			// wordpress
   			if($current_context->showWordpressLink() && $current_context->existWordpress() && $current_context->issetWordpressHomeLink()) {
   				global $c_wordpress_path_url;
   
   				$count++;
   				$return['wordpress']['active'] = true;
   				$return['wordpress']['title'] = $current_context->getWordpressTitle();
   				$return['wordpress']['path'] = $c_wordpress_path_url;
   				$return['wordpress']['item_id'] = $current_context->getItemID();
   
   				$url_session_id = '';
   				if($current_context->withWordpressUseCommSyLogin()) {
   					$session_item = $this->_environment->getSessionItem();
   					$url_session_id = '?commsy_session_id=' . $session_item->getSessionID();
   					unset($session_item);
   				}
   				$return['wordpress']['session'] = $url_session_id;
   			}
   			// rss
   			$show_rss_link = false;
   			if($current_context->isLocked() || $current_context->isClosed()) {
   				// do nothing
   			} elseif($current_context->isOpenForGuests()) {
   				$show_rss_link = true;
   			} elseif($current_user->isUser()) {
   				$show_rss_link = true;
   			}
   
   			$hash_string = '';
   			if(!$current_context->isOpenForGuests() && $current_user->isUser()) {
   				$hash_manager = $this->_environment->getHashManager();
   				$hash_string = '&amp;hid=' . $hash_manager->getRSSHashForUser($current_user->getItemID());
   			}
   
   			if(!$current_context->isRSSOn()) {
   				$show_rss_link = false;
   			}
   
   			if($show_rss_link) {
   				$count++;
   				$return['rss']['active'] = true;
   				$return['rss']['item_id'] = $current_context->getItemID();
   				$return['rss']['hash'] = $hash_string;
   			}
   
   			$return['rows'] = ceil($count / 2);
   
   			$addonInformation = $return;
   
   			$html .= '	<div id="tm_icons_bar">';
   
   			if ( $addonInformation["wiki"]["active"] === true )
   			{
   				$wiki = $addonInformation["wiki"];
   				$html .= '	<a href="' . $wiki["path"] . '/wikis/' . $wiki["portal_id"] . '/' . $wiki["item_id"] . '/index.php' . $wiki["session"] . '" title="' . $translator->getMessage("COMMON_WIKI_LINK") . ': ' . $wiki["title"] . '" target="_blank" id="tm_wiki">
   								&nbsp;
   							</a>
   				';
   			}
   
   			if ( $addonInformation["wordpress"]["active"] === true )
   			{
   				$wordpress = $addonInformation["wordpress"];
   				$html .= '	<a href="' . $wordpress["path"] . '/' . $this->_environment->getCurrentPortalID() . '_' . $wordpress["item_id"] . '/' . $wordpress["session"] . '" title="' . $translator->getMessage("COMMON_WORDPRESS_LINK") . ': ' . $wordpress["title"] . '" target="_blank" id="tm_wordpress">
   								&nbsp;
   							</a>
   				';
   			}
   
   			if ( isset($ownRoomItem)) {
   			   // portal2portal
   				if ( !empty($ownRoomItem)
   					  and $ownRoomItem->showCSBarConnection()
   					) {
   					$html .= '	<a href="#" id="tm_connection" title="' . $translator->getMessage("CS_BAR_CONNECTION") . '">&nbsp;</a>';
   				}
   				   				
   				if ( $ownRoomItem->getCSBarShowPortfolio() == "1" )
   				{
   					$html .= '	<a href="#" id="tm_portfolio" title="' . $translator->getMessage("CS_BAR_PORTFOLIO") . '">&nbsp;</a>';
   				}
   					
   				if ( $ownRoomItem->getCSBarShowWidgets() == "1" )
   				{
   					$html .= '	<a href="#" id="tm_widgets" title="' . $translator->getMessage("MYWIDGETS_INDEX") . '">&nbsp;</a>';
   				}
   					
   				if ( $ownRoomItem->getCSBarShowCalendar() == "1" )
   				{
   					$html .= '	<a href="#" id="tm_mycalendar" title="' . $translator->getMessage("MYCALENDAR_INDEX") . '">&nbsp;</a>';
   				}
   					
   				if ( $ownRoomItem->getCSBarShowStack() == "1" )
   				{
   					$html .= '	<a href="#" id="tm_stack" title="' . $translator->getMessage("COMMON_ENTRY_INDEX") . '">&nbsp;</a>';
   				}
   				
   				$html .= '<a href="#" id="tm_clipboard" title="' . $translator->getMESSAGE("MYAREA_MY_COPIES") . '">&nbsp;</a>';
   				$numCopies = 0;
   				$rubric_copy_array = array(CS_ANNOUNCEMENT_TYPE, CS_DATE_TYPE, CS_DISCUSSION_TYPE, CS_MATERIAL_TYPE, CS_TODO_TYPE);
   				$session = $this->_environment->getSessionItem();
   				foreach ($rubric_copy_array as $rubric){
   					$numCopies += count($session->getValue($rubric.'_clipboard'));
   				}
   					
   				if ( $numCopies > 0)
   				{
   					$html .= '	<span id="tm_clipboard_copies">' . $numCopies . '</span>';
   				}
   			}
   
   			$html .= '
   							<div class="clear"></div>
   						</div>
   			';
   		}
   		 
   		if (isset($ownRoomItem) && $ownRoomItem->getCSBarShowOldRoomSwitcher() === "1" )
   		{
   			$html .= '	<div id="tm_breadcrumb_old">';
   			$retour  = '';
   			$retour .= '   <form style="margin:0px; padding:0px;" method="post" action="'.curl($this->_environment->getCurrentContextID(),'room','change','').'" name="room_change_bar">'.LF;
   			// jQuery
   			//$retour .= '         <select size="1" style="font-size:8pt; width:220px;" name="room_id" onChange="javascript:document.room_change.submit()">'.LF;
   			$retour .= '         <select onchange="document.room_change_bar.submit()" size="1" style="font-size:8pt; width:220px;" name="room_id" id="submit_form">'.LF;
   					// jQuery
   					$context_array = array();
   					$context_array = $this->_getAllOpenContextsForCurrentUser();
   					$current_portal = $this->_environment->getCurrentPortalItem();
   					$translator = $this->_environment->getTranslationObject();
   					$text_converter = $this->_environment->getTextConverter();
   					if ( !$this->_environment->inServer() ) {
   					$title = $this->_environment->getCurrentPortalItem()->getTitle();
   					$title .= ' ('.$translator->getMessage('COMMON_PORTAL').')';
   					$additional = '';
   					if ($this->_environment->inPortal()){
   					$additional = 'selected="selected"';
   					}
   					$retour .= '            <option value="'.$this->_environment->getCurrentPortalID().'" '.$additional.'>'.$title.'</option>'.LF;
   
   							$current_portal_item = $this->_environment->getCurrentPortalItem();
   									if ( $current_portal_item->showAllwaysPrivateRoomLink() ) {
   									$link_active = true;
   					} else {
   					$current_user_item = $this->_environment->getCurrentUserItem();
   					if ( $current_user_item->isRoomMember() ) {
   					$link_active = true;
   					} else {
   					$link_active = false;
   					}
   					unset($current_user_item);
   					}
   					unset($current_portal_item);
   
   					}
   
   						$first_time = true;
   						foreach ($context_array as $con) {
   							$title = $text_converter->text_as_html_short($con['title']);
   							$additional = '';
   							if (isset($con['selected']) and $con['selected']) {
   							$additional = ' selected="selected"';
   	}
   	if ($con['item_id'] == -1) {
   	$additional = ' class="disabled" disabled="disabled"';
   	if (!empty($con['title'])) {
   	$title = '----'.$text_converter->text_as_html_short($con['title']).'----';
   	} else {
   			$title = '&nbsp;';
   	}
   	}
   	if ($con['item_id'] == -2) {
   	$additional = ' class="disabled" disabled="disabled" style="font-style:italic;"';
   	if (!empty($con['title'])) {
   			$title = $text_converter->text_as_html_short($con['title']);
   	} else {
   		$title = '&nbsp;';
   	}
   	$con['item_id'] = -1;
   	if ($first_time) {
   			$first_time = false;
   			} else {
   				$retour .= '            <option value="'.$con['item_id'].'"'.$additional.'>&nbsp;</option>'.LF;
   				}
   				}
   					$retour .= '            <option value="'.$con['item_id'].'"'.$additional.'>'.$title.'</option>'.LF;
   				}
   
   				$current_user_item = $this->_environment->getCurrentUserItem();
   				if (!$current_user_item->isUser() and $current_user_item->getUserID() != "guest") {
   					$context = $this->_environment->getCurrentContextItem();
   					if (!empty($context_array)) {
   					$retour .= '            <option value="-1" class="disabled" disabled="disabled">&nbsp;</option>'.LF;
				         }
   							$retour .= '            <option value="-1" class="disabled" disabled="disabled">----'.$translator->getMessage('MYAREA_CONTEXT_GUEST_IN').'----</option>'.LF;
   									$retour .= '            <option value="'.$context->getItemID().'" selected="selected">'.$context->getTitle().'</option>'."\n";
   	}
   	$retour .= '         </select>'.LF;
   	$retour .= '         <noscript><input type="submit" style="margin-top:3px; font-size:10pt; width:12.6em;" name="room_change_bar" value="'.$translator->getMessage('COMMON_GO_BUTTON').'"/></noscript>'.LF;
				      $retour .= '   </form>'.LF;
				      unset($context_array);
   				
   				      $html .= $retour;
   				      $html .= '	</div>';
   		}
   		else
   		{
   			$html .= '
   						<div id="tm_breadcrumb">
      						<a href="#" id="tm_bread_crumb">' . $translator->getMessage("COMMON_GO_BUTTON") . ': ' . $this->_environment->getCurrentPortalItem()->getTitle() . '</a>
      						</div>
      						';
   	}
   	 
      								if ( $currentUser->isModerator() )
   		{
      										$html .= '
      										<div id="tm_icons_left_bar">
   							<a href="commsy.php?cid=' . $this->_environment->getCurrentContextID() . '&mod=configuration&fct=index" id="tm_settings" title="' . $translator->getMessage("COMMON_CONFIGURATION") . '">&nbsp;</a>
      							';
   
      							$html .= '
      							<div class="clear"></div>
      							</div>
      							';
      							}
   
   		$html .= '
   						<div class="clear"></div>
   					</div>
      									</div>
      										
      									<div id="tm_menus">
      									<div id="tm_dropmenu_breadcrumb" class="hidden"></div>
   							   		<div id="tm_dropmenu_connection" class="hidden"></div>
      									<div id="tm_dropmenu_widget_bar" class="hidden"></div>
      									<div id="tm_dropmenu_portfolio" class="hidden"></div>
			   		<div id="tm_dropmenu_mycalendar" class="hidden"></div>
   			   		<div id="tm_dropmenu_stack" class="hidden"></div>
			   		<div id="tm_dropmenu_pers_bar" class="hidden"></div>
			   		<div id="tm_dropmenu_clipboard" class="hidden"></div>
   			   		<div id="tm_dropmenu_configuration" class="hidden"></div>
   			   				</div>
   			   				</div>
   		';
   	}
   
   	return $html;
   }
  


   function asHTML(){

     /*
     *********************************
     ******Verwendbare Funktionen*****
     getLogoAsHTML()                  -> Darstellung des Logos
     getMyAreaAsHTML()                -> Darstellung des Anmeldungsbereichs
     getSearchBoxAsHTML()             -> Darstellung der Suchbox
     getPortalFormsAsHTML()           -> Darstellung der Formulare
     getContentListAsHTML()           -> Darstellung der Raumliste
     getPortalActionsAsHTML()         -> Raumformularlinks
     getPortalModerationLinksAsHTML() -> Moderationslinkliste
     getRoomItemAsHTML()              -> Detailbeschreibung
     getConfigurationAsHTML()         ->Raumer�ffnung / PortalConfig
     getWelcomeTextAsHTML()           ->einleitender Text
     ************************************
     */

      /*
      **********************************
      ********auszuwertende Variablen***
      **********************************
      */

      /**********Formular**********/
      $get_vars  = $this->_environment->getCurrentParameterArray();
      $post_vars = $this->_environment->getCurrentPostParameterArray();
      if (!empty($get_vars['cs_modus'])) {
         $cs_mod = $get_vars['cs_modus'];
      } elseif (!empty($post_vars['cs_modus'])) {
         $cs_mod = $post_vars['cs_modus'];
      } else {
         $cs_mod = '';
      }

     /********Konfiguration********/
      $cs_module = $this->_environment->getCurrentModule();
      $cs_function = $this->_environment->getCurrentFunction();

      /**************Suche***********/
      if (!empty($get_vars['search'])) {
         $cs_search = $get_vars['search'];
      } elseif (!empty($post_vars['search'])) {
         $cs_search = $post_vars['search'];
      } else {
         $cs_search = '';
      }

      /*************Detailansicht************/
      if (!empty($get_vars['room_id'])) {
         $cs_room_id = $get_vars['room_id'];
      } elseif (!empty($post_vars['room_id'])) {
         $cs_room_id = $post_vars['room_id'];
      } else {
         $cs_room_id = '';
      }

      if (!empty($get_vars['iid'])) {
         $cs_iid = $get_vars['iid'];
      } elseif (!empty($post_vars['iid'])) {
         $cs_iid = $post_vars['iid'];
      } else {
         $cs_iid = '';
      }

      unset($get_vars);
      unset($post_vars);

      $html = '';
      $html .= $this->_getHTMLHeadAsHTML();

      $lang = $this->_environment->getSelectedLanguage();
      $session_item = $this->_environment->getSessionItem();
      $current_user = $this->_environment->getCurrentUserItem();
      if ( $current_user->isUser() ) {
      	$lang = $current_user->getLanguage();
      } else {
      	$session_item = $this->_environment->getSessionItem();
      	$lang = $session_item->getValue('message_language_select');
      }
      $params = array();
      
      
    $html .= '<body class="tundra">'.LF;


    $html .= $this->_getCommSyBarBeforeContentAsHTML();
    if ($current_user->isUser()){
    	
    }else{
    	
    	if (!( ($cs_iid and $cs_module == 'configuration') or
    	($cs_module == 'configuration') or
    	($cs_module == 'project') or
    	($cs_module == 'community')or
    	($cs_module == 'mail')or
    	($cs_module == 'account') or
    	isset($this->_agb_view)
    	)){
    		   	$html .= $this->getMyAreaAsHTML();
       	}    	
    	
    }
    
$html .='

<!-- =========== LOGOS ============ -->
<div id="logo-wrapper" class="wrapper">
  <div id="logo-container" class="container_24">
    <div class="grid_24 invisible" id="site-name-slogan">
      <hgroup class="invisible">        
        <h2 class="invisible" id="site-name"><a title="Startseite" href="/startseite">AGORA</a></h2>
        <h6 class="invisible" id="slogan">e-Plattform für die Hamburger Geisteswissenschaften</h6>
      </hgroup>
    </div>
    <!-- end .grid_24 -->
  <div class="clear"></div>'.LF;
$lang = $this->_environment->getSelectedLanguage();

$external_link_url= 'http://www.agora.uni-hamburg.de';
$session = $this->_environment->getSessionItem();
$language_addon = '';
if ($session->issetValue('message_language_select')){
	$lang = $session->getValue('message_language_select');
	if ($lang == 'en'){
		$language_addon = '_en';
	}
}elseif($current_user->isUser()){
	$lang = $current_user->getLanguage();
	if ($lang == 'en'){
		$language_addon = '_en';
	}
}

if ( $lang == 'en' ) {
	$html .='
    <div class="grid_5">
      <a title="Universität Hamburg" href="http://www.uni-hamburg.de/"><img title="Universität Hamburg" src="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-uhh.png" alt="logo"></a>
    </div>
	
   <!-- end .grid_5 -->
    <div class="grid_19">
      <a title="AGORA-Homepage" href="'.$external_link_url.'/en"><img title="AGORA" src="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-agora-en.png" alt="logo"></a>
	</div>
    <!-- end .grid_19 -->
  </div>'.LF;
	
}else{
	$html .='    		
    <div class="grid_5">
      <a title="Universität Hamburg" href="http://www.uni-hamburg.de/"><img title="Universität Hamburg" src="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-uhh.png" alt="logo"></a>
    </div>

   <!-- end .grid_5 -->
    <div class="grid_19">
      <a title="AGORA-Startseite" href="'.$external_link_url.'/"><img title="AGORA" src="css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-agora.png" alt="logo"></a>
	</div>
    <!-- end .grid_19 -->
  </div>'.LF;
}
    

$html .='  <!-- end #logo-container -->
</div>
<!-- end #logo-wrapper -->
<!-- ========= END LOGOS ========== -->

<!-- ============ CONTENT ============ -->
<div id="content-wrapper" class="wrapper">
  <div id="content-container" class="container_24">
    <!-- 1. Sidebar -->
    <div id="sidebar-first" class="sidebar grid_5">
      <div id="menu-main" class="block">
        '.LF;
    

if ( $lang == 'en' ) {
	$html .='        <h2>Menu</h2><ul class="menu">
  <li class="first leaf menu-mlid-646"><a href="'.$external_link_url.'/en" title="AGORA homepage, News">Homepage</a></li>
  <li class="leaf active-trail active menu-mlid-585">'.ahref_curl($this->_environment->getCurrentContextID(), 'home', 'index', '','Workspaces','List of AGORA workspaces','','','','','','class="active-trail active"').'</li>
  <li class="expanded menu-mlid-649"><a href="'.$external_link_url.'/en/usage-information" title="First steps, FAQ, Tutorials">Usage information</a>
    <ul class="menu">
      <li class="first leaf menu-mlid-666"><a href="'.$external_link_url.'/en/usage-information/first-steps" title="First steps on AGORA">First steps</a></li>
      <li class="leaf menu-mlid-670"><a href="'.$external_link_url.'/en/usage-information/tutorials" title="Docs about the usage of AGORA">Tutorials</a></li>
      <li class="leaf menu-mlid-707"><a href="'.$external_link_url.'/en/usage-information/faq" title="Frequently Asked Questions">FAQ</a></li>
      <li class="leaf menu-mlid-667"><a href="'.$external_link_url.'/en/usage-information/glossary" title="Important AGORA terms and their meaning">Glossary</a></li>
      <li class="leaf menu-mlid-669"><a href="'.$external_link_url.'/en/usage-information/tips-of-the-month" title="Tips how to use AGORA">Tips of the month</a></li>
      <li class="leaf menu-mlid-671"><a href="'.$external_link_url.'/en/usage-information/workshops" title="Workshops for students and lecturers">Workshops</a></li>
    </ul>
  </li>
  <li class="collapsed menu-mlid-672"><a href="'.$external_link_url.'/en/about-agora" title="Concept, features, and academic activities">About AGORA</a></li>
  <li class="last collapsed menu-mlid-676"><a href="'.$external_link_url.'/en/contact" title="Contact details, office hours, contact form">Contact</a></li>
						 <li>'.$this->_getFlagsAsHTML().'
	    		
	</ul>'.LF;
}else{   
	$html .='        <h2>Menü</h2><ul class="menu">
  <li class="first leaf menu-mlid-218"><a title="AGORA-Homepage, Aktuelle Meldungen" href="'.$external_link_url.'/">Startseite</a></li>
  <li class="leaf active-trail active menu-mlid-585">'.ahref_curl($this->_environment->getCurrentContextID(), 'home', 'index', '','Raumübersicht','Liste der Projekt- und Gemeinschaftsräume','','','','','','class="active-trail active"').'</li>
  <li class="expanded menu-mlid-555"><a title="Erste Schritte, FAQ, Tutorials" href="'.$external_link_url.'/hilfe-bei-der-nutzung">Hilfe bei der Nutzung</a>
    <ul class="menu">
      <li class="first leaf menu-mlid-651"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/erste-schritte" title="Erste Schritte mit AGORA">Erste Schritte</a></li>
      <li class="leaf has-children menu-mlid-655"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/tutorials" title="Dokumente zur Handhabung von AGORA">Tutorials</a></li>
      <li class="leaf menu-mlid-488"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/faq" title="Frequented Asked Questions">FAQ</a></li>
      <li class="leaf active-trail active menu-mlid-652"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/glossar" title="Wichtige AGORA-Begriffe und was sie bedeuten">Glossar</a></li>
      <li class="leaf menu-mlid-654"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/tipps-des-monats" title="Tipps zur Arbeit mit AGORA">Tipps des Monats</a></li>
      <li class="last leaf menu-mlid-656"><a href="'.$external_link_url.'/hilfe-bei-der-nutzung/workshops" title="AGORA-Workshops für Lehrende und Studierende">Workshops</a></li>
    </ul>
  </li>
  <li class="collapsed menu-mlid-573"><a title="Konzept, Funktionen und wissenschaftliche Arbeit von AGORA" href="'.$external_link_url.'/ueber-agora">Über AGORA</a></li>
  <li class="last collapsed menu-mlid-580"><a title="Kontaktdaten, Sprechstunden, Kontaktformular" href="'.$external_link_url.'/kontakt">Kontakt</a></li>
						 <li>'.$this->_getFlagsAsHTML().'
	   
	</ul>'.LF;
}

	$lang = $this->_environment->getSelectedLanguage();

    $session = $this->_environment->getSessionItem();
    $current_user = $this->_environment->getCurrentUserItem();
    $language_addon = '';
    if ($session->issetValue('message_language_select')){
    	$lang = $session->getValue('message_language_select');
    	if ($lang == 'en'){
    		$html .='<div style="background-image:url(\'css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-agora_eng.gif\')">'.LF;
    	}else{
    		$html .='<div >'.LF;
    	}
    }elseif($current_user->isUser()){
    	$lang = $current_user->getLanguage();
    	if ($lang == 'en'){
    		$html .='<div style="background-image:url(\'css/external_portal_styles/'.$this->_environment->getCurrentContextID().'/img/logo-agora_eng.gif\')">'.LF;
    	}else{
    		$html .='<div >'.LF;
    	}
    }else{
    	$html .='<div>'.LF;
    }
    $html .='</div>'.LF;
    
    
    $lang = $this->_environment->getSelectedLanguage();
    
    $session = $this->_environment->getSessionItem();
    $language_addon = '';
    if ($session->issetValue('message_language_select')){
    	$lang = $session->getValue('message_language_select');
    	if ($lang == 'en'){
    		$language_addon = '_en';
    	}
    }elseif($current_user->isUser()){
    	$lang = $current_user->getLanguage();
    	if ($lang == 'en'){
    		$language_addon = '_en';
    	}
    }
    
    
    
$html .='      </div>'.LF;

if ( $lang == 'en' ) {
	$html .='<div id="kontakt" class="block">
        <h2>Contact</h2>
        <h3>Office Hours</h3>
       <p>Tue 11:30&ndash;12:30 and<br />
	      Thu 13:30&ndash;14:30,<br />
	      Phil-Turm (VMP 6), R. 1212,<br />
	      Phone 040 / 4 28 38-39 71</p>
       <h3>E-Mail_</h3>
       <p><a href="mailto:agora@uni-hamburg.de">agora@uni-hamburg.de</a></p>
       <h3>Postal adress</h3>
       <p>AGORA-Team<br />
	      Universität Hamburg<br />
	      c/o Institut für Germanistik II<br />
	      Von-Melle-Park 6<br />
	      20146 Hamburg</p>
      </div>
      <div id="status" class="block">
        <h2>Status</h2>
        <p><img src="http://appmon.rrz.uni-hamburg.de/appmon/status.php?app=commsy&amp;size=24">&nbsp; Availability<br />
	      (<a title="Availibility of AGORA (CommSy)" href="http://www.rrz.uni-hamburg.de/index.php?id=1874">What means availability</a>)</p>
      </div>'.LF;
	
}else{
$html .='<div id="kontakt" class="block">
        <h2>Kontaktdaten</h2>
        <h3>Sprechstunden</h3>
       <p>DI 11:30&ndash;12:30 Uhr und<br />
	      DO 13:30&ndash;14:30 Uhr,<br />
	      Phil-Turm (VMP 6), R. 1212,<br />
	      Tel. 040 / 4 28 38-39 71</p>
       <h3>eMail-Adresse</h3>
       <p><a href="mailto:agora@uni-hamburg.de">agora@uni-hamburg.de</a></p>
			<h3>Postadresse</h3>
       <p>AGORA-Team<br />
	      Universität Hamburg<br />
	      c/o Institut für Germanistik II<br />
	      Von-Melle-Park 6<br />
	      20146 Hamburg</p>
      </div>
      <div id="status" class="block">
        <h2>Status</h2>
        <p><img src="http://appmon.rrz.uni-hamburg.de/appmon/status.php?app=commsy&amp;size=24">&nbsp; Verfügbarkeit<br />
	      (<a title="Verfügbarkeit von AGORA (CommSy) am RRZ - Link führt zur Legende der Icons" href="http://www.rrz.uni-hamburg.de/index.php?id=1874">Was bedeutet das?</a>)</p>
      </div>'.LF;
}

$html .='
    </div>
    <!-- end #sidebar-first -->'.LF;
    

		
		
		


if ( ($cs_iid and $cs_module == 'configuration') or
($cs_module == 'configuration') or
($cs_module == 'project') or
($cs_module == 'community')or
($cs_module == 'mail')or
($cs_module == 'account') or
isset($this->_agb_view)
){
$html .='<!-- Main Content -->
    <div id="main-content" class="grid_14" style="width:740px;">
      <h1 id="page-title" class="invisible">'.$this->_translator->getMessage('COMMON_ROOM_OVERWIEW').'</h1>'.LF;
	$html .='<div id="my-agora" class="block">'.LF;

	if (($cs_module == 'project') or ($cs_module == 'community') or ($cs_module == 'configuration' and $cs_function =='common')){
		if(isset($_GET['iid']) and $_GET['iid'] != 'NEW'){
			$html .= '<h2>'.$this->_translator->getMessage('PORTAL_EDIT_ROOM').'</h2>'.LF;
		}else{
			$html .= '<h2>'.$this->_translator->getMessage('PORTAL_ENTER_ROOM').'</h2>'.LF;
		}
	}
	$html .='</div>'.LF;
	
}else{

$html .='<!-- Main Content -->
    <div id="main-content" class="grid_14">
      <h1 id="page-title" class="invisible">Raumübersicht</h1>'.LF;
	$html .='<div id="raumsuche" class="block" style="margin-bottom:20px;">'.LF;

	$html .= '<h2>'.$this->_translator->getMessage('COMMON_ROOM_SEARCH').'</h2>'.LF;
	$html .= $this->getSearchBoxAsHTML().LF;
	$html .='</div>'.LF;

	
	
	
	$html .='<div id="raumsuche" class="block">'.LF;
	
	if ($cs_room_id) {
		$html .= '<h2>'.$this->_translator->getMessage('PORTAL_ROOM_DESCRIPTION').'</h2>'.LF;
	}
	
}


   if ( ($cs_iid and $cs_module == 'configuration') or
      ($cs_module == 'configuration') or
      ($cs_module == 'project') or
      ($cs_module == 'community')or
      ($cs_module == 'mail')or
      ($cs_module == 'account') or
      isset($this->_agb_view)
){
$html .='<a id="content" name="content"></a> <!-- Skiplink-Anker: Content -->
 <!-- #col3: Statische Spalte des Inhaltsbereiches (mitte) -->
 '.LF;

  if (($cs_module == 'project') or ($cs_module == 'community') or ($cs_module == 'configuration' and $cs_function =='common')){
      if(isset($_GET['iid']) and $_GET['iid'] != 'NEW'){
#         $html .= '<h2>'.$this->_translator->getMessage('PORTAL_EDIT_ROOM').'</h2>'.LF;
      }else{
#         $html .= '<h2>'.$this->_translator->getMessage('PORTAL_ENTER_ROOM').'</h2>'.LF;
      }
  }

}


      $show_list = true;
      if ( isset($this->_agb_view) ) {
         $html .= $this->_getAGBViewAsHTML().LF;
      }
      elseif ($cs_room_id and $cs_module == 'configuration' ){
         $room_manager = $this->_environment->getRoomManager();
         $room_item = $room_manager->getItem($cs_room_id);
         $html .= $this->_getRoomFormAsHTML($room_item);
         $show_list = false;
      } elseif ($cs_module == 'mail' and $this->_environment->getCurrentFunction() == 'to_moderator'){
         $html .= $this->_getModeratorMailTextAsHTML();
         $show_list = false;
      } elseif ($cs_module == 'configuration'
                or $cs_module == 'account'
                or ($cs_module == 'mail' and $cs_function == 'process')
                or ($cs_module == 'project' and $cs_function == 'edit' )
                or ($cs_module == 'community' and $cs_function == 'edit')
               ){
         if ($cs_module == 'account'){
            $html .='<div style="background-color:white; width:700px; font-size:8pt;">';
            $html .= $this->getConfigurationAsHTML();
            $html .= '</div>';
         }else{
            $html .= $this->getConfigurationAsHTML();
         }
         $show_list = false;
      } elseif ( $cs_module == 'language' ) {
         $html .= $this->_getLanguageIndexAsHTML();
         $show_list = false;
      } elseif ($cs_room_id) {
         $room_manager = $this->_environment->getRoomManager();
         $room_item = $room_manager->getItem($_GET['room_id']);
         if ( isset($room_item) ) {
            $html .= $this->getRoomItemAsHTML($room_item);
         }
      }

      if ($show_list){
		$html .= '<h2>'.$this->_translator->getMessage('PORTAL_ROOM_OVERVIEW').'</h2>'.LF;
      	$html .= $this->getContentListAsHTML();
      }

      $html .='</div>'.LF;
      






$html.='  
    </div>
    <!-- end #main-content -->'.LF;




if ( !(($cs_iid and $cs_module == 'configuration') or
($cs_module == 'configuration') or
($cs_module == 'project') or
($cs_module == 'community')or
($cs_module == 'mail')or
($cs_module == 'account') or
isset($this->_agb_view)
)){


$html .='
    
    <!-- 2. Sidebar -->
    <div id="sidebar-second" class="sidebar grid_5">'.LF;

    $current_user = $this->_environment->getCurrentUser();
    $params = $this->_environment->getCurrentParameterArray();
    if ($current_user->isUser()){

		$html .='		<div id="news" class="block">'.LF;
		$html .= $this->_getMyCommSyAsHTML();
		$html .='	</div>'.LF;	

	
		$html .='		<div id="news" class="block">'.LF;
			if ( $lang == 'en' ) {
				$html .='<h2>Create workspaces</h2><div style="padding:10px 5px; white-space:nowrap;">';
				$html.= '- <a href="commsy.php?cid='.$this->_environment->getCurrentPortalID().'&mod=project&fct=edit&iid=NEW">New project workspace</a>'.BRLF;
				if ($current_user->isModerator()){
					$html.= '- <a href="commsy.php?cid='.$this->_environment->getCurrentPortalID().'&mod=community&fct=edit&iid=NEW">New community workspace</a></div>'.LF;
				}
		}else{
			$html .='<h2>Raum anlegen</h2><div style="padding:10px 5px; white-space:nowrap;">';
			$html.= '- <a href="commsy.php?cid='.$this->_environment->getCurrentPortalID().'&mod=project&fct=edit&iid=NEW">Neuer Projektraum</a>'.BRLF;
			if ($current_user->isModerator()){
				$html.= '- <a href="commsy.php?cid='.$this->_environment->getCurrentPortalID().'&mod=community&fct=edit&iid=NEW">Neuer Gemeinschaftsraum</a></div>'.LF;
				$html.= $this->_getConfigurationBoxAsHTML();
			}
		}
		$html .='	</div>'.LF;	


	}
$html .='	<div id="news" class="block">'.LF;








$html .= $this->_getServerNewsAsHTML();

	$html.='
      </div><div style="margin-top:40px;">'.$this->_getSystemInfoAsHTML().'</div>
    </div>
    <!-- end #sidebar-second -->'.LF;
}	


$html .= '';

if ( $lang == 'en' ) {
$html.= '  </div>
  <!-- end #content-container -->
</div>
<!-- end #content-wrapper -->
<!-- ========== END CONTENT ========== -->

<!-- ============ FOOTER ============ -->
<div id="footer-wrapper" class="wrapper">
  <div id="footer-container" class="container_24">
    <div class="grid_24">
      <div id="menu-footer">
        <ul class="menu">
  		<li class="first leaf menu-mlid-218"><a title="AGORA homepage, News" href="'.$external_link_url.'/en">Homepage</a></li>
          <li class="leaf active-trail active menu-mlid-585">'.ahref_curl($this->_environment->getCurrentContextID(), 'home', 'index', '','Workspaces','List of AGORA workspaces','','','','','','class="active-trail active"').'</li>
          <li class="leaf has-children menu-mlid-555"><a title="First steps, FAQ, Tutorials" href="'.$external_link_url.'/en/usage-information">Usage information</a></li>
          <li class="leaf has-children menu-mlid-573"><a title="Concept, features, and academic activities" href="'.$external_link_url.'/en/about-agora">About AGORA</a></li>
          <li class="last leaf has-children menu-mlid-580"><a title="Contact details, office hours, contact form" href="'.$external_link_url.'/en/contact">Contact</a></li>
        </ul>
      </div>
    </div>
    <!-- end .grid_24 -->
    <div class="clear"></div>
    <div class="grid_24">
      <div id="footer-info">AGORA &middot; e-platform for the Humanities at Hamburg University &middot; <a href="mailto:agora@uni-hamburg.de">agora@uni-hamburg.de</a></div>
    </div>
    <!-- end .grid_24 -->
  </div>
  <!-- end #footer-container -->
</div>
<!-- end #footer-wrapper -->
<!-- ========== END FOOTER ========== -->
    ';

}else{
	$html.= '  </div>
  <!-- end #content-container -->
</div>
<!-- end #content-wrapper -->
<!-- ========== END CONTENT ========== -->
	
<!-- ============ FOOTER ============ -->
<div id="footer-wrapper" class="wrapper">
  <div id="footer-container" class="container_24">
    <div class="grid_24">
      <div id="menu-footer">
        <ul class="menu">
          <li class="first leaf menu-mlid-218"><a title="AGORA-Homepage, Aktuelle Meldungen" href="'.$external_link_url.'/">Startseite</a></li>
          <li class="leaf active-trail active menu-mlid-585">'.ahref_curl($this->_environment->getCurrentContextID(), 'home', 'index', '','Raumübersicht','Liste der Projekt- und Gemeinschaftsräume','','','','','','class="active-trail active"').'</li>
          <li class="leaf has-children menu-mlid-555"><a title="Erste Schritte, FAQ, Tutorials" href="'.$external_link_url.'/hilfe-bei-der-nutzung">Hilfe bei der Nutzung</a></li>
          <li class="leaf has-children menu-mlid-573"><a title="Konzept, Funktionen und wissenschaftliche Arbeit von AGORA" href="'.$external_link_url.'/ueber-agora">Über AGORA</a></li>
          <li class="last leaf has-children menu-mlid-580"><a title="Kontaktdaten, Sprechstunden, Kontaktformular" href="'.$external_link_url.'/kontakt">Kontakt</a></li>
        </ul>
      </div>
    </div>
    <!-- end .grid_24 -->
    <div class="clear"></div>
    <div class="grid_24">
      <div id="footer-info">AGORA &middot; e-Plattform für die Hamburger Geisteswissenschaften &middot; <a href="mailto:agora@uni-hamburg.de">agora@uni-hamburg.de</a></div>
    </div>
    <!-- end .grid_24 -->
  </div>
  <!-- end #footer-container -->
</div>
<!-- end #footer-wrapper -->
<!-- ========== END FOOTER ========== -->
    ';	
}














      $html .= '</body>'.LF;
      return $html;
   }
   
   function _getConfigurationBoxAsHTML () {
   	$current_context = $this->_environment->getCurrentContextItem();
   	$html  = '';
   	$html .= '<div class="search_link">'.LF;
   	$html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','index','',$this->_translator->getMessage('PORTAL_CONFIGURATION_ACTION')).BRLF;
   	$html .= '</div>'.LF;
   
   	// tasks
   	$manager = $this->_environment->getTaskManager();
   	$manager->resetLimits();
   	$manager->setContextLimit($this->_environment->getCurrentContextID());
   	$manager->setStatusLimit('REQUEST');
   	$manager->select();
   	$task_list = $manager->get();
   	if ( !$task_list->isEmpty() ) {
   		$task_item = $task_list->getFirst();
   		while ( $task_item ) {
   			$ref_item = $task_item->getLinkedItem();
   			$html .= '<div class="search_link" style="margin-top:10px;">'.LF;
   			$html .= $this->_translator->getMessage('PORTAL_ROOM_MOVE_REQUESTED').': '.$ref_item->getTitle().LF;
   			$html .= '</div>'.LF;
   			$params = array();
   			$params['iid'] = $ref_item->getItemID();
   			$params['tid'] = $task_item->getItemID();
   			$params['modus'] = 'agree';
   			$html .= '<div class="search_link">'.LF;
   			$html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','move2',$params,$this->_translator->getMessage('COMMON_ACCEPT')).BRLF;
   			$html .= '</div>'.LF;
   			$params['modus'] = 'reject';
   			$html .= '<div class="search_link">'.LF;
   			$html .= '> '.ahref_curl($this->_environment->getCurrentContextID(),'configuration','move2',$params,$this->_translator->getMessage('COMMON_REJECT'));
   			$html .= '</div>'.LF;
   			unset($params);
   
   			$task_item = $task_list->getNext();
   		}
   		$html .='</div>'.LF;
   	}
   
   
   	return $html;
   }


   private function _getFlagsAsHTML () {
      $html = '';
      $lang = $this->_environment->getSelectedLanguage();
      $session_item = $this->_environment->getSessionItem();
      $current_user = $this->_environment->getCurrentUserItem();
      if ( $current_user->isUser() ) {
         $lang = $current_user->getLanguage();
      } else {
         $session_item = $this->_environment->getSessionItem();
         $lang = $session_item->getValue('message_language_select');
      }
      $params = array();
      if ( $lang == 'en' ) {
         $flag_lang = 'de';
         $link_lang = 'de';
         $params['language'] = $link_lang;
         $html .= '<div style=" border-top:1px solid #DDDDDD; border-bottom:1px solid #DDDDDD;">'.ahref_curl($this->_environment->getCurrentContextID(),'language','change',$params,'Deutsch <img src="images/flags/'.$flag_lang.'.gif"  alt="'.$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG').'"/>',$this->_translator->getMessageInLang('de','COMMON_CHANGE_LANGUAGE_WITH_FLAG')).'</div>'.LF;
      }  else {
         $flag_lang = 'gb';
         $link_lang = 'en';
         $params['language'] = $link_lang;
         $html .= '<div style="border-top:1px solid #DDDDDD; border-bottom:1px solid #DDDDDD;">'.ahref_curl($this->_environment->getCurrentContextID(),'language','change',$params,'English <img src="images/flags/'.$flag_lang.'.gif" alt="'.$this->_translator->getMessageInLang($link_lang,'COMMON_CHANGE_LANGUAGE_WITH_FLAG').'"/>',$this->_translator->getMessageInLang('en','COMMON_CHANGE_LANGUAGE_WITH_FLAG')).'</div>'.LF;
      }
      unset($params);
      return $html;
   }

   function getProfileBoxAsHTML(){
      $html = '';
      global $profile_view;
      if ( !empty($profile_view) ) {
         $environment = $this->_environment;
         $html  = '<div style="position:absolute; left:0px; top:0px; z-index:1000; width:100%; height: 100%;">'.LF;
         $html .= '<div style="z-index:1000; margin-top:40px; margin-bottom:0px; margin-left: 20%; width:60%; text-align:left; background-color:#FFFFFF;">';
         $html .= $profile_view->asHTML();
         $html .= '</div>';
         $html .= '</div>';
         $html .= '<div id="profile" style="position: absolute; left:0px; top:0px; z-index:900; width:100%; height: 850px; background-color:#FFF; opacity:0.7; filter:Alpha(opacity=70);">'.LF;
         $html .= '</div>';
      }
      return $html;
   }

   private function _getMyCommSyAsHTML () {
      $retour = '';
      $current_user = $this->_environment->getCurrentUserItem();
      if ( $current_user->isUser()
           and !$current_user->isRoot()
         ) {
         $retour .= '<h2>myAGORA</h2>'.LF;
         $retour .='<div style="padding:10px 5px; background-color:#FFFFFF; margin-bottom:20px;">';
   		 $currentUser = $this->_environment->getCurrentUserItem();
   		 $translator = $this->_environment->getTranslationObject();
   		 $ownRoomItem = $currentUser->getOwnRoom();
   		 
   		 if ($this->_environment->InPortal() && !$currentUser->isGuest()) {
         	$retour .= '- <a href="#" onclick="document.getElementById(\'tm_user\').click();" title="' . $this->_translator->getMessage("MYAREA_ACCOUNT_PROFIL") . '">'.$this->_translator->getMessage("MYAREA_ACCOUNT_PROFIL").'</a><br/>';
   		 	
    		   // portal2portal
   			if ( !empty($ownRoomItem)
   				   and $ownRoomItem->showCSBarConnection()
   				) {
         		$retour .= '- <a href="#" onclick="document.getElementById(\'tm_connection\').click();" title="' . $this->_translator->getMessage("CS_BAR_CONNECTION") . '">'.$this->_translator->getMessage("CS_BAR_CONNECTION").'</a><br/>';
   			}
   				
         	if ( $ownRoomItem->getCSBarShowPortfolio() == "1" )
         	{
         		$retour .= '- <a href="#" onclick="document.getElementById(\'tm_portfolio\').click();" title="' . $this->_translator->getMessage("CS_BAR_PORTFOLIO") . '">'.$this->_translator->getMessage("CS_BAR_PORTFOLIO").'</a><br/>';
         	}
         	
         	if ( $ownRoomItem->getCSBarShowWidgets() == "1" )
         	{
         		$retour .= '- <a href="#" onclick="document.getElementById(\'tm_widgets\').click();" title="' . $this->_translator->getMessage("MYWIDGETS_INDEX") . '">'.$this->_translator->getMessage("MYWIDGETS_INDEX").'</a><br/>';
         	}
         	
         	if ( $ownRoomItem->getCSBarShowCalendar() == "1" )
         	{
         		$retour .= '- <a href="#" onclick="document.getElementById(\'tm_mycalendar\').click();" title="' . $this->_translator->getMessage("MYCALENDAR_INDEX") . '">'.$this->_translator->getMessage("MYCALENDAR_INDEX").'</a><br/>';
         	}
         	
         	if ( $ownRoomItem->getCSBarShowStack() == "1" )
         	{
         		$retour .= '- <a href="#" onclick="document.getElementById(\'tm_stack\').click();" title="' . $this->_translator->getMessage("CS_BAR_STACK") . '">'.$this->_translator->getMessage("CS_BAR_STACK").'</a>';
         	}
         	
         	$retour .= '- <a href="#" onclick="document.getElementById(\'tm_clipboard\').click();" title="' . $this->_translator->getMessage("MYAREA_MY_COPIES") . '">'.$this->_translator->getMessage("MYAREA_MY_COPIES").'</a>';
   		 }
   		 $retour .='</div>';
          
      }
      return $retour;
   }

   private function _getPrivateRoomLinkAsHTML () {
      $retour = '';
      $user = $this->_environment->getCurrentUserItem();
      if ( !$user->isRoot()
           and $user->isUser()
         ) {
         $private_room_manager = $this->_environment->getPrivateRoomManager();
         $own_room = $private_room_manager->getRelatedOwnRoomForUser($user,$this->_environment->getCurrentPortalID());
         global $c_annonymous_account_array;
         if ( isset($own_room)
              and empty($c_annonymous_account_array[strtolower($user->getUserID()).'_'.$user->getAuthSource()])
            ) {
            $current_portal_item = $this->_environment->getCurrentPortalItem();
            if ( $current_portal_item->showAllwaysPrivateRoomLink() ) {
               $link_active = true;
            } else {
               if ( $user->isRoomMember() ) {
                  $link_active = true;
               } else {
                  $link_active = false;
               }
            }
            unset($current_portal_item);
            if ($link_active) {
               $retour .= '<span> '.ahref_curl($own_room->getItemID(), 'home',
                                'index',
                                '',
                                '<img src="images/door_open_small.gif" style="vertical-align: middle" alt="door open"/>','','','','','','','style="display:inline;"').LF;

               $retour .= ahref_curl($own_room->getItemID(), 'home', 'index', '',$this->_translator->getMessage('EXTERNALMESSAGES_PORTAL_MYROOM_LINK'),'','','','','','','style="display:inline;"').'</span>'.BRLF;
            } else {
               // disable private room
               $retour .= '<span class="disabled"><img src="images/door_closed_small.gif" style="vertical-align: middle" alt="door close"/>'.LF;
               $retour .= $this->_translator->getMessage('EXTERNALMESSAGES_PORTAL_MYROOM_LINK').'</span>'.BRLF;
            }
         }
         unset($own_room);
      }
      unset($user);
      return $retour;
   }

   private function _getProfileLinkAsHTML () {
      $retour = '';
      $params = array();
      $params = $this->_environment->getCurrentParameterArray();
      $params['uid'] = $this->_current_user->getItemID();
      $params['show_profile'] = 'yes';
      unset($params['is_saved']);
      unset($params['show_copies']);
      unset($params['profile_page']);
      global $c_annonymous_account_array;
      if ( empty($c_annonymous_account_array[mb_strtolower($this->_current_user->getUserID(), 'UTF-8').'_'.$this->_current_user->getAuthSource()])
           and !$this->_current_user->isOnlyReadUser()
         ) {
         $retour .= '<span>'.ahref_curl($this->_environment->getCurrentContextID(), $this->_environment->getCurrentModule(), $this->_environment->getCurrentFunction(), $params,$this->_translator->getMessage('EXTERNALMESSAGES_PORTAL_PROFILE_LINK'),'','','','','','','style="display:inline;"').'</span>'.BRLF;
      } else {
         $retour .= '<span class="disabled">'.$this->_translator->getMessage('EXTERNALMESSAGES_PORTAL_PROFILE_LINK').'</span>'.BRLF;
      }
      return $retour;
   }

   private function _getRoomListHeadingAsHTML () {
      $retour = $this->_translator->getMessage('PORTAL_ROOM_OVERVIEW');
      if ( isset($this->_room_list_view) ) {
         $selroom = $this->_room_list_view->getSelectedRoom();
      } else {
         $selroom = '';
      }
      if ( !empty($selroom) ) {
         if ( $selroom == 1 ) {
            $retour = $this->_translator->getMessage('COMMON_NO_SELECTION');
         } elseif ( $selroom == 9 ) {
            $retour = $this->_translator->getMessage('PORTAL_DELETED_ROOMS');
         } elseif ( $selroom == 3 ) {
            $retour = $this->_translator->getMessage('COMMON_PROJECT_PL');
         } elseif ( $selroom == 4 ) {
            $retour = $this->_translator->getMessage('COMMON_COMMUNITY_PL');
         } elseif ( $selroom == 6 ) {
            $retour = $this->_translator->getMessage('GROUPROOM_PORTAL_SELECT_TITLE');
         } elseif ( $selroom == 5 ) {
            $retour = $this->_translator->getMessage('PORTAL_MY_ROOMS');
         }
      } else {
         $retour = $this->_translator->getMessage('COMMON_NO_SELECTION');
      }
      return $retour;
   }
}
?>