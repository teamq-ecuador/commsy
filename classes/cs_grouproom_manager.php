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

/** cs_list is needed for storage of the commsy items
 */
include_once('classes/cs_list.php');

/** upper class of the group room manager
 */
include_once('classes/cs_context_manager.php');

/** date functions are needed for method _newVersion()
 */
include_once('functions/date_functions.php');

/** text functions are needed for ???
 */
include_once('functions/text_functions.php');

/** misc functions are needed for extras field in database table
 */
include_once('functions/misc_functions.php');

/** class for database connection to the database table "room" type "group"
 * this class implements a database manager for the table "room" type "group"
 */
class cs_grouproom_manager extends cs_context_manager {

  /**
   * integer - containing the age of project as a limit
   */
  var $_age_limit = NULL;

  /**
   * string - enth�lt die USERID eines Benutzers
   */
  var $_user_id_limit = NULL;

  var $_time_limit = NULL;

  private $_project_room_limit = NULL;

  /** constructor
    * the only available constructor, initial values for internal variables
    *
    * @param object cs_environment the environment
    */
  public function __construct ($environment) {
     $this->_db_table = 'room';
     $this->_room_type = CS_GROUPROOM_TYPE;
     $this->cs_context_manager($environment);
  }

  /** reset limits
    * reset limits of this class: local limits and all limits from upper class
    */
  public function resetLimits () {
     parent::resetLimits();
     $this->_age_limit = NULL;
     $this->_user_id_limit = NULL;
     $this->_time_limit = NULL;
     $this->_project_room_limit = NULL;
  }

  /** set age limit
    * this method sets an age limit for group room
    *
    * @param integer limit age limit for group room
    */
  public function setAgeLimit ($limit) {
     $this->_age_limit = (integer)$limit;
  }

  /** set user id limit
    *
    * @param string limit userid limit for selected group rooms
    */
  public function setUserIDLimit ($limit) {
     $this->_user_id_limit = (string)$limit;
  }

  public function setAuthSourceLimit ($limit) {
     $this->_auth_source_limit = (int)$limit;
  }

  public function setGetAllRoomLimit () {
     $this->_all_room_limit = true;
  }

  public function setProjectRoomLimit ($limit) {
     $this->_project_room_limit = (int)$limit;
  }

  /** set time limit
    * this method sets an clock pulses limit for rooms
    *
    * @param integer limit time limit for rooms (item id of clock pulses)
    */
  public function setTimeLimit ($limit) {
     $this->_time_limit = $limit;
  }

  /** select group rooms limited by limits
    * this method returns a list (cs_list) of group rooms within the database limited by the limits. the select statement is a bit tricky, see source code for further information
    */
   public function _performQuery ($mode = 'select') {
      $query = 'SELECT DISTINCT';
      if ($mode == 'count') {
         $query .= ' count(DISTINCT '.$this->_db_table.'.item_id) as count';
      } elseif ($mode == 'id_array') {
         $query .= ' '.$this->_db_table.'.item_id';
      } else {
         $query .= ' '.$this->_db_table.'.*';
      }
      $query .= ' FROM '.$this->_db_table.'';

      // user id limit
      if (isset($this->_user_id_limit)) {
         $query .= ' LEFT JOIN user ON user.context_id='.$this->_db_table.'.item_id AND user.deletion_date IS NULL';
         if (!$this->_all_room_limit) {
            $query .= ' AND user.status >= "2"';
         }
      }
      if ( !empty($this->_search_array) ) {
         $query .= ' LEFT JOIN user AS user2 ON user2.context_id='.$this->_db_table.'.item_id AND user2.deletion_date IS NULL AND user2.is_contact="1"';
      }
      if ( isset($this->_topic_limit) ) {
         $query .= ' LEFT JOIN link_items AS l41 ON ( l41.deletion_date IS NULL AND ((l41.first_item_id='.$this->_db_table.'.item_id AND l41.second_item_type="'.CS_TOPIC_TYPE.'"))) ';
         $query .= ' LEFT JOIN link_items AS l42 ON ( l42.deletion_date IS NULL AND ((l42.second_item_id='.$this->_db_table.'.item_id AND l42.first_item_type="'.CS_TOPIC_TYPE.'"))) ';
      }

      // time (clock pulses)
      if ( isset($this->_time_limit) ) {
         if ($this->_time_limit != -1) {
            $query .= ' INNER JOIN links AS room_time ON room_time.from_item_id=room.item_id AND room_time.link_type="in_time"';
            $query .= ' INNER JOIN labels AS time_label ON room_time.to_item_id=time_label.item_id AND time_label.type="time"';
         } else {
            $query .= ' LEFT JOIN links AS room_time ON room_time.from_item_id=room.item_id AND room_time.link_type="in_time"';
         }
      }

      $query .= ' WHERE 1';
      if (isset($this->_room_type)) {
         $query .= ' AND '.$this->_db_table.'.type = "'.encode(AS_DB,$this->_room_type).'"';
      }
      if ( isset($this->_topic_limit) ){
         if($this->_topic_limit == -1){
            $query .= ' AND (l41.first_item_id IS NULL AND l41.second_item_id IS NULL)';
            $query .= ' AND (l42.first_item_id IS NULL AND l42.second_item_id IS NULL)';
         } else {
            $query .= ' AND ((l41.first_item_id = "'.encode(AS_DB,$this->_topic_limit).'" OR l41.second_item_id = "'.encode(AS_DB,$this->_topic_limit).'")';
            $query .= ' OR (l42.first_item_id = "'.encode(AS_DB,$this->_topic_limit).'" OR l42.second_item_id = "'.encode(AS_DB,$this->_topic_limit).'"))';
         }
      }

      // insert limits into the select statement
      if ( isset($this->_room_limit) ) {
         $query .= ' AND '.$this->_db_table.'.context_id = "'.encode(AS_DB,$this->_room_limit).'"';
      }
      if ( $this->_delete_limit == true ) {
         $query .= ' AND '.$this->_db_table.'.deleter_id IS NULL AND '.$this->_db_table.'.deletion_date IS NULL';
      }
      if ( isset($this->_age_limit) ) {
         $query .= ' AND '.$this->_db_table.'.modification_date >= DATE_SUB(CURRENT_DATE,interval '.encode(AS_DB,$this->_age_limit).' day)';
      }
      if ( isset($this->_existence_limit) ) {
         $query .= ' AND '.$this->_db_table.'.creation_date >= DATE_SUB(CURRENT_DATE,interval '.encode(AS_DB,$this->_existence_limit).' day)';
      }

      if (isset($this->_status_limit)) {
         if ($this->_status_limit != 5) {
            $query .= ' AND '.$this->_db_table.'.status = "'.encode(AS_DB,$this->_status_limit).'"';
         } elseif ($this->_status_limit == 5) {
            $query .= ' AND ( '.$this->_db_table.'.status = "1" OR '.$this->_db_table.'.status = "2")';
         }
      }

      if (isset($this->_search_array) AND !empty($this->_search_array)) {
         $query .= ' AND (';
         $field_array = array('TRIM(CONCAT(user2.firstname," ",user2.lastname))',$this->_db_table.'.title');
         $search_limit_query_code = $this->_generateSearchLimitCode($field_array);
         $query .= $search_limit_query_code;
         $query .= ' )';
      }

      if (!empty($this->_user_id_limit)) {
         $query .= ' AND user.user_id="'.encode(AS_DB,$this->_user_id_limit).'"';
      }
      if ( !empty($this->_auth_source_limit) ) {
         $query .= ' AND user.auth_source="'.encode(AS_DB,$this->_auth_source_limit).'"';
      }

      // time (clock pulses)
      if ( isset($this->_time_limit) ) {
         if ( $this->_time_limit != -1 ) {
            $query .= ' AND time_label.item_id = "'.encode(AS_DB,$this->_time_limit).'"';
         } else {
            $query .= ' AND room_time.to_item_id IS NULL';
         }
      }

      // template
      if ( isset($this->_template_limit) ) {
         $query .= ' AND '.$this->_db_table.'.template = "'.encode(AS_DB,$this->_template_limit).'"';
      }

      // id_array_limit
      if ( !empty($this->_id_array_limit) ) {
         $query .= ' AND '.$this->_db_table.'.item_id IN ('.implode(", ",encode(AS_DB,$this->_id_array_limit)).')';
      }

      // project room limit
      if ( isset($this->_project_room_limit) and !empty($this->_project_room_limit) ) {
         $query .= ' AND extras LIKE "%<PROJECT_ROOM_ITEM_ID>'.encode(AS_DB,$this->_project_room_limit).'</PROJECT_ROOM_ITEM_ID>%"';
      }

      if ( isset($this->_sort_order) ) {
         if ($this->_sort_order == 'title_rev') {
            $query .= ' ORDER BY '.$this->_db_table.'.title DESC';
         } elseif ($this->_sort_order == 'activity') {
            $query .= ' ORDER BY '.$this->_db_table.'.activity ASC,'.$this->_db_table.'.title';
         } elseif ($this->_sort_order == 'activity_rev') {
            $query .= ' ORDER BY '.$this->_db_table.'.activity DESC,'.$this->_db_table.'.title';
         } else {
            $query .= ' ORDER BY '.$this->_db_table.'.title ASC';
         }
      } elseif (isset($this->_order)) {
         if ($this->_order == 'date') {
            $query .= ' ORDER BY '.$this->_db_table.'.creation_date DESC, '.$this->_db_table.'.title ASC';
         } elseif ($this->_order == 'creator') {
            $query .= ' ORDER BY user.lastname, '.$this->_db_table.'.creation_date DESC';
         } elseif ($this->_order == 'status') {
            $query .= ' ORDER BY '.$this->_db_table.'.status, '.$this->_db_table.'.title ASC';
         } elseif ($this->_order == 'activity') {
            $query .= ' ORDER BY '.$this->_db_table.'.activity ASC, '.$this->_db_table.'.title ASC';
         } elseif ($this->_order == 'activity_rev') {
            $query .= ' ORDER BY '.$this->_db_table.'.activity DESC,'.$this->_db_table.'.title';
         } else {
            $query .= ' ORDER BY '.$this->_db_table.'.title, '.$this->_db_table.'.creation_date DESC';
         }
      } else {
         $query .= ' ORDER BY '.$this->_db_table.'.title, '.$this->_db_table.'.creation_date DESC';
      }

      if ($mode == 'select') {
         if (isset($this->_interval_limit) and isset($this->_from_limit)) {
            $query .= ' LIMIT '.encode(AS_DB,$this->_from_limit).', '.encode(AS_DB,$this->_interval_limit);
         }
      }

      // perform query
      $result = $this->_db_connector->performQuery($query);
      if (!isset($result)) {
         include_once('functions/error_functions.php');trigger_error('Problems selecting '.$this->_db_table.' items from query: "'.$query.'"',E_USER_WARNING);
      } else {
         return $result;
      }
   }

   public function getSortedItemList($id_array,$sortBy) {
      if (empty($id_array)) {
         return new cs_list();
      } else {
         $query = 'SELECT * FROM '.$this->_db_table.' WHERE '.$this->_db_table.'.item_id IN ("'.implode('", "',encode(AS_DB,$id_array)).'") AND '.$this->_db_table.'.type LIKE "grouproom"';
         $query .= " ORDER BY ".$sortBy;
         $result = $this->_db_connector->performQuery($query);
         if (!isset($result)) {
            include_once('functions/error_functions.php');trigger_error('Problems selecting list of '.$this->_room_type.' items from query: "'.$query.'"',E_USER_WARNING);
         } else {
            $list = new cs_list();
            foreach ($result as $rs) {
               $list->add($this->_buildItem($rs));
            }
         }
         return $list;
      }
   }

   public function getRelatedGroupRoomListForUser ($user_item) {
      return $this->_getRelatedContextListForUser($user_item->getUserID(),$user_item->getAuthSource(),$this->_environment->getCurrentPortalID());
   }

   public function getRelatedGroupRoomListForUserSortByTime ($user_item) {
      return $this->_getRelatedContextListForUserSortByTime($user_item->getUserID(),$user_item->getAuthSource(),$this->_environment->getCurrentPortalID());
   }

   public function getItemList($id_array) {
      return $this->_getItemList(CS_ROOM_TYPE, $id_array);
   }


   ##########################################################
   # statistic functions
   ##########################################################

   public function getCountAllGroupRooms ($start, $end) {
      $retour = 0;

      $query = "SELECT count(".$this->_db_table.".item_id) as number FROM ".$this->_db_table." WHERE context_id = '".encode(AS_DB,$this->_room_limit)."' and creation_date < '".encode(AS_DB,$end)."' and status != '4'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting all group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountOpenGroupRooms ($start, $end) {
      $retour = 0;

      $query = "SELECT count(".$this->_db_table.".item_id) as number FROM ".$this->_db_table." WHERE context_id = '".encode(AS_DB,$this->_room_limit)."' AND (status = '1' or status = '3') and (deletion_date IS NULL or deletion_date > '".encode(AS_DB,$end)."') and creation_date < '".encode(AS_DB,$end)."'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting open group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountClosedGroupRooms ($start, $end) {
      $retour = 0;

      $query = "SELECT count(".$this->_db_table.".item_id) as number FROM ".$this->_db_table." WHERE context_id = '".encode(AS_DB,$this->_room_limit)."' AND status = '2' and (deletion_date IS NULL or deletion_date > '".encode(AS_DB,$end)."') and creation_date < '".encode(AS_DB,$end)."'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting open group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountUsedGroupRooms ($start, $end) {
      $retour = 0;

      $query  = "SELECT count(DISTINCT ".$this->_db_table.".item_id) as number FROM ".$this->_db_table.", user";
      $query .= " WHERE user.context_id=".$this->_db_table.".item_id AND user.lastlogin > '".encode(AS_DB,$start)."' and user.creation_date < '".encode(AS_DB,$end)."'";
      $query .= " AND ".$this->_db_table.".context_id = '".encode(AS_DB,$this->_room_limit)."' AND (".$this->_db_table.".status = '1' or ".$this->_db_table.".status = '3') and ".$this->_db_table.".deletion_date IS NULL and ".$this->_db_table.".creation_date < '".encode(AS_DB,$end)."'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting used group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountUsedClosedGroupRooms ($start, $end) {
      $retour = 0;

      $query  = "SELECT count(DISTINCT ".$this->_db_table.".item_id) as number FROM ".$this->_db_table.", user";
      $query .= " WHERE user.context_id=".$this->_db_table.".item_id AND user.lastlogin > '".encode(AS_DB,$start)."' and user.creation_date < '".encode(AS_DB,$end)."'";
      $query .= " AND ".$this->_db_table.".context_id = '".encode(AS_DB,$this->_room_limit)."' AND ".$this->_db_table.".status = '2' and ".$this->_db_table.".deletion_date IS NULL and ".$this->_db_table.".creation_date < '".encode(AS_DB,$end)."'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting used group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountActiveGroupRooms ($start, $end) {
      $list = $this->getActiveGroupRooms($start,$end);
      if ($list->isEmpty()) {
         return 0;
      } else {
         return $list->getCount();
      }
   }

   ##########################################

   public function getActiveGroupRooms ($start, $end) {
      $list = $this->getUsedGroupRooms($start,$end);

      // delete rooms that are not really active
      $retour_list = new cs_list();
      if (!$list->isEmpty()) {
         $item = $list->getFirst();
         while ($item) {
            if ($item->isActive($start,$end)) {
               $retour_list->add($item);
            }
            $item = $list->getNext();
         }
      }

      return $retour_list;
   }

   public function getUsedGroupRooms ($start, $end) {
      $list = new cs_list();

      $query  = "SELECT ".$this->_db_table.".* FROM ".$this->_db_table.", user";
      $query .= " WHERE user.context_id=".$this->_db_table.".item_id AND user.lastlogin > '".encode(AS_DB,$start)."' and user.creation_date < '".encode(AS_DB,$end)."'";
      $query .= " AND ".$this->_db_table.".context_id = '".encode(AS_DB,$this->_room_limit)."' AND ".$this->_db_table.".status != '4' and ".$this->_db_table.".deletion_date IS NULL and ".$this->_db_table.".creation_date < '".encode(AS_DB,$end)."'";
      $query .= " GROUP BY ".$this->_db_table.".item_id";
      $query .= " ORDER BY ".$this->_db_table.".title";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting used group rooms '.$this->_db_table.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ( $result as $rs ) {
            $list->add($this->_buildItem($rs));
         }
      }

      return $list;
   }

  /** create a project - internal, do not use -> use method save
    * this method creates a project
    *
    * @param object cs_item project_item the project
    */
   public function _create ($item) {
      $query = 'INSERT INTO items SET '.
               'context_id="'.encode(AS_DB,$item->getContextID()).'",'.
               'modification_date="'.getCurrentDateTimeInMySQL().'",'.
               'type="'.encode(AS_DB,$this->_room_type).'"';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems creating '.$this->_db_table.' item from query: "'.$query.'"',E_USER_WARNING);
         $this->_create_id = NULL;
      } else {
         $this->_create_id = $result;
         $item->setItemID($this->getCreateID());
         $this->_new($item);
      }
      unset($item);
   }

  /** update a room - internal, do not use -> use method save
    * this method updates a room
    *
    * @param object cs_context_item a commsy room
    */
   public function _update ($item) {
      if ( $this->_update_with_changing_modification_information ) {
         parent::_update($item);
      }
      $query  = 'UPDATE '.$this->_db_table.' SET ';
      if ( $this->_update_with_changing_modification_information ) {
         $query .= 'modification_date="'.getCurrentDateTimeInMySQL().'",';
         $modifier_id = $this->_current_user->getItemID();
         if ( !empty($modifier_id) ) {
            $query .= 'modifier_id="'.encode(AS_DB,$modifier_id).'",';
         }
      }

      if ( $item->isOpenForGuests() ) {
         $open_for_guests = 1;
      } else {
         $open_for_guests = 0;
      }
      if ( $item->isContinuous() ) {
         $continuous = 1;
      } else {
         $continuous = -1;
      }
      if ( $item->isTemplate() ) {
         $template = 1;
      } else {
         $template = -1;
      }

      if ( $item->getActivityPoints() ) {
         $activity = $item->getActivityPoints();
      } else {
         $activity = '0';
      }

      if ( $item->getPublic() ) {
         $public = '1';
      } else {
         $public = '0';
      }

      $query .= 'title="'.encode(AS_DB,$item->getTitle()).'",'.
#                "short_title='".encode(AS_DB,$item->getShortTitle())."',".
                "extras='".encode(AS_DB,serialize($item->getExtraInformation()))."',".
                "status='".encode(AS_DB,$item->getStatus())."',".
                "activity='".encode(AS_DB,$activity)."',".
                "public='".encode(AS_DB,$public)."',".
                "continuous='".$continuous."',".
                "template='".$template."',".
                "is_open_for_guests='".$open_for_guests."'".
                ' WHERE item_id="'.encode(AS_DB,$item->getItemID()).'"';

      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) or !$result ) {
         include_once('functions/error_functions.php');trigger_error('Problems updating '.$this->_db_table.' item from query: "'.$query.'"',E_USER_WARNING);
      }
      unset($item);
   }

   /** creates a new room - internal, do not use -> use method save
    * this method creates a new room
    *
    * @param object cs_context_item (upper class) a commsy room
    */
   public function _new ($item) {
      $current_datetime = getCurrentDateTimeInMySQL();
      $user = $item->getCreatorItem();
      if ( empty($user) ) {
         $user = $this->_environment->getCurrentUserItem();
      }

      if ( $item->isContinuous() ) {
         $continuous = 1;
      } else {
         $continuous = -1;
      }

      if ( $item->getPublic() ) {
         $public = $item->getPublic();
      } else {
         $public = 0;
      }

      $query = 'INSERT INTO '.$this->_db_table.' SET '.
               'item_id="'.encode(AS_DB,$item->getItemID()).'",'.
               'context_id="'.encode(AS_DB,$item->getContextID()).'",'.
               'creator_id="'.encode(AS_DB,$user->getItemID()).'",'.
               'modifier_id="'.encode(AS_DB,$user->getItemID()).'",'.
               'creation_date="'.$current_datetime.'",'.
               'modification_date="'.$current_datetime.'",'.
               'title="'.encode(AS_DB,$item->getTitle()).'",'.
#               'short_title="'.encode(AS_DB,$item->getShortTitle()).'",'.
               'extras="'.encode(AS_DB,serialize($item->getExtraInformation())).'",'.
               'public="'.encode(AS_DB,$public).'",'.
               'type="'.encode(AS_DB,$item->getRoomType()).'",'.
               'continuous="'.$continuous.'",'.
               'status="'.encode(AS_DB,$item->getStatus()).'"';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems creating new '.$this->_room_type.' item from query: "'.$query.'"', E_USER_ERROR);
      } else {
         $item->setCreationDate($current_datetime);
      }
      unset($user);
      unset($item);
   }

   ########################################################
   # statistic functions
   ########################################################

   public function getCountGroupRooms ($start, $end) {
      $retour = 0;

      $query  = 'SELECT count('.$this->_db_table.'.item_id) as number FROM '.$this->_db_table;
      $query .= ' WHERE '.$this->_db_table.'.type = "'.encode(AS_DB,$this->_room_type).'" AND '.$this->_db_table.'.context_id = "'.encode(AS_DB,$this->_room_limit).'" AND (('.$this->_db_table.'.creation_date > "'.encode(AS_DB,$start).'" AND '.$this->_db_table.'.creation_date < "'.encode(AS_DB,$end).'") OR ('.$this->_db_table.'.modification_date > "'.encode(AS_DB,$start).'" AND '.$this->_db_table.'.modification_date < "'.encode(AS_DB,$end).'"))';

      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting all '.$this->_room_type.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }

      return $retour;
   }

   public function getCountNewGroupRooms ($start, $end) {
      $retour = 0;

      $query = "SELECT count(".$this->_db_table.".item_id) as number FROM ".$this->_db_table." WHERE ".$this->_db_table.".type = '".encode(AS_DB,$this->_room_type)."' AND ".$this->_db_table.".context_id = '".encode(AS_DB,$this->_room_limit)."' and ".$this->_db_table.".creation_date > '".encode(AS_DB,$start)."' and ".$this->_db_table.".creation_date < '".encode(AS_DB,$end)."'";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting '.$this->_room_type.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }
      return $retour;
   }

   public function getCountModGroupRooms ($start, $end) {
      $retour = 0;

      $query = "SELECT count(labels.item_id) as number FROM ".$this->_db_table." WHERE ".$this->_db_table.".type = '".encode(AS_DB,$this->_room_type)."' AND ".$this->_db_table.".context_id = '".encode(AS_DB,$this->_room_limit)."' and ".$this->_db_table.".modification_date > '".encode(AS_DB,$start)."' and ".$this->_db_table.".modification_date < '".encode(AS_DB,$end)."' and ".$this->_db_table.".modification_date != ".$this->_db_table.".creation_date";
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems counting '.$this->_room_type.' from query: "'.$query.'"',E_USER_WARNING);
      } else {
         foreach ($result as $rs) {
            $retour = $rs['number'];
         }
      }
      return $retour;
   }

   public function getRelatedGroupListForUser ($user_item) {
      return $this->_getRelatedContextListForUser($user_item->getUserID(),$user_item->getAuthSource(),$this->_environment->getCurrentPortalID());
   }


}
?>