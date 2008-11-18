<?php
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2007 Dirk Bl�ssl, Matthias Finck, Dirk Fust, Franz Gr�nig,
// Oliver Hankel, Iver Jackewitz, Michael Janneck, Martti Jeenicke,
// Detlev Krause, Irina L. Marinescu, Frithjof Meyer, Timo Nolte, Bernd Pape,
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

$this->includeClass(VIEW);
include_once('functions/date_functions.php');

/**
 *  generic upper class for CommSy homepage-views
 */
class cs_home_usageinfo_view extends cs_view {

var $_config_boxes = false;

   function cs_home_usageinfo_view ($params) {
      $this->cs_view($params);
      $this->setViewName('usageinfos');
      $user = $this->_environment->getCurrentUserItem();
      $room = $this->_environment->getCurrentContextItem();
      $this->_view_title = $room->getUsageInfoHeaderForRubric($this->_environment->getCurrentModule());
      $rubric_info_array = $room->getUsageInfoArray();
      if (!is_array($rubric_info_array)) {
         $rubric_info_array = array();
      }
   }


   function asHTML () {
     $html  = '';
     $current_context = $this->_environment->getCurrentContextItem();
     $current_user = $this->_environment->getCurrentUserItem();
     $room = $this->_environment->getCurrentContextItem();
     $rubric_info_array = $room->getUsageInfoArray();
#     if (!(in_array($this->_environment->getCurrentModule().'_no', $rubric_info_array)) and $current_user->isUser() ){
         $html .= $this->_getRubricInfoAsHTML($this->_environment->getCurrentModule());
#     }
     return $html;
   }

  function _getRubricInfoAsHTML($act_rubric){
      $html='';
      $room = $this->_environment->getCurrentContextItem();
      $info_text = $room->getUsageInfoTextForRubric($act_rubric);
      $html .= '<div style="margin-top:0px;">'.LF;
      $html .= '<div style="position:relative; top:12px;">'.LF;
      $html .= '<img src="images/commsyicons/usage_info_3.png"/>';
      $html .= '</div>'.LF;
      $html .= '<div class="right_box_title" style="font-weight:bold;">'.getMessage('PREFERENCES_USAGE_INFOS').'</div>';
      $html .= '<div class="usage_info">'.LF;
      $html .= $this->_text_as_html_long($info_text).BRLF;
      $html .= '</div>'.LF;
      $html .= '</div>'.LF;
      return $html;
   }

}
?>