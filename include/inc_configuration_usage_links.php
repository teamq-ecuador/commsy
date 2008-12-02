<?PHP
// $Id$
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

include_once('classes/cs_link.php');
include_once('classes/cs_list.php');

$usage_link_list = new cs_list();

if ( !isset($environment) and isset($this->_environment) ) {
   $environment = $this->_environment;
}

   if ( !$environment->inServer() and !$environment->inPrivateRoom()) {
      $link_item = new cs_link();
      $link_item->setDescription(getMessage('ROOM_MEMBER_ADMIN_DESC'));
      $link_item->setIconPath('images/commsyicons/48x48/config/account.png');
      $link_item->setTitle(getMessage('ROOM_MEMBER_ADMIN'));
      $link_item->setContextID($environment->getCurrentContextID());
      $link_item->setModule('account');
      $link_item->setFunction('index');
      $link_item->setParameter(array());
      $usage_link_list->add($link_item);
   }



   if ( !$environment->inServer() and !$environment->inPrivateRoom() and !$environment->inPortal()) {
      $link_item = new cs_link();
      $link_item->setTitle(getMessage('COMMON_INFORMATION_BOX'));
      $link_item->setShortTitle(getMessage('COMMON_INFORMATION_BOX_SHORT'));
      $link_item->setDescription(getMessage('COMMON_INFORMATION_BOX_DESC'));
      $link_item->setIconPath('images/commsyicons/48x48/config/informationbox.png');
      $link_item->setContextID($environment->getCurrentContextID());
      $link_item->setModule('configuration');
      $link_item->setFunction('informationbox');
      $link_item->setParameter('');
      $usage_link_list->add($link_item);
   }



   $context_item = $environment->getCurrentContextItem();
   if ( $context_item->isCommunityRoom()
        and $context_item->isOpenForGuests()
        and $context_item->withRubric(CS_MATERIAL_TYPE)
      ) {
      $link_item = new cs_link();
      $link_item->setTitle(getMessage('MATERIAL_ADMIN_TINY_HEADER_CONFIGURATION'));
      $link_item->setDescription(getMessage('MATERIAL_ADMIN_TINY_DESCRIPTION'));
      $link_item->setIconPath('images/cs_config/MATERIAL_ADMIN_TINY_DESCRIPTION.gif');
      $link_item->setContextID($environment->getCurrentContextID());
      $link_item->setModule('material_admin');
      $link_item->setFunction('index');
      $link_item->setParameter(array());
      $usage_link_list->add($link_item);
   }

   if ( $environment->inProjectRoom()
        or $environment->inCommunityRoom()
        or $environment->inPrivateRoom()
        or $environment->inGroupRoom()
      ) {
      $link_item = new cs_link();
      $link_item->setTitle(getMessage('PREFERENCES_USAGE_INFOS'));
      $link_item->setDescription(getMessage('PREFERENCES_USAGE_INFOS_DESC'));
      $link_item->setIconPath('images/commsyicons/48x48/config/usage_info_options.png');
      $link_item->setContextID($environment->getCurrentContextID());
      $link_item->setModule('configuration');
      $link_item->setFunction('usageinfo');
      $link_item->setParameter('');
      $usage_link_list->add($link_item);
   }

   if ( !$environment->inServer() and !$environment->inPrivateRoom() ) {
      $link_item = new cs_link();
      $link_item->setTitle(getMessage('PREFERENCES_MAIL_LINK'));
      $link_item->setDescription(getMessage('PREFERENCES_MAIL_DESC'));
      $link_item->setIconPath('images/commsyicons/48x48/config/mail_options.png');
      $link_item->setContextID($environment->getCurrentContextID());
      $link_item->setModule('configuration');
      $link_item->setFunction('mail');
      $link_item->setParameter(array());
      $usage_link_list->add($link_item);
   }

?>