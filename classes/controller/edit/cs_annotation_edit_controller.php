<?php
	require_once('classes/controller/cs_edit_controller.php');

	class cs_annotation_edit_controller extends cs_edit_controller {
		private $_item = null;
		
		/**
		 * constructor
		 */
		public function __construct(cs_environment $environment) {
			// call parent
			parent::__construct($environment);
			
			$this->_tpl_file = 'annotation_edit';
		}
		
		
		/*
		 * // Get the translator object
				$translator = $environment->getTranslationObject();
				// Function used for redirecting to connected rubrics
				function attach_redirect ($rubric_type, $current_iid) {
				   global $session, $environment;
				   $infix = '_'.$rubric_type;
				   $session->setValue($current_iid.'_post_vars', $_POST);
				   if ( isset($_POST[$rubric_type]) ) {
				      $session->setValue($current_iid.$infix.'_attach_ids', $_POST[$rubric_type]);
				   } else {
				      $session->setValue($current_iid.$infix.'_attach_ids', array());
				   }
				   $session->setValue($current_iid.$infix.'_back_module', 'annotation');
				   $params = array();
				   $params['ref_iid'] = $current_iid;
				   $params['mode'] = 'formattach';
				   redirect($environment->getCurrentContextID(), 'material', 'index', $params);
				}
				
				function attach_return ($rubric_type, $current_iid) {
				   global $session;
				   $infix = '_'.$rubric_type;
				   $attach_ids = $session->getValue($current_iid.$infix.'_attach_ids');
				   $session->unsetValue($current_iid.'_post_vars');
				   $session->unsetValue($current_iid.$infix.'_attach_ids');
				   $session->unsetValue($current_iid.$infix.'_back_module');
				   return $attach_ids;
				}
		 */

		/*
		 * every derived class needs to implement an processTemplate function
		 */
		public function processTemplate() {
			// call parent
			parent::processTemplate();
			
			// assign rubric to template
			$this->assign('room', 'rubric', CS_DISCUSSION_TYPE);
		}
		
		protected function getFieldInformation() {
			return array(
				array(	'name'		=> 'title',
						'type'		=> 'text',
						'mandatory' => true),
				array(	'name'		=> 'description',
						'type'		=> 'text',
						'mandatory'	=> false)
			);
		}
		
		/*****************************************************************************/
		/******************************** ACTIONS ************************************/
		/*****************************************************************************/
		public function actionEdit() {
			$session = $this->_environment->getSessionItem();
			
			// get the current user and room
			$current_user = $this->_environment->getCurrentUserItem();
			$room_item = $this->_environment->getCurrentContextItem();
			
			// get post data
			$this->getPostData();
			
			// $with_anchor = false;
			
			// coming back from attaching something
			$backfrom = false;
			if(!empty($_GET['backfrom'])) {
				$backfrom = $_GET['backfrom'];
			}
			
			// load item from database
			$annotation_item = null;
			if($this->_item_id !== 'NEW') {
				$annotation_manager = $this->_environment->getAnnotationManager();
				$annotation_item = $annotation_manager->getItem($this->_item_id);
			}
			
			// get history from session
			$history = $session->getValue('history');
			
			// save the history
			if(isset($_GET['mode']) && $_GET['mode'] === 'annotate' && $history[0]['module'] !== 'annotation') {
				$session->setValue('annotation_history_context', $history[0]['context']);
				$session->setValue('annotation_history_module', $history[0]['module']);
				$session->setValue('annotation_history_function', $history[0]['function']);
				$session->setValue('annotation_history_parameter', $history[0]['parameter']);
			}
			
			// check access rights
			$item_manager = $this->_environment->getItemManager();
			if($this->_item_id !== 'NEW' && !isset($annotation_item)) {
				/*
				 * $params = array();
				   $params['environment'] = $environment;
				   $params['with_modifying_actions'] = true;
				   $errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
				   unset($params);
				   $errorbox->setText($translator->getMessage('ITEM_DOES_NOT_EXIST', $current_iid));
				   $page->add($errorbox);
				 */
			} elseif(	!(($this->_item_id === 'NEW' && $current_user->isUser()) ||
						($this->_item_id !== 'NEW' && isset($annotation_item) && $annotation_item->mayEdit($current_user)) ||
						($this->_item_id === 'NEW' && isset($_GET['ref_iid']) && $item_manager->getExternalViewerForItem($_GET['ref_iid'], $current_user->getUserID())))) {
						/*
						 *    $params = array();
							   $params['environment'] = $environment;
							   $params['with_modifying_actions'] = true;
							   $errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
							   unset($params);
							   $errorbox->setText($translator->getMessage('LOGIN_NOT_ALLOWED'));
							   $page->add($errorbox);
						 */	
			} else {
				$translator = $this->_environment->getTranslationObject();
				
				// cancel editing
				if(isOption($this->_command, CS_OPTION_CANCEL)) {
					$context = $session->getValue('annotation_history_context');
					$module = $session->getValue('annotation_history_module');
					$function = $session->getValue('annotation_history_function');
					$param = $session->getValue('annotation_history_parameter');
					
					$anchor = '';
					if(isset($annotation_item) && $annotation_item !== null) {
						$anchor = 'anchor' . $annotation_item->getItemID();
					}
					
					$this->cleanup_session($this->_item_id);
					redirect($context, $module, $function, $param, $anchor);
					exit;
				}
				
				// delete item
				if(isOption($this->_command, CS_OPTION_DELETE)) {
					// go back to the origin
					$context = $session->getValue('annotation_history_context');
					$module = $session->getValue('annotation_history_module');
					$function = $session->getValue('annotation_history_function');
					$param = $session->getValue('annotation_history_parameter');
					
					$this->cleanup_session($this->_item_id);
					$annotation_item->delete();
					redirect($context, $module, $function, $param);
					exit;
				}
				
				// show form and/or save item
				
				/*
				 * // Initialize the form
				      $class_params= array();
				      $class_params['environment'] = $environment;
				      $form = $class_factory->getClass(ANNOTATION_FORM,$class_params);
				      unset($class_params);
				
				      if ( !empty($_GET['mode'])
				           and $_GET['mode'] == 'annotate'
				           and !empty($_POST)
				         ) {
				         $form->setDetailMode(1);
				      }
				 */
				
				$post_file_ids = array();
				if(isset($_POST['filelist'])) {
					$post_file_ids = $_POST['filelist'];
				}
				
				/*
				 * // Define rubric connections
				      $rubric_connection = array();
				      $rubric_connection[] = CS_MATERIAL_TYPE;
				      $form->setRubricConnections($rubric_connection);
				 */
				
				// load form data from postvars
				if(!empty($_POST)) {
					$session_post_vars = $_POST;
					if(isset($post_file_ids) && !empty($post_file_ids)) {
						$session_post_vars['filelist'] = $post_file_ids;
					}
					//$form->setFormPost($session_post_vars);
				}
				
				/*
				 * // Back from multi upload
				      elseif ( $from_multiupload ) {
				         $session_post_vars = array();
				         if ( isset($post_file_ids) AND !empty($post_file_ids) ) {
				            $session_post_vars['filelist'] = $post_file_ids;
				         }
				         $form->setFormPost($session_post_vars);
				      }
				 */
				
				// load form data from database
				elseif(isset($annotation_item)) {
					/*
					 * $form->setItem($annotation_item);
				
				         // Files
				         $file_list = $annotation_item->getFileList();
				         if ( !$file_list->isEmpty() ) {
				            $file_array = array();
				            $file_item = $file_list->getFirst();
				            while ( $file_item ) {
				               $temp_array = array();
				               $temp_array['name'] = $file_item->getDisplayName();
				               $temp_array['file_id'] = (int)$file_item->getFileID();
				               $file_array[] = $temp_array;
				               $file_item = $file_list->getNext();
				            }
				            if ( !empty($file_array)) {
				               $session->setValue($environment->getCurrentModule().'_add_files', $file_array);
				            }
				         }
					 */
				}
				
				// create data for a new item
				elseif($this->_item_id === 'NEW') {
					/*
					 * $form->setRefID($_GET['ref_iid']);
				         if ( !empty($_GET['version']) ) {
				            $form->setVersion($_GET['version']);
				         }
					 */
				}
				
				else {
					include_once('functions/error_functions.php');
					trigger_error('annotation_edit was called in an unknown manner', E_USER_ERROR);
				}
				
				if($session->issetValue($this->_environment->getCurrentModule() . '_add_files')) {
					//$form->setSessionFileArray($session->getValue($environment->getCurrentModule().'_add_files'));
				}
				
				/*
				 * $form->prepareForm();
				      $form->loadValues();
				 */
				
				// save item
				if(	$this->_command !== null &&
					(isOption($this->_command, CS_OPTION_SAVE) ||
					isOption($this->_command, CS_OPTION_CHANGE) ||
					isOption($this->_command, CS_OPTION_NEW))) {
					
					if($this->checkFormData()) {
						$user = $this->_environment->getCurrentUserItem();
						
						// create new item
						if($annotation_item === null) {
							$annotation_manager = $this->_environment->getAnnotationManager();
							$annotation_item = $annotation_manager->getNewItem();
							$annotation_item->setContextID($this->_environment->getCurrentContextID());
							$annotation_item->setCreatorItem($user);
							$annotation_item->setCreationDate(getCurrentDateTimeInMySQL());
						}
						
						// set modificator and modification date
						$annotation_item->setModificatorItem($user);
						$annotation_item->setModificationDate(getCurrentDateTimeInMySQL());
						
						// set attributes
						if(isset($_POST['form_data']['title'])) {
							$annotation_item->setTitle($_POST['form_data']['title']);
						} elseif(isset($_POST['form_data']['annotation_title'])) {
							$annotation_item->setTitle($_POST['form_data']['title']);
						}
						
						if(isset($_POST['form_data']['description_annotation'])) {
							$annotation_item->setDescription($_POST['form_data']['description_annotation']);
						} elseif(isset($_POST['form_data']['annotation_description'])) {
							$annotation_item->setDescription($_POST['form_data']['annotation_description']);
						}
						
						if(!empty($_POST['ref_iid'])) {
							$annotation_item->setLinkedItemID($_POST['ref_iid']);
						}
						
						if(!empty($_POST['version'])) {
							$annotation_item->setLinkedVersionID($_POST['version']);
						}
						
						// set links to connected rubrics
						if(isset($_POST[CS_MATERIAL_TYPE])) {
							$annotation_item->setMaterialListByID($_POST[CS_MATERIAL_TYPE]);
						} else {
							$annotation_item->setMaterialListByID(array());
						}
						
						// files
						$this->setFilesForItem($annotation_item, $post_file_ids);
						
						// save item
						$annotation_item->save();
						
						// add modifier to all users who ever edited this item
						$manager = $this->_environment->getLinkModifierItemManager();
						$manager->markEdited($annotation_item->getItemID());
						
						// reset id array
						$session->setValue('cid' . $this->_environment->getCurrentContextID() . '_annotation_index_ids', array($annotation_item->getItemID()));
						
						$context = $session->getValue('annotation_history_context');
						$module = $session->getValue('annotation_history_module');
						$function = $session->getValue('annotation_history_function');
						$param = $session->getValue('annotation_history_parameter');
						
						if(isset($annotation_item) && $annotation_item !== null) {
							$anchor = 'anchor' . $annotation_item->getItemID();
						} else {
							$anchor = '';
						}
						
						// redirect
						$this->cleanup_session($this->_item_id);
						redirect($context, $module, $function, $param, $anchor);
					}/* elseif ( $form->isDetailModeActive() ) {
				            $form->reset();
				            if ( !empty($_POST) ) {
				               $form->setFormPost($_POST);
				            }
				            $form->prepareForm();
				            $form->loadValues();
				            $form->check();
				         }*/
				}
				
				/*
				 * // Display form
				      $params = array();
				      $params['environment'] = $environment;
				      $params['with_modifying_actions'] = true;
				      $form_view = $class_factory->getClass(FORM_VIEW,$params);
				      unset($params);
				      if ($with_anchor){
				         $form_view->withAnchor();
				      }
				      if (!mayEditRegular($current_user, $annotation_item)) {
				         $form_view->warnChanger();
				         $params = array();
				         $params['environment'] = $environment;
				         $params['with_modifying_actions'] = true;
				         $params['width'] = 500;
				         $errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
				         unset($params);
				         $errorbox->setText($translator->getMessage('COMMON_EDIT_AS_MODERATOR'));
				         $page->add($errorbox);
				      }
				      $form_view->setAction(curl($environment->getCurrentContextID(),'annotation','edit',''));
				      $form_view->setForm($form);
				      $form_view->setRubricConnections($rubric_connection);
				      $page->add($form_view);
				 */
			}
		}
		
		/*****************************************************************************/
		/******************************** END ACTIONS ********************************/
		/*****************************************************************************/
		
		protected function getDetailContent() {
			/*
			$disc_articles = $this->getDiscArticleContent();
			
			$return = array(
				'discussion'		=> $this->getDiscussionContent(),
				'disc_articles'		=> $disc_articles,
				'new_num'			=> count($disc_articles) + 1
			);
			
			return $return;
			*/
		}
		
		private function cleanup_session($current_iid) {
			$session = $this->_environment->getSessionItem();
			$session->unsetValue($this->_environment->getCurrentModule().'_add_files');
			$session->unsetValue($current_iid.'_post_vars');
			$session->unsetValue($current_iid.'_material_attach_ids');
			$session->unsetValue($current_iid.'_material_back_module');
			$session->unsetValue('annotation_history_context');
			$session->unsetValue('annotation_history_module');
			$session->unsetValue('annotation_history_function');
			$session->unsetValue('annotation_history_parameter');
		}
	}