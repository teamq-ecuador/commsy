<?php
	require_once('classes/controller/cs_ajax_controller.php');

	class cs_ajax_accounts_controller extends cs_ajax_controller {
		/**
		 * constructor
		 */
		public function __construct(cs_environment $environment) {
			// call parent
			parent::__construct($environment);
		}
		
		public function actionGetInitialData() {
			$return = array();
			
			$translator = $this->_environment->getTranslationObject();
			
			$translations = array();
			$translations['USER_LIST_ACTION_DELETE_ACCOUNT'] = $translator->getMessage('USER_LIST_ACTION_DELETE_ACCOUNT');
			$translations['USER_LIST_ACTION_LOCK_ACCOUNT'] = $translator->getMessage('USER_LIST_ACTION_LOCK_ACCOUNT');
			$translations['USER_LIST_ACTION_FREE_ACCOUNT'] = $translator->getMessage('USER_LIST_ACTION_FREE_ACCOUNT');
			$translations['USER_LIST_ACTION_STATUS_USER'] = $translator->getMessage('USER_LIST_ACTION_STATUS_USER');
			$translations['USER_LIST_ACTION_STATUS_MODERATOR'] = $translator->getMessage('USER_LIST_ACTION_STATUS_MODERATOR');
			$translations['USER_LIST_ACTION_STATUS_CONTACT_MODERATOR'] = $translator->getMessage('USER_LIST_ACTION_STATUS_CONTACT_MODERATOR');
			$translations['USER_LIST_ACTION_STATUS_NO_CONTACT_MODERATOR'] = $translator->getMessage('USER_LIST_ACTION_STATUS_NO_CONTACT_MODERATOR');
			$translations['USER_LIST_ACTION_EMAIL_SEND'] = $translator->getMessage('USER_LIST_ACTION_EMAIL_SEND');
			
			$return['translations'] = $translations;
			$return['success'] = true;
			
			echo json_encode($return);
		}

		public function actionPerformUserAction() {
			$return = array();
			
			$user_manager = $this->_environment->getUserManager();
			
			// get request data
			$ids = $this->_data['ids'];
			$action = $this->_data['action'];
			
			// prevent removing all moderators
			$user_manager->resetLimits();
			$user_manager->setContextLimit($this->_environment->getCurrentContextID());
			$user_manager->setModeratorLimit();
			
			$moderator_ids = $user_manager->getIds();
			if(!is_array($moderator_ids)) $moderator_ids = array();
			$room_moderator_count = count($moderator_ids);
			
			$selected_moderator_count = count(array_intersect($selected_ids, $moderator_ids));
			$room_moderator_count = count($moderator_ids);
			
			switch($action) {
				case "delete":
				case "lock":
				case "free":
				case "user_status_user":
					if($room_moderator_count - $selected_moderator_count < 1) {
						$this->setErrorReturn('103', 'you are trying to remove all moderators', array());
						echo $this->_return;
						exit;
					}
					break;
				case "status_contact_moderator":
				
				case "status_no_contact_moderator":
			}
			
			
			
			
			/*
			 * 

         case 30:
            $action = 'USER_MAKE_CONTACT_PERSON';
            $error = false;
            $user_manager = $environment->getUserManager();
            $array_login_id = array();
            foreach ($selected_ids as $id) {
               $user = $user_manager->getItem($id);
               if ( !$user->isUser() ) {
                  $error = true;
                  $array_login_id[] = $id;
               }
            }
            if ($error) {
               $error_text_on_selection = $translator->getMessage('INDEX_USER_MAKE_CONTACT_ERROR');
               $selected_ids = array_diff($selected_ids,$array_login_id);
               $view->setCheckedIDs($selected_ids);
            }
            break;
         case 31:
            $action = 'USER_UNMAKE_CONTACT_PERSON';
            $error = false;
            $user_manager = $environment->getUserManager();
            $array_login_id = array();
            foreach ($selected_ids as $id) {
               $user = $user_manager->getItem($id);
               if ( !$user->isContact() ) {
                  $error = true;
                  $array_login_id[] = $id;
               }
            }
            if ($error) {
               $error_text_on_selection = $translator->getMessage('INDEX_USER_UNMAKE_CONTACT_ERROR');
               $selected_ids = array_diff($selected_ids,$array_login_id);
               $view->setCheckedIDs($selected_ids);
            }
            break;
         
      }
      if (!isset($error) or !$error) {
         $current_user = $environment->getCurrentUser();
         $user_item_id = $current_user->getItemID();

         $action_array = array();
         $action_array['user_item_id']    = $user_item_id;
         $action_array['action']          = $action;
         $action_array['backlink']['cid'] = $environment->getCurrentContextID();
         $action_array['backlink']['mod'] = $environment->getCurrentModule();
         $action_array['backlink']['fct'] = $environment->getCurrentFunction();
         $action_array['backlink']['par'] = '';
         $action_array['selected_ids']    = $selected_ids;
         $params = array();
         $session->setValue('index_action',$action_array);
         redirect( $environment->getCurrentContextID(),
                   $environment->getCurrentModule(),
                   'action',
                   $params);

      } // end if of $error
   } // end if (perform list actions)
			 */

			

			$return['success'] = true;

			echo json_encode($return);
		}

		public function actionPerformRequest() {
			$return = array();

			$user_manager = $this->_environment->getUserManager();
			$translator = $this->_environment->getTranslationObject();

			// get request data
			$current_page = $this->_data['current_page'];
			$restrictions = $this->_data['restrictions'];
			
			// get data from db
			$user_manager->reset();
			$user_manager->setContextLimit($this->_environment->getCurrentContextID());
			$count_all = $user_manager->getCountAll();
			
			// set restrictions
			/*
			 * if ( !empty($sort) ) {
			      $user_manager->setSortOrder($sort);
			   }
			   
			   if ( !empty($sel_auth_source)
			        and  $sel_auth_source != -1
			      ) {
			      $user_manager->setAuthSourceLimit($sel_auth_source);
			   }
			 */
			
			if(!empty($restrictions['search'])) $user_manager->setSearchLimit($restrictions['search']);
			
			if(!empty($restrictions['status'])) {
				if($restrictions['status'] == '10') $user_manager->setContactModeratorLimit();
				else $user_manager->setStatusLimit($restrictions['status']);
			}
			
			$ids = $user_manager->getIDArray();
			$count_all_shown = count($ids);
			
			$interval = CS_LIST_INTERVAL;
			$from = $current_page * $interval;
			
			$user_manager->setIntervalLimit($from, $interval);
			$user_manager->select();
			
			// get user list
			$user_list = $user_manager->get();
			
			// prepare return
			$return['list'] = array();
			$item = $user_list->getFirst();
			while($item) {
				$entry = array();
				
				$status = '';
				if($item->isModerator()) $status = $translator->getMessage('USER_STATUS_MODERATOR');
				elseif($item->isUser()) $status = $translator->getMessage('USER_STATUS_USER');
				elseif($item->isRequested()) $status = $translator->getMessage('USER_STATUS_REQUESTED');
				else {
					$status = $translator->getMessage('USER_STATUS_REJECTED');
				}
				
				if($item->isContact()) $status .= ' [' . $translator->getMessage('USER_STATUS_CONTACT_SHORT') . ']';
				
				$entry['item_id']			= $item->getItemID();
				$entry['fullname']			= $item->getFullName();
				$entry['email']				= $item->getEmail();
				$entry['status']			= $status;
			
				$return['list'][] = $entry;
				$item = $user_list->getNext();
			}
			$return['paging']['pages'] = ceil($count_all_shown / $interval);

			$return['success'] = true;

			echo json_encode($return);
		}

		/*
		 * every derived class needs to implement an processTemplate function
		 */
		public function process() {
			// check rights
			$current_context = $this->_environment->getCurrentContextItem();
			$current_user = $this->_environment->getCurrentUserItem();
				
			$error = false;
			if(isset($current_context) && !$current_context->isOpen() && !$current_context->isTemplate()) {
				// room is closed
				$error = true;
			} elseif(!$current_user->isModerator()) {
				// not allowed
				$error = true;
			}
				
			if($error === true) exit;
			
			// call parent
			parent::process();
		}
	}