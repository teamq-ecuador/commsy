<?php
	require_once('classes/controller/cs_ajax_controller.php');
	
	class cs_ajax_tagtree_controller extends cs_ajax_controller {
		/**
		 * constructor
		 */
		public function __construct(cs_environment $environment) {
			// call parent
			parent::__construct($environment);
		}
		
		public function actionGetTreeData() {
			$utils = $this->getUtils();
			if($utils->showTags()) {
				$item_id = $this->_data['item_id'];
				$room_id = $this->_data["room_id"];
					
				if($item_id !== null && $item_id !== 'NEW') {
					// get item
					$item_manager = $this->_environment->getItemManager();
					$type = $item_manager->getItemType($item_id);
					if($type === CS_LABEL_TYPE) {
						$label_manager = $this->_environment->getLabelManager();
						$label_item = $label_manager->getItem($item_id);
						$type = $label_item->getItemType();
					}
					$manager = $this->_environment->getManager($type);
					$item = $manager->getItem($item_id);
					
					$item_tag_list = $item->getTagList();
					$item_tag_id_array = $item_tag_list->getIDArray();
					
					if ($room_id !== null) {
						$tags = $utils->getTags($room_id);
					} else {
						$tags = $utils->getTags();
					}
					
					$utils->markTags($tags, $item_tag_id_array);
				} else {
					if ($room_id !== null) {
						$tags = $utils->getTags($room_id);
					} else {
						$tags = $utils->getTags();
					}
				}
				
				$this->setSuccessfullDataReturn($tags);
			} else {
				$this->setErrorReturn("103", "tags are not enabled", array());
			}
			
			echo $this->_return;
		}

		/*
		 * every derived class needs to implement an processTemplate function
		 */
		public function process() {
			// call parent
			parent::process();
		}
	}
?>