<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Jose Mauel Gonzalez Vazquez
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

include_once('functions/misc_functions.php');
include_once('functions/text_functions.php');

class cs_item {

   /**
   * string - containing the type of the item
   */
   var $_type = 'item';

   /**
   * array - containing the data of this item, including lists of linked items

   */
   var $_data = array();


   var $_environment = null;
   /**

   * array - array of boolean values. TRUE if key is changed

   */
   var $_changed = array();

   var $_context_item;

  /** error array for detecting multiple errors.
   *
   */

   var $_error_array = array();

  /**
   * boolean - file list is changed, save new list
   */
   var $_filelist_changed = false;
   var $_filelist_changed_empty = false;
   var $_cache_on = true;

  /**
   * boolean - if true the modification_date will be updated - else not
   */
   var $_change_modification_on_save = true;

   /** constructor
   * the only available constructor, initial values for internal variables
   *
   * @author CommSy Development Group
   */
   function cs_item ($environment) {
      $this->_environment = $environment;
      $this->_changed['general']=true;
      $this->_type = 'item';
   }

   function getContextItem() {
      if ($this->_context_item == null) {
         $context_id = $this->getContextID();
         if ( !empty($context_id) ) {
            $item_manager = $this->_environment->getItemManager();
            $item = $item_manager->getItem($this->getContextID());
            $manager = $this->_environment->getManager($item->getItemType());
            $this->_context_item = $manager->getItem($this->getContextId());
         }
      }
      return $this->_context_item;
   }

   public function setCacheOff () {
      $this->_cache_on = false;
   }

   /** Sets the data of the item.
    *
    * @param $data_array Is the prepared array from "_buildItem($db_array)"
    * @return boolean TRUE if data is valid FALSE otherwise
    */
   function _setItemData($data_array) {
      $this->_data = $data_array;
      return $this->isValid();
   }

   /** Gets the data of the item.
    *
    * @param $data_array Is the prepared array from "_saveItem($db_array)"
    * @return boolean TRUE if data is valid FALSE otherwise
    */

   function _getItemData() {
      if ( $this->isValid() ){
         return $this->_data;
      } else {
      //TBD
        echo('Error in cs_item_new._getItemData(). Item not valid.');
      }
   }


   ###############
   # PUBLIC METHODS
   ############



   /** asks if item is editable by everybody or just creator
    *
    * @param value
    *
    * @author CommSy Development Group
    */
   function isPrivateEditing() {
      if ($this->_getValue('public') == 1) {
         return false;
      }
      return true;
   }

   /** sets if tem is editable by everybody or just creator
    *
    * @param value
    */
   function setPrivateEditing ($value) {
      $this->_setValue('public', $value);
   }


    /** get buzzwords of a material
    * this method returns a list of buzzwords which are linked to the material
    *
    * @return object cs_list a list of buzzwords (cs_label_item)
    *
    * @author CommSy Development Group
    */
   function getBuzzwordArray () {
      $buzzword_array = $this->_getValue('buzzword_array');
      if(empty($buzzword_array)) {
         $label_manager = $this->_environment->getLabelManager();
         $label_manager->setTypeLimit('buzzword');
         $buzzword_list = $this->_getLinkedItemsForCurrentVersion($label_manager, 'buzzword_for');
         $buzzword = $buzzword_list->getFirst();
         while($buzzword) {
            $name = $buzzword->getName();
            if ( !empty($name) ) {
               $this->_data['buzzword_array'][] = $name;
            }
            $buzzword = $buzzword_list->getNext();
         }
      }
      return $this->_getValue('buzzword_array');
   }

    /** get buzzwords of a material
    * this method returns a list of buzzwords which are linked to the material
    *
    * @return object cs_list a list of buzzwords (cs_label_item)
    */
   function getBuzzwordList () {
      $label_manager = $this->_environment->getLabelManager();
      $label_manager->setTypeLimit('buzzword');
      return $this->_getLinkedItemsForCurrentVersion($label_manager, 'buzzword_for');
   }

  /** set buzzwords of a material
    * this method sets a list of buzzwords which are linked to the material
    *
    * @param string value title of the material
    *
    * @author CommSy Development Group
    */
   function setBuzzwordArray($value) {
      $this->_data['buzzword_array'] = $value;
   }

   function _saveBuzzwords() {
      if ( !isset($this->_setBuzzwordsByIDs) ) {
         $buzzword_array = $this->getBuzzwordArray();
         if (!empty($buzzword_array)) {
            array_walk($buzzword_array,create_function('$buzzword','return trim($buzzword);'));
            $label_manager = $this->_environment->getLabelManager();
            $label_manager->resetLimits();
            $label_manager->setTypeLimit('buzzword');
            $label_manager->setContextLimit($this->getContextID());
            $buzzword_exists_id_array = array();
            $buzzword_not_exists_name_array = array();
            foreach ($buzzword_array as $buzzword) {
               $buzzword_item = $label_manager->getItemByName($buzzword);
               if (!empty($buzzword_item)) {
                  $buzzword_exists_id_array[] = array('iid' => $buzzword_item->getItemID());
               } else {
                  $buzzword_not_exists_name_array[] = $buzzword;
               }
            }
            // make buzzword items to get ids
            if (count($buzzword_not_exists_name_array) > 0) {
               foreach($buzzword_not_exists_name_array as $new_buzzword) {
                  $item = $label_manager->getNewItem();
                  $item->setContextID($this->getContextID());
                  $item->setName($new_buzzword);
                  $item->setLabelType('buzzword');
                  $item->save();
                  $buzzword_exists_id_array[] = array('iid' => $item->getItemID());
               }
            }
            // set id array so the links to the items get saved
            $this->_setValue('buzzword_for', $buzzword_exists_id_array, FALSE);
         } else {
            $this->_setValue('buzzword_for', array(), FALSE); // to unset buzzword links
         }
      }
   }

   function setBuzzwordListByID($array){
      $this->_setValue('buzzword_for', $array, FALSE);
      $this->_setBuzzwordsByIDs = true;
   }


   /** get list of linked items
   * this method returns a list of items which are linked to this item
   *
   * @return object cs_list a list of cs_items
   * @access private
   * @author CommSy Development Group
   */
   function _getLinkedItemsForCurrentVersion ($item_manager, $link_type) {
      if (!isset($this->_data[$link_type]) or !is_object($this->_data[$link_type])) {
         $link_manager = $this->_environment->getLinkManager();
         // preliminary version: there should be something like 'getIDArray() in the link_manager'
         $id_array = array();
         $link_array = $link_manager->getLinks($link_type, $this, $this->getVersionID(), 'eq');
         $id_array = array();
            foreach($link_array as $link) {
               if ($link['to_item_id'] == $this->getItemID()) {
                  $id_array[] = $link['from_item_id'];
               } elseif ($link['from_item_id'] == $this->getItemID()) {
                  $id_array[] = $link['to_item_id'];
               }
            }
            $this->_data[$link_type] = $item_manager->getItemList($id_array, $this->getVersionID());
         }
      return $this->_data[$link_type];
   }


   /** get tags of a material
    * this method returns a list of tags which are linked to the material
    *
    * @return object cs_list a list of tags (cs_label_item)
    */
   function getTagArray () {
      $tag_array = $this->_getValue('tag_array');
      if ( empty($tag_array) ) {
         $tag_list = $this->getTagList();
         $tag = $tag_list->getFirst();
         while ($tag) {
            $linked_item = $tag->getLinkedItem($this);  // Get the linked item
            if ( isset($linked_item) ) {
               $title = $linked_item->getTitle();
               if ( !empty($title) ) {
                  $this->_data['tag_array'][] = $title;
               }
               unset($linked_item);
            }
            $tag = $tag_list->getNext();
         }
         unset($tag_list);
         unset($tag);
      }
      return $this->_getValue('tag_array');
   }

   /** get tags of a material
    * this method returns a list of tags which are linked to the material
    *
    * @return object cs_list a list of tags (cs_label_item)
    */
   function getTagList () {
      $list = new cs_list();
      $tag_list = $this->getLinkItemList(CS_TAG_TYPE);
      $tag = $tag_list->getFirst();
      while ($tag) {
         $linked_item = $tag->getLinkedItem($this);  // Get the linked item
         if ( isset($linked_item) ) {
            $list->add($linked_item);
            unset($linked_item);
         }
         $tag = $tag_list->getNext();
      }
      unset($tag_list);
      unset($tag);
      return $list;
   }

  /** set materials of a announcement item by item id and version id
   * this method sets a list of material item_ids and version_ids which are linked to the announcement
   *
   * @param array of material ids, index of id must be 'iid', index of version must be 'vid'
   * Example:
   * array(array('iid' => id1, 'vid' => version1), array('iid' => id2, 'vid' => version2))
   */
   function setTagListByID ($value) {
      $this->setLinkedItemsByID (CS_TAG_TYPE, $value);
   }

   /** set materials of a announcement
    * this method sets a list of materials which are linked to the news
    *
    * @param string value title of the news
    */
   function setTagList ($value) {
      $this->_setObject(CS_TAG_TYPE, $value, FALSE);
   }




   /** Checks the data of the item.
    *
    * @return boolean TRUE if data is valid FALSE otherwise
    * @author CommSy Development Group
    */
   function isValid() {
      $creator = $this->getCreatorID();
      #$creation_date = $this->getCreationDate();
      return !empty($creator); #and !empty($creation_date);
   }

   /** is the type of the item = $type ?
    * this method returns a boolean expressing if type of the item is $type or not
    *
    * @param string type string to compare with type of the item (_type)
    *
    * @return boolean   true - type of this item is $type
    *                   false - type of this item is not $type
    *
    * @author CommSy Development Group
    */
   function isA ($type) {
      return $this->_type == $type;
   }

   /** get item id
   * this method returns the id of the item
   *
   * @return integer id of the item
   *
   * @author CommSy Development Group
   */
   function getItemID() {
      return $this->_getValue('item_id');
   }

   /** set item id
    * this method sets the id of the item
    *
    * @param integer id of the item
    *
    * @author CommSy Development Group
    */
   function setItemID ($value) {
      $this->_setValue('item_id', (int)$value);
   }

   /** get version id
   * this method returns 0
   * it must be overwritten in case version ids are needed
   *
   * @return integer version id of the item
   *
   * @author CommSy Development Group
   */
   function getVersionID() {
      return 0;
   }


   /** set version id
    * this method sets the version id of the item
    *
    * @param integer version id of the item
    *
    * @author CommSy Development Group
    */
   function setVersionID ($value) {
      $this->_setValue('version_id', (integer)$value);
   }

   /** get context id
    * this function returns the id of the current context:
    */
   function getContextID () {
      $context_id = $this->_getValue('context_id');
      if ($context_id === '') {
         $context_id = $this->_environment->getCurrentContextID();
      }
      return (int) $context_id;
   }


   /** set context id
   * this method sets the context id of the item
   *
   * @param integer value context id of the item
   */
   function setContextID ($value) {
      return $this->_setValue('context_id', $value);
   }

   /** get creator
   * this method returns the modificator of the item
   * By default the creator is returned.
   *
   * @return cs_user_item creator of the item
   *
   * @author CommSy Development Group
   */
   function getModificatorItem () {
      $retour = $this->_getUserItem('modifier');
      if ( !isset($retour) ) {
         $retour = $this->getCreatorItem();
      } else {
         $iid = $retour->getItemID();
         if (empty($iid)) {
            $retour = $this->getCreatorItem();
         }
      }
      return $retour;
   }

   /** get creator-id
   * this method returns the modificator of the item
   * By default the creator is returned.
   *
   * @return cs_user_item creator of the item
   *
   * @author CommSy Development Group
   */
   function getModificatorID () {
      $modifier = $this->_getValue('modifier_id');
      if ( !empty($modifier)){
         return $this->_getValue('modifier_id');
      }else{
         return $this->_getValue('creator_id');
      }
   }


   /** get creation date
    * this method returns the creation date of the item
    *
    * @return string creation date of the item in datetime-FORMAT
    *
    * @author CommSy Development Group
    */
   function getCreationDate () {
      return $this->_getValue('creation_date');
   }

   /** set creation date
    * this method sets the creation date of the item
    *
    * @param string creation date in datetime-FORMAT of the item
    *
    * @author CommSy Development Group
    */
   function setCreationDate ($value) {
      $this->_setValue('creation_date', (string)$value);
   }

   /** get modification date
    * this method returns the modification date of the item
    *
    * @return string modification date of the item in datetime-FORMAT
    *
    * @author CommSy Development Group
    */
   function getModificationDate () {
      $date = $this->_getValue('modification_date');
      if (is_null($date)) {
         $date = $this->_getValue('creation_date');
      }
      return $date;
   }

   /** set modification date
    * this method sets the modification date of the item
    *
    * @param string modification date in datetime-FORMAT of the item
    *
    * @author CommSy Development Group
    */
   function setModificationDate ($value) {
      $this->_setValue('modification_date', (string)$value);
   }

   /** get deletion date
    * this method returns the deletion date of the item
    *
    * @return string deletion date of the item in datetime-FORMAT
    *
    * @author CommSy Development Group
    */
   function getDeletionDate () {
      return $this->_getValue('deletion_date');

   }

   function isNotActivated(){
      $date = $this->getModificationDate();
      if ( $date > getCurrentDateTimeInMySQL() ) {
        return true;
      }else{
         return false;
      }
   }

   function getActivatingDate(){
      $retour = '';
      if ($this->isNotActivated()){
         $retour = $this->getModificationDate();
      }
      return $retour;
   }

   /** set deletion date
    * this method sets the deletion date of the item
    *
    * @param string deletion date in datetime-FORMAT of the item
    *
    * @author CommSy Development Group
    */
   function setDeletionDate ($value) {
      $this->_setValue('deletion_date', (string)$value);
   }

   /** get type, should be like getItemType (TBD)
    * this method returns the type of the item
    *
    * @return string type of the item
    *
    * @author CommSy Development Group
    */
   function getType () {
      return $this->_type;
   }


   function getTitle ($mode=NONE) {              //TBD: In Zukunft sollten alle Titel auch Titel sein!!!
     $title = $this->_getValue('title',$mode);
     if (!empty($title)){
        return($title);
     }
     else{
        return($this->_getValue('name',$mode));
     }
   }

   /** set type
    * this method sets the type of the item
    *
    * @param string type of the item
    *
    * @author CommSy Development Group
    */
   function setType ($value) {
      $this->_type = (string)$value;
   }

   /** get item type form database tabel item
    * this method returns the type of the item form the database table item
    *
    * @return string type of the item out of the database table item
    *
    * @author CommSy Development Group
    */
   function getItemType () {
      $type = $this->_getValue('type');
      if (empty($type)){
          $type = $this->getType();
      }
      return $type;
   }

   /** add an extra to the item -- OLD, use setExtra
    * this method adds a value (string, integer or array) to the extra information
    *
    * @param string key   the key (name) of the value
    * @param *      value the value: string, integer, array
    */
   function _addExtra($key, $value) {
      $this->_setExtra ($key,$value);
   }

   /** set an extra in the item
    * this method sets a value (string, integer or array) to the extra information
    *
    * @param string key   the key (name) of the value
    * @param *      value the value: string, integer, array
    */
   function _setExtra ($key, $value) {
      $extras = $this->_getValue('extras');
      $extras[$key] = $value;
      $this->_setValue('extras', $extras);
   }

   /** unset a value
    * this method unsets a value of the extra information
    *
    * @param string key   the key (name) of the value
    *
    * @author CommSy Development Group
    */
   function _unsetExtra($key) {
      if ($this->_issetExtra($key)) {
         $extras = $this->_getValue('extras');
         unset($extras[$key]);
         $this->_setValue('extras', $extras);
      }
   }

   /** exists the extra information with the name $key ?
    * this method returns a boolean, if the value exists or not
    *
    * @param string key   the key (name) of the value
    *
    * @return boolean true, if value exists
    *                 false, if not
    *
    * @author CommSy Development Group
    */
   function _issetExtra($key) {
      $result = false;
      $extras = $this->_getValue('extras');
      if (isset($extras) and is_array($extras) and array_key_exists($key,$extras) and isset($extras[$key])) {
         $result = true;
      }
      return $result;
   }

   /** get an extra value
    * this method returns a value of the extra information
    *
    * @param string key the key (name) of the value
    *
    * @return * value of the extra information
    *
    * @author CommSy Development Group
    */
   function _getExtra($key) {
      $extras = $this->_getValue('extras');
      if ($this->_issetExtra($key)) {
         return $extras[$key];
      }
   }

   /** get all extra keys
    * this method returns an array with all keys in
    *
    * @return array returns an array with all keys in
    *
    * @author CommSy Development Group
    */
   function getExtraKeys () {
      $extras = $this->_getValue('extras');
      return array_keys($extras);
   }
      /** get extra information of an item
    * this method returns the extra information of an item
    *
    * @return string extra information of an item
    *
    * @author CommSy Development Group
    */

   function getExtraInformation () {
      return $this->_getValue('extras');
   }

   /** set extra information of an item
    * this method sets the extra information of an item
    *
    * @param string value extra information of an item
    *
    * @author CommSy Development Group
    */
   function setExtraInformation ($value) {
      $this->_setValue('extras', (array)$value);
   }

   function resetExtraInformation () {
      $this->_setValue('extras', array());
   }

   function isDeleted () {
      $is_deleted = false;
      $deletion_date = $this->getDeletionDate();
      if (!empty($deletion_date) and $deletion_date != '0000-00-00 00:00:00') {
         $is_deleted = true;
      }
      return $is_deleted;
   }

   function getDeleterID() {
      return $this->_getValue('deleter_id');
   }

   function setDeleterID($value) {
      return $this->_setValue('deleter_id',$value);
   }

   function getCreatorID() {
      return $this->_getValue('creator_id');
   }

   function setCreatorID($value) {
      return $this->_setValue('creator_id',$value);
   }

   function setModifierID($value) {
      return $this->_setValue('modifier_id',$value);
   }

   /** set creator of a material
    * this method sets the creator of the material
    *
    * @param user_object creator of a material
    */
   function setCreatorItem ($user) {
       $this->_setUserItem($user,'creator');
   }

   /**Wieder l�schen!!*/
   function setCreator($user) {
      $this->setCreatorItem($user);
   }

    /** get creator of a material
    * this method returns the creator of the material
    *
    * @return user_object creator of a material
    *
    * @author CommSy Development Group
    */
   function getCreatorItem () {
      return $this->_getUserItem('creator');
   }

   function getCreator() {
      return $this->getCreatorItem();
   }

   /** set deleter of a material
    * this method sets the deleter of the material
    *
    * @param user_object deleter of a material
    *
    * @author CommSy Development Group
    */
   function setDeleterItem ($user) {
       $this->_setUserItem($user,'deleter');
   }

   function setDeleter($user) {
      $this->setDeleterItem($user);
   }

  /** set modificator
   * this method set the modificator of the item
   *
   * @param cs_user_item modificator of the item
   *
   * @author CommSy Development Group
   */
   function setModificatorItem ($item) {
      $this->_setUserItem($item,'modifier');
   }

    /** get deleter of a material
    * this method returns the deleter of the material
    *
    * @return user_object deleter of a material
    *
    * @author CommSy Development Group
    */
   function getDeleterItem () {
      return $this->_getUserItem('deleter');
   }

   function getDeleter() {
      return $this->getDeleterItem();
   }

   /** get annotations of the item
   * this method returns a list of materials which are linked to the news
   *
   * @return object cs_list a list of cs_material_item
   *
   * @author CommSy Development Group
   */
   function getAnnotationList () {
      $annotation_manager = $this->_environment->getAnnotationManager();
      $annotation_manager->resetLimits();
      $annotation_manager->setLinkedItemID($this->getItemID());
      $annotation_manager->select();
      return $annotation_manager->get();
   }

   function getItemAnnotationList () {
      $annotation_manager = $this->_environment->getAnnotationManager();
      $annotation_list = $annotation_manager->getAnnotatedItemList($this->getItemID());
      return $annotation_list;
   }


   ######################
   # private methods
   ##################



//********************************************************
//TBD: Nach der vollst�ndigen Migration der Links kann diese Methode entfernt werden
//********************************************************
   /** get list of linked items
   * this method returns a list of items which are linked to the news item
   *
   * @return object cs_list a list of cs_items
   * @access private
   * @author CommSy Development Group
   */
   function _getLinkedItems ($item_manager, $link_type, $order='') {
      if (!isset($this->_data[$link_type]) or !is_object($this->_data[$link_type])) {

         global $environment;
         $link_manager = $environment->getLinkManager();
         // preliminary version: there should be something like 'getIDArray() in the link_manager'

         $id_array = array();
         $link_array = $link_manager->getLinks($link_type, $this, $this->getVersionID(), 'eq');
         $id_array = array();
         foreach($link_array as $link) {
            if ($link['to_item_id'] == $this->getItemID()) {
               $id_array[] = $link['from_item_id'];
            } elseif ($link['from_item_id'] == $this->getItemID()) {
               $id_array[] = $link['to_item_id'];
            }
         }
         $this->_data[$link_type] = $item_manager->getItemList($id_array);
      }
      return $this->_data[$link_type];
   }


   /** get data value
   * this method returns the value for the specified key or an empty string if it is not set.
   *
   * @param string key
   * @access private
   * @author CommSy Development Group
   */
   function _getValue($key) {
      if(!isset($this->_data[$key])) {
         $this->_data[$key] = ($key == 'extras') ? array() : '';
      }
      return $this->_data[$key];
   }

   /** get data object

   * this method returns the object for the specified key or NULL if it is not set.

   *

   * @param string key
   * @access private

   * @author CommSy Development Group

   */
   function _getObject($key) {
      if(!isset($this->_data[$key])) {
         $this->_data[$key] = NULL;
      }
      return $this->_data[$key];
   }

   function _getUserItem($role) {
      $user = $this->_getObject($role);
      if (is_null($user)) {
         $user_manager = $this->_environment->getUserManager();
         $user_manager->setContextLimit($this->getContextID());
         $user_id = $this->_getValue($role.'_id');
         if ( !is_null($user_id) ) {
            $user = $user_manager->getItem($user_id);
            $this->_data[$role] = $user;
         }
      }
      return $user;
   }

   function _setUserItem ($user, $role) {

     if (isset($user) and is_object($user)) { // ??? (TBD)
      $this->_data[$role] = $user;
      $item_id = $user->getItemID();
      $this->_setValue($role.'_id', $item_id);
     } else {
        // abbruch ??? (TBD)
     }
   }


   /** set data value
   * this method sets values for the specified key and marks it as changed
   *
   * @param mixed value to be changed
   * @access private
   * @author CommSy Development Group
   */
   function _setValue($key, $value, $internal=TRUE) {
      $this->_data[$key] = $value;
      if ($internal) {
         $this->_changed['general'] = TRUE;
      } else {
         $this->_changed[$key] = TRUE;
      }
   }

   function _unsetValue ($key) {
      unset($this->_data[$key]);
   }

   /** set object
   * this method sets an object for the specified key and marks it as changed
   *
   * @param mixed object to be changed
   * @access private
   * @author CommSy Development Group
   */
   function _setObject($key, $value, $internal=TRUE) {
      $this->_data[$key] = $value;
      if ($internal) {
         $this->_changed['general'] = TRUE;
      } else {
         $this->_changed[$key] = TRUE;
      }
   }

   /** save item
   * this method saves the item to the database; if links to other items (e.g. relevant groups) are changed, they will be updated too.
   *
   * @param cs_manager the manager that should be used to save the item (e.g. cs_news_manager for cs_news_item)
   * @access private
   */
   function _save($manager) {
      $saved = false;
      if(isset($this->_changed['general']) and $this->_changed['general'] == TRUE) {
         $manager->setCurrentContextID($this->getContextID());
         $saved = $manager->saveItem($this);
      }
      foreach ($this->_changed as $changed_key => $is_changed) {
         if ($is_changed) {
            if ($changed_key != 'general' and $changed_key !='section_for' and $changed_key !='task_item' and $changed_key !='copy_of') {
               // Abfrage n�tig wegen langsamer Migration auf die neuen LinkTypen.
               if ( in_array($changed_key, array(  CS_TOPIC_TYPE,
                                                   CS_INSTITUTION_TYPE,
                                                   CS_GROUP_TYPE,
                                                   CS_PROJECT_TYPE,
                                                   CS_PRIVATEROOM_TYPE,
                                                   CS_MYROOM_TYPE,
                                                   CS_COMMUNITY_TYPE,
                                                   CS_ANNOUNCEMENT_TYPE,
                                                   CS_MATERIAL_TYPE,
                                                   CS_TAG_TYPE,
                                                   CS_TODO_TYPE,
                                                   CS_DATE_TYPE,
                                                   CS_DISCUSSION_TYPE,
                                                   CS_USER_TYPE)) ) {
                  $link_manager = $this->_environment->getLinkItemManager();
                  if (is_object($this->_data[$changed_key])) { // a list of objects or one object
                     $this->_setObjectLinkItems($changed_key);
                  } elseif (is_array($this->_data[$changed_key])) { // an array
                     $this->_setIDLinkItems($changed_key);
                  }
               } else {   // sollte irgendwann �berfl�ssig werden!!!!
                  $link_manager = $this->_environment->getLinkManager();
                  $version_id = $this->getVersionID();
                  $link_manager->deleteLinks($this->getItemID(),$version_id,$changed_key);
                  if (is_object($this->_data[$changed_key])) { // a list of objects or one object
                     $this->_setObjectLinks($changed_key);
                  } elseif (is_array($this->_data[$changed_key])) { // an array
                     $this->_setIDLinks($changed_key);
                  }
               }
            }
         }
      }
      return $saved;
   }

   function _setObjectLinkItems($changed_key) {
      // $changed_key_item_list enth�lt die link_items EINES TYPS, die das Item aktuell bei sich tr�gt
      // $old_link_item_list die Link items EINES TYPS, die das Link Item vor der Bearbeitung besa
      $link_manager = $this->_environment->getLinkItemManager();
      $link_manager->resetLimits();
     if ( ($changed_key == CS_COMMUNITY_TYPE and $this->isA(CS_PROJECT_TYPE))
          or
         ($changed_key == CS_PROJECT_TYPE and $this->isA(CS_COMMUNITY_TYPE))
        ) {
         $link_manager->setContextLimit($this->getContextID());
     } else {
         $link_manager->setContextLimit($this->_environment->getCurrentContextID());
     }
      $link_manager->setLinkedItemLimit($this);
      $link_manager->setTypeLimit($changed_key);
      $link_manager->select();
      $old_link_item_list = $link_manager->get();
      $delete_link_item_list = $link_manager->get();
      $changed_key_item_list = $this->_data[$changed_key];
      $create_key_item_list = $this->_data[$changed_key];
      $old_link_item = $old_link_item_list->getFirst();
      //Beide Listen durchgehen und vergleichen
      while ($old_link_item) {
         $old_linked_item = $old_link_item->getLinkedItem($this);
         $changed_key_item = $changed_key_item_list->getFirst();
         while( $changed_key_item ){
            $changed_key_item_id = $changed_key_item->getItemID();
            #$changed_key_version_id = $changed_key_item->getVersionID();
            $old_linked_item_id = $old_linked_item->getItemID();
            #$old_linked_version_id = $old_linked_item->getVersionID();
            // gibt es keine �bereinstimmung
            #if ($changed_key_item_id == $old_linked_item_id AND $changed_key_version_id == $old_linked_version_id){
            if ($changed_key_item_id == $old_linked_item_id) {
               $create_key_item_list->removeElement($changed_key_item);
               $delete_link_item_list->removeElement($old_linked_item);
            }
            $changed_key_item = $changed_key_item_list->getNext();
         }
        $old_link_item = $old_link_item_list->getNext();
      }
      $changed_key_item = $create_key_item_list->getFirst();
      while( $changed_key_item ){
         //Das neue Link_item erzeugen und abspeichern
         $link_item = $link_manager->getNewItem();
         $link_item->setFirstLinkedItem($this);
         $link_item->setSecondLinkedItem($changed_key_item);
         $link_item->save();
         $changed_key_item = $create_key_item_list->getNext();
      }
      $delete_link_item = $delete_link_item_list->getFirst();
      while ($delete_link_item) {
         $delete_link_item->delete();
         $delete_link_item = $delete_link_item_list->getNext();
      }
   }

   function _setIDLinkItems($changed_key) {
      $link_manager = $this->_environment->getLinkItemManager();
      $link_manager->resetLimits();
      if (
          ( $changed_key == CS_COMMUNITY_TYPE
            and $this->isA(CS_PROJECT_TYPE)
         )
         or ( $changed_key == CS_PROJECT_TYPE
               and $this->isA(CS_COMMUNITY_TYPE)
            )
       ) {
         $link_manager->setContextLimit($this->getContextID());
     } else {
         $link_manager->setContextLimit($this->_environment->getCurrentContextID() );
     }
      if ($changed_key == CS_COMMUNITY_TYPE){
         $change_all_items_in_community_room = true;
      }else{
         $change_all_items_in_community_room = false;
      }
      $link_manager->setLinkedItemLimit($this);
      if ($changed_key == CS_MYROOM_TYPE){
         $type_array[0]='project';
         $type_array[1]='community';
         $link_manager->setTypeArrayLimit($type_array);
      }else{
         $link_manager->setTypeLimit($changed_key);
      }
      $link_manager->select();
      $old_link_item_list = $link_manager->get();
      $delete_link_item_list = clone $old_link_item_list;
      $changed_key_array = $this->_data[$changed_key];
      $create_key_array = $changed_key_array;
      $old_link_item = $old_link_item_list->getFirst();
      //Beide Listen durchgehen und vergleichen
      while ($old_link_item) {
         $old_linked_item = $old_link_item->getLinkedItem($this);
         if ( isset($old_linked_item) ) {
            foreach ($changed_key_array as $item_data) {
               $old_linked_item_id = $old_linked_item->getItemID();
               $changed_key_item_id = $item_data['iid'];
               if ($changed_key_item_id == $old_linked_item_id) {
                  foreach($create_key_array as $count => $create_data){
                     if ($create_data['iid'] == $old_linked_item_id) {
                        array_splice($create_key_array,$count,1);
                     }
                  }
                  $delete_link_item_list->removeElement($old_link_item);
               }
            }
         }
         $old_link_item = $old_link_item_list->getNext();
      }

      foreach( $create_key_array as $item_data ){
         //Das neue Link_item erzeugen und abspeichern
         $link_item = $link_manager->getNewItem();
         $link_item->setFirstLinkedItem($this);
         $item_manager = $this->_environment->getManager($changed_key);
         $item = $item_manager->getItem($item_data['iid']);
         $link_item->setSecondLinkedItem($item);
         $link_item->save();
      }
      $delete_link_item = $delete_link_item_list->getFirst();
      while ($delete_link_item) {
         if ($change_all_items_in_community_room){
            $item_id = $delete_link_item->getFirstLinkedItemID();
            $context_id = $delete_link_item->getSecondLinkedItemID();
            $link_manager = $this->_environment->getLinkItemManager();
            $link_manager->deleteAllLinkItemsInCommunityRoom($item_id,$context_id);
         }
         $delete_link_item->delete();
         $delete_link_item = $delete_link_item_list->getNext();
      }
   }


//********************************************************
//TBD: Nach der vollst�ndigen Migration der Links kann diese Methode entfernt werden
//********************************************************

   function _setObjectLinks($changed_key) {
      $link_manager = $this->_environment->getLinkManager();
      $item = $this->_data[$changed_key]->getFirst();
      // iterating through the list should be done by the link manager
      while ($item) {
         if ( $changed_key == 'material_for' ||
              $changed_key == 'member_of' ) {# ||
#              $changed_key == 'task_item'){
            $link_array = array();
            $link_array['room_id'] = $this->getContextID();
            $link_array['to_item_id'] = $this->getItemID();
            $link_array['to_version_id'] = $this->getVersionID();
            $link_array['from_item_id'] = $item->getItemID();
            $link_array['from_version_id'] = $this->getVersionID();
         } else {
            $link_array = array();
            $link_array['room_id'] = $this->getContextID();
            $link_array['from_item_id'] = $this->getItemID();
            $link_array['from_version_id'] = $this->getVersionID();
            $link_array['to_item_id'] = $item->getItemID();
            $link_array['to_version_id'] = $item->getVersionID();
         }
         // needed for import material !!!
         if ($item->getContextID() != $this->_environment->getCurrentContextID()) {
            $link_array['room_id'] = $item->getContextID();
         }
         $link_array['link_type']= $changed_key;
         $link_manager->save($link_array);
         $item = $this->_data[$changed_key]->getNext();
      }
   }


//********************************************************
//TBD: Nach der vollst�ndigen Migration der Links kann diese Methode entfernt werden
//********************************************************
   function _setIDLinks($changed_key) {
      $link_manager = $this->_environment->getLinkManager();
      foreach ($this->_data[$changed_key] as $item_data) {
         if ( $changed_key == 'material_for' ||
              $changed_key == 'member_of' ) {# ||
#              $changed_key == 'task_item') {
            $link_array = array();
            $link_array['room_id'] = $this->getContextID();
            $link_array['to_item_id'] = $this->getItemID();
            $link_array['to_version_id'] = $this->getVersionID();
            $link_array['from_item_id'] = $item_data['iid'];
            if(isset($item_data['vid'])) {
               $link_array['from_version_id'] = $item_data['vid'];
            } else {
                $link_array['from_version_id'] = 0;
            }
         } else {
            $link_array = array();
            $link_array['room_id'] = $this->getContextID();
            $link_array['from_item_id'] = $this->getItemID();
            $link_array['from_version_id'] = $this->getVersionID();
            if ($changed_key == 'buzzword_for' and (!is_array($item_data)) ) {
               $link_array['to_item_id'] = $item_data;
            } else {
               $link_array['to_item_id'] = $item_data['iid'];
            }
            $link_array['to_version_id'] = 0;
         }
         $link_array['link_type'] = $changed_key;
         $link_manager->save($link_array);
      }
      // MERDE
   }

   function _setValueAsID ($key, $value) {
      $data[] = array('iid' => (int)$value, 'vid' => '0');
      $this->_setValue($key, $data, FALSE);
   }

   function _setValueAsIDArray ($key, $value) {
      $data = array();
      foreach ($value as $id) {
         $data[] = array('iid' => $id, 'vid' => '0');
      }
      $this->_setValue($key, $data, FALSE);
   }

   function _setObjectAsItem ($key, $value) {
      $list = new cs_list();
      $list->add((object)$value);
      $this->_setObject($key, $list, FALSE);
   }

   /** delete item
   * this method deletes the item to the database; if links to other items (e.g. relevant groups) are changed, they will be updated too.
   *
   * @param cs_manager the manager that should be used to delete the item (e.g. cs_news_manager for cs_news_item)
   * @access private
   *
   * @author CommSy Development Group
   */
   function _delete($manager) {
      $manager->delete($this->getItemID());
      $link_manager = $this->_environment->getLinkItemManager();
      $link_manager->deleteLinksBecauseItemIsDeleted($this->getItemID());
   }

   function _undelete ($manager) {
      $manager->undelete($this->getItemID());
      $link_manager = $this->_environment->getLinkItemManager();
      $link_manager->undeleteLinks($this);
   }

   function isPublic () {
      return false;
   }

   function mayEdit ($user_item) {
       if ( $user_item->isRoot() or
           ($user_item->getContextID() == $this->getContextID()
            and ($user_item->isModerator()
                 or ($user_item->isUser()
                     and ($user_item->getItemID() == $this->getCreatorID()
                          or $this->isPublic())))) ) {
         $access = true;
      } else {
         $access = false;
      }
      return $access;
   }

   public function mayEditByUserID ($user_id,$auth_source) {
      $user_manager = $this->_environment->getUserManager();
      $user_manager->resetLimits();
      $user_manager->setUserIDLimit($user_id);
      $user_manager->setAuthSourceLimit($auth_source);
      $user_manager->setContextLimit($this->getContextID());
      $user_manager->select();
      $user_list = $user_manager->get();
      if ($user_list->getCount() == 1) {
         $user_in_room = $user_list->getFirst();
         return $this->mayEdit($user_in_room);
      } elseif ($user_list->getCount() > 1) {
         include_once('functions/error_functions.php');
         trigger_error('ambiguous user data in database table "user" for user-id "'.$user_id.'"',E_USER_WARNING);
      } else {
         include_once('functions/error_functions.php');
         trigger_error('can not find user data in database table "user" for user-id "'.$user_id.'", auth_source "'.$auth_source.'", context_id "'.$this->getContextID().'"',E_USER_WARNING);
      }
   }

   function maySee ($user_item) {
      if ( $user_item->isRoot()
          or ( $user_item->getContextID() == $this->_environment->getCurrentContextID()
                and $user_item->isUser() )
         or $user_item->isGuest() ) {
            $access = true;
      } else {
         $access = false;
      }
      return $access;
   }

   function getLatestLinkItemList ($count) {
      $link_list = new cs_list();
      $link_item_manager = $this->_environment->getLinkItemManager();
      $link_item_manager->setLinkedItemLimit($this);
      $link_item_manager->setEntryLimit($count);

      $context_item = $this->_environment->getCurrentContextItem();
      $conf = $context_item->getHomeConf();
      if ( !empty($conf) ) {
         $rubrics = explode(',', $conf);
      } else {
         $rubrics = array();
      }
      $type_array = array();
      foreach ( $rubrics as $rubric ) {
         $rubric_array = explode('_', $rubric);
         if ( $rubric_array[1] != 'none' and $rubric_array[0] != CS_USER_TYPE) {
            $type_array[] = $rubric_array[0];
         }
      }
      $link_item_manager->setTypeArrayLimit($type_array);
      $link_item_manager->setRoomLimit($this->getContextID());
      $link_item_manager->select();
      $link_list = $link_item_manager->get();
      $link_item_manager->resetLimits();
      return $link_list;
   }


   function getLinkItemList ($type) {
      $link_list = new cs_list();
      $link_item_manager = $this->_environment->getLinkItemManager();
      $link_item_manager->setLinkedItemLimit($this);
      if ($type == CS_MYROOM_TYPE){
         $type_array[0]='project';
         $type_array[1]='community';
         $link_item_manager->setTypeArrayLimit($type_array);
      } else {
         $link_item_manager->setTypeLimit($type);
      }

      $context_item = $this->_environment->getCurrentContextItem();
      if (
            ($type == CS_COMMUNITY_TYPE and $this->isA(CS_PROJECT_TYPE) and $this->_environment->inProjectRoom())
            or ($type == CS_COMMUNITY_TYPE and $this->isA(CS_PROJECT_TYPE) and $this->_environment->getCurrentModule() == 'project')
            or ($type == CS_PROJECT_TYPE and $this->isA(CS_COMMUNITY_TYPE))
         ) {
         $link_item_manager->setRoomLimit($this->getContextID());
      } elseif ( $this->isA(CS_LABEL_TYPE) and $this->getLabelType() == CS_GROUP_TYPE ) {
         // m�sste dies nicht f�r alle F�lle gelten ???
         $link_item_manager->setRoomLimit($this->getContextID());
      } elseif ( $this->isA(CS_USER_TYPE) ) {
         $link_item_manager->setRoomLimit($this->getContextID());
      } else {
         $link_item_manager->setRoomLimit($this->_environment->getCurrentContextID() );
      }
      $link_item_manager->select();
      $link_list = $link_item_manager->get();
      return $link_list;
   }

   function getLinkedItemList ($type) {
      $link_list = $this->getLinkItemList($type);

      $result_list = new cs_list();
      $link_item = $link_list->getFirst();
      while ($link_item) {
         $result_list->add($link_item->getLinkedItem($this));
         $link_item = $link_list->getNext();
      }
      return $result_list;
   }

   function getLinkedItemIDArray($type) {
      $id_array = array();
      $link_list = $this->getLinkItemList($type);
      $link_item = $link_list->getFirst();
      while ($link_item) {
         $link_item_id = $link_item->getFirstLinkedItemID();
         if ($link_item_id == $this->getItemID()){
            $id_array[] = $link_item->getSecondLinkedItemID();
         } else {
            $id_array[] = $link_item->getFirstLinkedItemID();
         }
         $link_item = $link_list->getNext();
      }
      return $id_array;
   }

   function setLinkedItemsByID ($rubric, $value) {
      $data = array();
      foreach ( $value as $iid ) {
         $tmp['iid'] = $iid;
         $data[] = $tmp;
      }
      $this->_setValue($rubric, $data, FALSE);
   }

  /** change creator and modificator - INTERNAL should be called from methods in subclasses
   * change creator and modificator after item was saved for the first time
   */
   function _changeCreatorItemAndModificatorItemTo ($user, $manager) {
      $this->setCreatorItem($user);
      $this->setModificatorItem($user);
      $manager->setCurrentContextID($this->getContextID());
      $manager->saveItemNew($this);
   }

   function hasBeenClicked($user){
      $user_array = $this->getArrayNew4User();
      $id = $user->getItemID();
      if (!empty($user_array) and in_array($id,$user_array)){
         return true;
      }else{
         return false;
      }
   }

   function HasBeenClickedSinceChanged ($user) {
      $user_array = $this->getArrayChanged4User();
      $id = $user->getItemID();
      if (!empty($user_array) and in_array($id, $user_array)){
         return true;
      } else {
         return false;
      }
   }

   function undelete () {
     $manager = $this->_environment->getManager($this->getItemType());
     $manager->undeleteItemByItemID($this->getItemID());
   }

   /** delete item
    * this method deletes an item
    */
   function delete() {
      $manager = $this->_environment->getManager($this->getItemType());
      $this->_delete($manager);
   }

   protected function _getDataAsXML () {
      $retour = '';
      foreach ($this->_data as $key => $value) {
         if ($key == 'extras') {
            $xml = array2XML($value);
            if ( strstr($xml,"%CS_AND;") ) {
               $xml = ereg_replace("%CS_AND;", "&", $xml);
            }
            if ( strstr($xml,"%CS_LT;") ) {
               $xml = ereg_replace("%CS_LT;", "<", $xml);
            }
            if ( strstr($xml,"%CS_GT;") ) {
               $xml = ereg_replace("%CS_GT;", ">", $xml);
            }
            $retour .= '<'.$key.'>'.$xml.'</'.$key.'>'.LF;
         } elseif ( !empty($value) ) {
            $retour .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>'.LF;
         }
      }
      return $retour;
   }

   ################## file handling ############################

  /** get list of files attached o this item
      if a list of files has been set (@see setFileList()), get it
      if an array of file-ids has been set (@see setFileIDArray()),
      get corresponding files, otherwise get files linked in material_link_file
      @return cs_list list of file items
   */
   function getFileList() {
      $file_list = new cs_list;
      if ( !empty($this->_data['file_list']) ) {
         $file_list = $this->_data['file_list'];
      } else {
         if ( isset($this->_data['file_id_array']) and !empty($this->_data['file_id_array']) ) {
            $file_id_array = $this->_data['file_id_array'];
         } else {
            $link_manager = $this->_environment->getLinkManager();
            $file_links = $link_manager->getFileLinks($this);
            if ( !empty($file_links) ) {
               foreach ($file_links as $link) {
                  $file_id_array[] = $link['file_id'];
               }
            }
         }
         if ( !empty($file_id_array) ) {
            $file_manager = $this->_environment->getFileManager();
            $file_manager->setIDArrayLimit($file_id_array);
            $file_manager->setContextLimit('');
            $file_manager->select();
            $file_list = $file_manager->get();
         }
      }
      $file_list->sortby('filename');
      return $file_list;
   }

   /**get array of file ids
      if an array of file-ids has been set (@see setFileIDArray()), get it
      if a list of files has been set (@see setFileList()), get corresponding file-ids,
      otherwise get file-ids according to links in material_link_file
      @return array file_id_array
   */
   function getFileIDArray () {
      $file_id_array = array();
      if ( isset($this->_data['file_id_array']) and !empty($this->_data['file_id_array']) ) { // check if file_id_array has been set by user or this method has been called before
         $file_id_array = $this->_data['file_id_array'];
      } elseif ( isset($this->_data['file_id_array'])
                 and empty($this->_data['file_id_array'])
                 and $this->_filelist_changed
               ) { // alle dateien bewusst abh�ngen
         $file_id_array = $this->_data['file_id_array'];
      } elseif ( isset($this->_data['file_list']) and is_object($this->_data['file_list']) ) {
         $file = $this->_data['file_list']->getFirst();
         while($file) {
            $file_id_array[] = $file->getFileID();
            $file = $this->_data['file_list']->getNext();
         }
      } else {
         $link_manager = $this->_environment->getLinkManager();
         $file_links = $link_manager->getFileLinks($this);
         if ( !empty($file_links) ) {
            foreach ($file_links as $link) {
               $file_id_array[] = $link['file_id'];
            }
         }
      }
      return $file_id_array;
   }

   function setFileIDArray ($value) {
      $this->_data['file_id_array'] = $value;
      $this->_data['file_list'] = NULL;
      $this->_filelist_changed = TRUE;
      if ( empty($value) ) {
         $this->_filelist_changed_empty = true;
      }
   }

   function setFileList ($value) {
      $this->_data['file_list'] = $value;
      $this->_data['file_id_array'] = '';
      $this->_filelist_changed = TRUE;
   }

   function _saveFileLinks() {   // das ist so komplex, weil wir die filelinks nicht aus der db l�schen k�nnen
                                 // wenn jemandem was eleganteres einf�llt: nur zu
      if ( $this->_filelist_changed ) {
         $this->setModificationDate(NULL);
         $link_manager = $this->_environment->getLinkManager();
         $file_id_array = $this->getFileIDArray();
         if ( $file_id_array === '' or $this->_filelist_changed_empty ) {
            $link_manager->deleteFileLinks($this);
         } else {
            $current_file_links = $link_manager->getFileLinks($this);
            $keep_links = array();
            if ( !empty($current_file_links) ) {
               foreach ($current_file_links as $cur_link) {
                  if ( in_array($cur_link['file_id'], $file_id_array) ) {
                     $keep_links[] = $cur_link['file_id'];
                  } else {
                     $link_manager->deleteFileLinkByID($this, $cur_link['file_id']);
                  }
               }
            }
            $add_links = array_diff($file_id_array, $keep_links);
            if( !empty($add_links) ) {
               foreach ($add_links as $file_id) {
                  $link_manager->linkFileByID($this, $file_id);
               }
            }
         }
      }
   }

   function _saveFiles () {
      $file_id_array = array();
      $result = false;
      if ( $this->_filelist_changed
           and isset($this->_data['file_list'])
           and $this->_data['file_list']->getCount() > 0
         ) {
         $file_id_array = array();
         $file_item = $this->_data['file_list']->getFirst();
         while ( $file_item ) {
            if ( $file_item->getContextID() != $this->getContextID() ) {
               $file_item->setContextID($this->getContextID());
            }
            $file_item->setCreatorItem($this->getCreatorItem());
            $result = $file_item->save();
            if ($result) {
               $file_item_id = $file_item->getFileID();
               if ( !empty($file_item_id) ) {
                  $file_id_array[] = $file_item_id;
               } else {
                  $this->_error_array[] = $file_item->getDisplayName();
               }
            } else {
               $this->_error_array[] = $file_item->getDisplayName();
            }
            $file_item = $this->_data['file_list']->getNext();
         }
         $this->setFileIDArray($file_id_array);
      }

      global $c_indexing,$c_indexing_cron;
      if ( isset($c_indexing)
           and !empty($c_indexing)
           and $c_indexing
           and isset($c_indexing_cron)
           and !$c_indexing_cron
         ) {
         $ftsearch_manager = $this->_environment->getFTSearchManager();
         $ftsearch_manager->buildFTIndex();
      }
   }

   function _copyFileList () {
      $file_list = $this->getFileList();
      $file_new_list = new cs_list();
      if ( !empty($file_list) and $file_list->getCount() > 0 ) {
         $file_item = $file_list->getFirst();
         while ( $file_item ) {
            $user = $this->getCreatorItem();
            $file_item->setItemID('');
            $file_item->setTempName($file_item->getDiskFilename());
            $file_item->setContextID($this->getContextID());
            $file_item->setCreatorItem($user);
            $file_new_list->add($file_item);
            $file_item = $file_list->getNext();
         }
      }
      return $file_new_list;
   }

   function isPublished () {
      return true;
   }

   function getErrorArray () {
      return $this->_error_array;
   }

   function setErrorArray ($error_array) {
      $this->_error_array = $error_array;
   }

   public function getDescriptionWithoutHTML () {
      $retour = $this->getDescription();
      $retour = str_replace('<!-- KFC TEXT -->','',$retour);
      $retour = preg_replace('�<[A-Za-z][^>.]+>�','',$retour);
      return $retour;
   }

   /** save item
    * this methode save the item into the database
    */
   public function save () {
      $manager = $this->_environment->getManager($this->getItemType());
      $this->_save($manager);
   }

   /**
    * returns true if the modification_date should be saved
    *
    * @param boolean
    */
   function isChangeModificationOnSave() {
      return $this->_change_modification_on_save;
   }

   function setChangeModificationOnSave($save) {
      $this->_change_modification_on_save = $save;
   }


   function getTopicList() {
      $topic_list = $this->getLinkedItemList(CS_TOPIC_TYPE);
      $topic_list->sortBy('name');
      return $topic_list;
   }

   function setTopicListByID ($value) {
      $topic_array = array();
      foreach ( $value as $iid ) {
         $tmp_data = array();
         $tmp_data['iid'] = $iid;
         $topic_array[] = $tmp_data;
      }
      $this->_setValue(CS_TOPIC_TYPE, $topic_array, FALSE);
   }

   function setTopicList($value) {
      $this->_setObject(CS_TOPIC_TYPE, $value, FALSE);
   }

   function getInstitutionList() {
      $institution_list = $this->getLinkedItemList(CS_INSTITUTION_TYPE);
      $institution_list->sortBy('name');
      return $institution_list;
   }

   function setInstitutionListByID ($value) {
      $this->setLinkedItemsByID (CS_INSTITUTION_TYPE, $value);
   }

   function setInstitutionList($value) {
      $this->_setObject(CS_INSTITUTION_TYPE, $value, FALSE);
   }

   function getGroupList () {
      $group_list = $this->getLinkedItemList(CS_GROUP_TYPE);
      $group_list->sortBy('name');
      return $group_list;
   }

   function setGroupListByID ($value) {
      $this->setLinkedItemsByID (CS_GROUP_TYPE, $value);
   }

   function setGroupList($value) {
      $this->_setObject(CS_GROUP_TYPE, $value, FALSE);
   }

   function getMaterialList () {
      return $this->getLinkedItemList(CS_MATERIAL_TYPE);
   }

   function setMaterialListByID ($value) {
      $this->setLinkedItemsByID (CS_MATERIAL_TYPE, $value);
   }

   function setMaterialList ($value) {
      $this->_setObject(CS_MATERIAL_TYPE, $value, FALSE);
   }
}
?>