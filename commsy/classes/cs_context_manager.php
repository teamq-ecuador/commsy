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

/** upper class of the context manager
 */
include_once('classes/cs_manager.php');

/** upper class for database connection to the database table "community", "project" and "portal"
 * this upper class implements a database manager for the table "community", "project" and "portal"
 */
class cs_context_manager extends cs_manager {

   var $_room_type = NULL;

   var $_all_room_limit = false;

  /**
   * integer - containing the id of a institution as a limit for the selected announcement
   */
  var $_institution_limit = NULL;

  /**
   * integer - containing the id of a topic as a limit for the selected announcement
   */
  var $_topic_limit = NULL;

   /**
    * string - containing an order limit for the select context
    */
   var $_order = NULL;

   /**
    * string - containing an order limit for the select project
    */
   var $_sort_order = NULL;

   /**
    * integer - containing a status limit: 0 no project, 1 open, 2 closed, 3 deleted
    */
   var $_status_limit = NULL;

   var $_id_array_limit = NULL;
   var $_cache_extras = array();
   var $_cache_list = array();
   var $_cache_row = array();
   var $_sql_with_extra = true;

   /** constructor: cs_room_manager
    * the only available constructor, initial values for internal variables
    *
    * @param object cs_environment the environment
    */
   function cs_context_manager ($environment) {
      $this->cs_manager($environment);
   }

   /** reset limits
    * reset limits of this class: age limit, from limit, interval limit, order limit and all limits from upper class
    */
   function resetLimits () {
      parent::resetLimits();
      $this->_status_limit = NULL;
      $this->_all_room_limit = false;
      $this->_order = NULL;
      $this->_institution_limit = NULL;
      $this->_topic_limit = NULL;
      $this->_sort_order = NULL;
      $this->_id_array_limit = NULL;
   }

   /** set status limit
    */
   function setStatusLimit($limit) {
      $this->_status_limit = (int)$limit;
   }

   /** set status limit to "opened"
    */
   function setOpenedLimit () {
      $this->_status_limit = CS_ROOM_OPEN;
   }

  /** set status limit to "closed"
    */
   function setClosedLimit () {
      $this->_status_limit = CS_ROOM_CLOSED;
   }

   /** set status limit to "locked"
    */
   function setLockedLimit () {
      $this->_status_limit = CS_ROOM_LOCK;
   }

   /** set status limit to "not locked"
    */
   function setNotLockedLimit () {
      $this->_status_limit = 5;
   }

   function setInstitutionLimit ($limit) {
      $this->_institution_limit = (int)$limit;
   }

   function setTopicLimit ($limit) {
      $this->_topic_limit = (int)$limit;
   }

   /** set order limit
    * this method sets an order limit for the select statement
    *
    * @param string limit order limit for selected project
    */
   function setOrder ($limit) {
      $this->_order = (string)$limit;
   }

   /** set order limit
    * this method sets an order limit for the select statement
    *
    * @param string limit order limit for selected project
    */
   function setSortOrder ($limit) {
      $this->_sort_order = (string)$limit;
   }

   /** build a new room item
    * this method returns a new EMTPY room item
    */
   function getNewItem () {
      return $this->_getNewRoomItem($this->_room_type);
   }

  /** Prepares the db_array for the item
    *
    * @param $db_array Contains the data from the database
    *
    * @return array Contains prepared data ( textfunctions applied etc. )
    */
   function _buildItem($db_array) {
      if (empty($db_array)) { // room not exists in database
         return NULL;
      }
      if (isset($db_array['extras'])){
         include_once('functions/text_functions.php');
         $db_array['extras'] = mb_unserialize($db_array['extras']);
      }
      if (isset($db_array['description'])){
         include_once('functions/text_functions.php');
         $db_array['description'] = mb_unserialize($db_array['description']);
      }
      $item = $this->_getNewRoomItem($db_array['type']);
      $item->_setItemData(encode(FROM_DB,$db_array));

      if ( isset($this->_sql_with_extra)
           and !$this->_sql_with_extra ) {
         $item->unsetLoadExtras();
      }

      if ( $this->_cache_on ) {
         if ( empty($this->_cache_object[$item->getItemID()]) ) {
            $this->_cache_object[$item->getItemID()] = $item;
         }
      }

      return $item;
   }

  /** build a new room item, INTERNAL
    * this method returns a new EMTPY room item
    *
    * @return object cs_item a new EMPTY room
    */
   function _getNewRoomItem ($type) {
      if ( (empty($type)) ) {
         $retour = NULL;
      } elseif ( $type == CS_PROJECT_TYPE ) {
         include_once('classes/cs_project_item.php');
         $retour = new cs_project_item($this->_environment);
      } elseif ( $type == CS_SERVER_TYPE ) {
         include_once('classes/cs_server_item.php');
         $retour = new cs_server_item($this->_environment);
      } elseif ( $type == CS_COMMUNITY_TYPE ) {
         include_once('classes/cs_community_item.php');
         $retour = new cs_community_item($this->_environment);
      } elseif ( $type == CS_PRIVATEROOM_TYPE ) {
         include_once('classes/cs_privateroom_item.php');
         $retour = new cs_privateroom_item($this->_environment);
      } elseif ( $type == CS_GROUPROOM_TYPE ) {
         include_once('classes/cs_grouproom_item.php');
         $retour = new cs_grouproom_item($this->_environment);
      } elseif ( $type == CS_PORTAL_TYPE ) {
         include_once('classes/cs_portal_item.php');
         $retour = new cs_portal_item($this->_environment);
      } else {
         include_once('functions/error_functions.php');
         trigger_error('do not know this type: '.$type,E_USER_WARNING);
      }
      $retour->setRoomType($type);
      return $retour;
   }

   function _getRelatedContextListForUser ($user_id, $auth_source, $context_id, $grouproom = false, $only_user = false) {
      include_once('classes/cs_list.php');
      $list = new cs_list();
      if ( !isset($this->_cache_list[$user_id.'_'.$auth_source.'_'.$context_id]) ) {
         $query  = 'SELECT '.$this->addDatabasePrefix($this->_db_table).'.*';
         $query .= ' FROM '.$this->addDatabasePrefix($this->_db_table);
         $query .= ' INNER JOIN '.$this->addDatabasePrefix('user').' ON '.$this->addDatabasePrefix('user').'.context_id='.$this->addDatabasePrefix($this->_db_table).'.item_id
                     AND '.$this->addDatabasePrefix('user').'.auth_source="'.$auth_source.'"
                     AND '.$this->addDatabasePrefix('user').'.deletion_date IS NULL
                     AND '.$this->addDatabasePrefix('user').'.user_id="'.encode(AS_DB,$user_id).'"';
         if ( !$only_user ) {
            $query .= ' AND '.$this->addDatabasePrefix('user').'.status >= "1"';
         } else {
            $query .= ' AND '.$this->addDatabasePrefix('user').'.status >= "2"';
         }
         $query .= ' WHERE 1';
         if ( isset($this->_room_type) and !empty($this->_room_type) ) {
            ############################################
            # FLAG: group room
            ###################BEGIN####################
            $current_portal = $this->_environment->getCurrentPortalItem();
            if ( !isset($current_portal) and !empty($context_id) ) {
               $portal_manager = $this->_environment->getPortalManager();
               $current_portal = $portal_manager->getItem($context_id);
            }
            if ( $this->_room_type == CS_PROJECT_TYPE
                 and (
                       ( isset($current_portal) and $current_portal->withGroupRoomFunctions() )
                       or $grouproom
                     )
               ) {
               $query .= ' AND ('.$this->addDatabasePrefix($this->_db_table).'.type = "'.encode(AS_DB,$this->_room_type).'" or '.$this->addDatabasePrefix($this->_db_table).'.type = "'.CS_GROUPROOM_TYPE.'")';
            } else {
            ####################END#####################
            # FLAG: group room
            ############################################
               $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.type = "'.encode(AS_DB,$this->_room_type).'"';
            ############################################
            # FLAG: group room
            ##################BEGIN####################
               if ( $this->_room_type != CS_GROUPROOM_TYPE ) {
                  $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.type != "'.CS_GROUPROOM_TYPE.'"';
               }
            }
         } else {
            $current_portal = $this->_environment->getCurrentPortalItem();
            if ( !isset($current_portal) and !empty($context_id) ) {
               $portal_manager = $this->_environment->getPortalManager();
               $current_portal = $portal_manager->getItem($context_id);
            }
            if ( ( isset($current_portal)
                   and !$current_portal->withGroupRoomFunctions()
                 )
                 or !$grouproom
               ) {
               $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.type != "'.CS_GROUPROOM_TYPE.'"';
            }
            ###################END######################
            # FLAG: group room
            ############################################
         }

         $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.context_id="'.encode(AS_DB,$context_id).'"';

         if ($this->_delete_limit == true) {
            $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.deleter_id IS NULL';
         }
         if (isset($this->_status_limit)) {
            $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.status = "'.encode(AS_DB,$this->_status_limit).'"';
         }
         $query .= ' ORDER BY title, creation_date DESC';

         // perform query
         $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) ) {
            include_once('functions/error_functions.php');
            trigger_error('Problems selecting '.$this->_db_table.' items.',E_USER_WARNING);
         } else {
            foreach ($result as $query_result) {
               $list->add($this->_buildItem($query_result));
            }
            if ( $this->_cache_on ) {
               $this->_cache_list[$user_id.'_'.$auth_source.'_'.$context_id] = $list;
            }
         }
      } else {
         $list = $this->_cache_list[$user_id.'_'.$auth_source.'_'.$context_id];
      }
      return $list;
   }

   function _getRelatedContextListForUserSortByTime ($user_id, $auth_source, $context_id, $grouproom = false) {
      $list = new cs_list();

      $query  = 'SELECT '.$this->addDatabasePrefix($this->_db_table).'.*, '.$this->addDatabasePrefix('labels').'.item_id AS labels_item_id';
      $query .= ' FROM '.$this->addDatabasePrefix($this->_db_table);

      $query .= ' INNER JOIN '.$this->addDatabasePrefix('user').' ON '.$this->addDatabasePrefix('user').'.context_id='.$this->addDatabasePrefix($this->_db_table).'.item_id
                  AND '.$this->addDatabasePrefix('user').'.auth_source="'.$auth_source.'"
                  AND '.$this->addDatabasePrefix('user').'.deletion_date IS NULL
                  AND '.$this->addDatabasePrefix('user').'.user_id="'.encode(AS_DB,$user_id).'"';
      if (!$this->_all_room_limit) {
         $query .= ' AND '.$this->addDatabasePrefix('user').'.status >= "2"';
      } else {
         $query .= ' AND '.$this->addDatabasePrefix('user').'.status >= "1"';
      }

      $query .= ' LEFT JOIN '.$this->addDatabasePrefix('links').' ON '.$this->addDatabasePrefix($this->_db_table).'.item_id='.$this->addDatabasePrefix('links').'.from_item_id AND '.$this->addDatabasePrefix('links').'.link_type="in_time" AND '.$this->addDatabasePrefix('links').'.context_id="'.$context_id.'"';
      $query .= ' LEFT JOIN '.$this->addDatabasePrefix('labels').' ON '.$this->addDatabasePrefix('links').'.to_item_id='.$this->addDatabasePrefix('labels').'.item_id';

      $query .= ' WHERE 1';
      if (isset($this->_room_type)) {
         ############################################
         # FLAG: group room
         ###################BEGIN####################
         $current_portal = $this->_environment->getCurrentPortalItem();
         if ( $this->_room_type == CS_PROJECT_TYPE
              and ($current_portal->withGroupRoomFunctions()
              or $grouproom) ) {
            $query .= ' AND ('.$this->addDatabasePrefix($this->_db_table).'.type = "'.encode(AS_DB,$this->_room_type).'" or '.$this->addDatabasePrefix($this->_db_table).'.type = "'.CS_GROUPROOM_TYPE.'")';
         } else {
         ####################END#####################
         # FLAG: group room
         ############################################
            $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.type = "'.encode(AS_DB,$this->_room_type).'"';
         ############################################
         # FLAG: group room
         ##################BEGIN####################
         }
         ###################END######################
         # FLAG: group room
         ############################################
      }

      $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.context_id="'.encode(AS_DB,$context_id).'"';

      if ($this->_delete_limit == true) {
         $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.deleter_id IS NULL';
      }
      if (isset($this->_status_limit)) {
         $query .= ' AND '.$this->addDatabasePrefix($this->_db_table).'.status = "'.encode(AS_DB,$this->_status_limit).'"';
      }

      $query .= ' ORDER BY '.$this->addDatabasePrefix('labels').'.name DESC, '.$this->addDatabasePrefix($this->_db_table).'.title, '.$this->addDatabasePrefix($this->_db_table).'.creation_date DESC';

      // perform query
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems selecting '.$this->_db_table.' items.',E_USER_WARNING);
      } else {
         $label_item_id = '';
         $label_manager = $this->_environment->getLabelManager();
         foreach ($result as $query_result) {
            if ( $label_item_id != $query_result['labels_item_id']) {
               if ( isset($query_result['labels_item_id']) ) {
                  $label_item_id = $query_result['labels_item_id'];
                  $label_item = $label_manager->getItem($label_item_id);
               } else {
                  $label_item_id = NULL;
                  $label_item = $label_manager->getNewItem();
                  $label_item->setTitle('COMMON_NOT_LINKED');
               }
               $list->add($label_item);
               unset($label_item);
            }
            $item = $this->_buildItem($query_result);
            $list->add($item);
         }
         unset($label_manager);
      }
      return $list;
   }

   /** get a room
    *
    * @param integer item_id id of the item
    *
    * @return object cs_context a room: project, community, portal, server
    */
   function getItem ($item_id) {
      $retour = NULL;
      if ( !empty($item_id)
           and is_numeric($item_id)
         ) {
         if ( !isset($this->_cache_object[$item_id])
              and !isset($this->_cache_row[$item_id])
            ) {
            $query = "SELECT * FROM ".$this->addDatabasePrefix($this->_db_table)." WHERE ".$this->addDatabasePrefix($this->_db_table).".item_id='".encode(AS_DB,$item_id)."'";
            $result = $this->_db_connector->performQuery($query);
            unset($query);
            if ( !isset($result) ) {
               include_once('functions/error_functions.php');
               trigger_error('Problems selecting '.$this->_db_table.' item.',E_USER_WARNING);
            } elseif ( !empty($result[0]) ) {
               $data_array = $result[0];
               if ( !empty($data_array) ) {
                  $retour = $this->_buildItem($data_array);
               }
               unset($result);
            }
         } else {
            if ( !empty($this->_cache_object[$item_id]) ) {
               $retour = $this->_cache_object[$item_id];
            } else {
               $retour = $this->_buildItem($this->_cache_row[$item_id]);
            }
         }
      }
      return $retour;
   }

   /** get a extras of a room
    *
    * @param integer item_id id of the item
    *
    * @return array extras of a room: project, community, portal, server
    */
   function getExtras ($item_id) {
      $retour = array();
      if ( !empty($item_id)
           and is_numeric($item_id)
         ) {
         if (isset($this->_cache_extras[$item_id])){
            $retour = mb_unserialize($this->_cache_extras[$item_id]);
         }else{
            $query = "SELECT extras FROM ".$this->addDatabasePrefix($this->_db_table)." WHERE ".$this->addDatabasePrefix($this->_db_table).".item_id='".encode(AS_DB,$item_id)."'";
            $result = $this->_db_connector->performQuery($query);
            unset($query);
            if ( !isset($result) ) {
               include_once('functions/error_functions.php');
               trigger_error('Problems selecting '.$this->_db_table.' item.',E_USER_WARNING);
            } elseif ( !empty($result[0]) ) {
               $data_array = $result[0];
               if ( !empty($data_array['extras']) ) {
                  include_once('functions/text_functions.php');
                  $retour = mb_unserialize($data_array['extras']);
               }
               unset($data_array);
               unset($result);
            }
         }
      }
      return $retour;
   }

   function loadExtrasForContextArrayInCache ($id_array) {
      $retour = array();
      $query = "SELECT extras, item_id FROM ".$this->addDatabasePrefix($this->_db_table)." WHERE ";
      $query .= $this->addDatabasePrefix($this->_db_table).".item_id IN (".implode(', ', $id_array).")";
      $result = $this->_db_connector->performQuery($query);
      unset($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems selecting '.$this->_db_table.' item.',E_USER_WARNING);
      } else{
         foreach($result as $r){
            $this->_cache_extras[$r['item_id']]=$r['extras'];
         }
      }

      return $retour;
   }


  /** create a project - internal, do not use -> use method save
    * this method creates a project
    *
    * @param object cs_item project_item the project
    */
  function _create ($item) {
     $query = 'INSERT INTO '.$this->addDatabasePrefix('items').' SET '.
              'context_id="'.encode(AS_DB,$item->getContextID()).'",'.
              'modification_date="'.getCurrentDateTimeInMySQL().'",'.
              'type="'.encode(AS_DB,$this->_room_type).'"';
     $result = $this->_db_connector->performQuery($query);
     if ( !isset($result) ) {
        include_once('functions/error_functions.php');
        trigger_error('Problems creating '.$this->_db_table.' item.',E_USER_WARNING);
        $this->_create_id = NULL;
     } else {
        $this->_create_id = $result;
        $item->setItemID($this->getCreateID());
        $this->_new($item);
     }
  }

  /** update a room - internal, do not use -> use method save
    * this method updates a room
    *
    * @param object cs_context_item a commsy room
    */
   function _update ($item) {
      if ( $this->_update_with_changing_modification_information ) {
         parent::_update($item);
      }
      $query  = 'UPDATE '.$this->addDatabasePrefix($this->_db_table).' SET ';
      if ( $this->_update_with_changing_modification_information ) {
         $query .= 'modification_date="'.getCurrentDateTimeInMySQL().'",';
         $modifier_id = $this->_current_user->getItemID();
         if ( !empty($modifier_id) ) {
            $query .= 'modifier_id="'.encode(AS_DB,$modifier_id).'",';
         }
      }

      if ($item->isOpenForGuests()) {
         $open_for_guests = 1;
      } else {
         $open_for_guests = 0;
      }

      $activity_points = $item->getActivityPoints();
      if ( empty($activity_points) ) {
         $activity_points = 0;
      }

      $query .= 'title="'.encode(AS_DB,$item->getTitle()).'",'.
                "context_id='".encode(AS_DB,$item->getContextID())."',".
                "extras='".encode(AS_DB,serialize($item->getExtraInformation()))."',".
                "status='".encode(AS_DB,$item->getStatus())."',".
                "activity='".encode(AS_DB,$activity_points)."',".
                "is_open_for_guests='".encode(AS_DB,$open_for_guests)."'";

      // maybe move this to method to portal/server manager
      if ( $item->isPortal()
           or $item->isServer()
         ) {
         $url = $item->getUrl();
         if ( isset($url) ) {
            $query .= ", url='".encode(AS_DB,$url)."'";
         }
      }

      $query .= ' WHERE item_id="'.encode(AS_DB,$item->getItemID()).'"';

      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems updating '.$this->_db_table.' item.',E_USER_WARNING);
      }
      unset($item);
   }

   /** creates a new room - internal, do not use -> use method save
    * this method creates a new room
    *
    * @param object cs_context_item (upper class) a commsy room
    */
   function _new ($item) {
      $current_datetime = getCurrentDateTimeInMySQL();
      $user = $item->getCreatorItem();
      if (empty($user)) {
         $user = $this->_environment->getCurrentUserItem();
      }
      $query = 'INSERT INTO '.$this->addDatabasePrefix($this->_db_table).' SET '.
               'item_id="'.encode(AS_DB,$item->getItemID()).'",'.
               'context_id="'.encode(AS_DB,$item->getContextID()).'",'.
               'creator_id="'.encode(AS_DB,$user->getItemID()).'",'.
               'modifier_id="'.encode(AS_DB,$user->getItemID()).'",'.
               'creation_date="'.$current_datetime.'",'.
               'modification_date="'.$current_datetime.'",'.
               'title="'.encode(AS_DB,$item->getTitle()).'",'.
               'extras="'.encode(AS_DB,serialize($item->getExtraInformation())).'",'.
               'type="'.encode(AS_DB,$item->getRoomType()).'",'.
               'status="'.encode(AS_DB,$item->getStatus()).'"';

      // maybe move this to method to portal/server manager
      if ( $item->isPortal()
           or $item->isServer()
         ) {
         $url = $item->getUrl();
         if ( isset($url) ) {
            $query .= ", url='".encode(AS_DB,$url)."'";
         }
      }

      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems creating new '.$this->_room_type.' item: "'.$this->_dberror.'" from query: "'.$query.'"', E_USER_ERROR);
      } else {
         $item->setCreationDate($current_datetime);
      }
      unset($item);
   }

   function minimizeActivityPoints ($quotient) {
      $retour = false;
      $query  = 'UPDATE '.$this->addDatabasePrefix($this->_db_table).' SET activity=ROUND(activity/'.encode(AS_DB,$quotient).') WHERE activity > 0;';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems minimizing activity points.',E_USER_WARNING);
      } else {
         $retour = true;
      }
      return $retour;
   }

   /** delete a project
    * this method deletes a project
    *
    * @param integer item_id item id of the project
    */
   function delete ($item_id) {
      $current_datetime = getCurrentDateTimeInMySQL();
      $current_user = $this->_environment->getCurrentUserItem();
      $user_id = $current_user->getItemID();
      $query = 'UPDATE '.$this->addDatabasePrefix($this->_db_table).' SET'.
               ' deletion_date="'.$current_datetime.'",'.
               ' deleter_id="'.encode(AS_DB,$user_id).'"'.
               ' WHERE item_id="'.encode(AS_DB,$item_id).'"';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems deleting '.$this->_db_table.'.',E_USER_WARNING);
      } else {
         parent::delete($item_id);
      }
   }

   function undelete ($item_id) {
      $current_datetime = getCurrentDateTimeInMySQL();
      $current_user = $this->_environment->getCurrentUserItem();
      $user_id = $current_user->getItemID();
      $query = 'UPDATE '.$this->addDatabasePrefix($this->_db_table).' SET'.
               ' deletion_date=NULL,'.
               ' deleter_id=NULL,'.
               ' modification_date="'.$current_datetime.'",'.
               ' modifier_id="'.encode(AS_DB,$user_id).'"'.
               ' WHERE item_id="'.encode(AS_DB,$item_id).'"';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems undeleting '.$this->_db_table.'.',E_USER_WARNING);
      } else {
         parent::undelete($item_id);
      }
   }

   function getMaxActivityPoints () {
      $retour = 0;
      $query = 'SELECT MAX(activity) AS max FROM '.$this->addDatabasePrefix($this->_db_table).' WHERE context_id = '.encode(AS_DB,$this->_room_limit).';';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or empty($result[0]) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems selecting '.$this->_db_table.' max activity.',E_USER_WARNING);
      } else {
         $data_array = $result[0];
         if (!empty($data_array)) {
            $retour = $data_array['max'];
         }
      }
      return $retour;
   }

   function getMaxActivityPointsInCommunityRoom ($community_room_limit) {
      $retour = 0;
      $query = 'SELECT MAX(activity) AS max FROM '.$this->addDatabasePrefix($this->_db_table).'';
      $query .= ' LEFT JOIN '.$this->addDatabasePrefix('link_items').' AS l31 ON ( l31.deletion_date IS NULL AND ((l31.first_item_id='.$this->addDatabasePrefix($this->_db_table).'.item_id AND l31.second_item_type="'.CS_COMMUNITY_TYPE.'"))) ';
      $query .= ' LEFT JOIN '.$this->addDatabasePrefix('link_items').' AS l32 ON ( l32.deletion_date IS NULL AND ((l32.second_item_id='.$this->addDatabasePrefix($this->_db_table).'.item_id AND l32.first_item_type="'.CS_COMMUNITY_TYPE.'"))) ';
      $query .= 'WHERE '.$this->addDatabasePrefix($this->_db_table).'.context_id = '.encode(AS_DB,$this->_room_limit).'';
      $query .= ' AND ( (l31.context_id="'.encode(AS_DB,$this->_room_limit).'" AND (l31.first_item_id = "'.encode(AS_DB,$community_room_limit).'" OR l31.second_item_id = "'.encode(AS_DB,$community_room_limit).'"))';
      $query .= ' OR ( l32.context_id="'.encode(AS_DB,$this->_room_limit).'" AND (l32.first_item_id = "'.encode(AS_DB,$community_room_limit).'" OR l32.second_item_id = "'.encode(AS_DB,$community_room_limit).'")));';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or empty($result[0]) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems selecting '.$this->_db_table.' max activity: "'.$this->_dberror.'" from query: "'.$query.'"',E_USER_WARNING);
      } else {
         $data_array = $result[0];
         if (!empty($data_array)) {
            $retour = $data_array['max'];
         }
      }
      return $retour;
   }

   function getMaxActivityPointsInCommunityRoomInternal ($community_room_array_limit) {
      $retour = 0;
      if ( !empty($community_room_array_limit)
           and is_array($community_room_array_limit)
         ) {
         $query  = 'SELECT MAX(activity) AS max FROM '.$this->addDatabasePrefix($this->_db_table);
         $query .= ' WHERE '.$this->addDatabasePrefix($this->_db_table).'.item_id IN ('.encode(AS_DB,implode(',',$community_room_array_limit)).');';
         $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) or empty($result[0]) ) {
            include_once('functions/error_functions.php');
            trigger_error('Problems selecting '.$this->_db_table.' max activity: "'.$this->_dberror.'" from query: "'.$query.'"',E_USER_WARNING);
         } else {
            $data_array = $result[0];
            if (!empty($data_array)) {
               $retour = $data_array['max'];
            }
         }
      }
      return $retour;
   }

   function saveActivityPoints ($item) {
      $query = 'UPDATE '.$this->addDatabasePrefix($this->_db_table).' SET'.
               ' activity="'.encode(AS_DB,$item->getActivityPoints()).'"'.
               ' WHERE item_id="'.encode(AS_DB,$item->getItemID()).'"';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems updating activity points '.$this->_db_table.'.',E_USER_WARNING);
      }
   }

   public function setQueryWithExtra () {
      $this->_sql_with_extra = true;
   }

   public function setQueryWithoutExtra () {
      $this->_sql_with_extra = false;
   }
}
?>