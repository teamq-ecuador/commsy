define([	"dojo/_base/declare",
        	"dojo/dom-construct",
        	"dojo/io-query",
        	"commsy/tree",
        	"dojo/_base/lang",
        	"dijit/Dialog",
        	"cbtree/Tree",
        	"dijit/form/TextBox",
        	"dijit/form/Button",
        	"dojo/query",
        	"cbtree/models/ForestStoreModel",
        	"dojo/dom-attr",
        	"cbtree/CheckBox",
        	"dojo/on",
        	"dojo/topic",
        	"dijit/tree/dndSource",
        	"cbtree/models/StoreModel-API",
        	"dojo/NodeList-traverse"], function(declare, DomConstruct, ioQuery, TreeClass, Lang, Dialog, Tree, TextBox, Button, Query, ForestStoreModel, DomAttr, CheckBox, On, Topic, DndSource) {
	return declare(TreeClass, {
		textbox:	null,
		dialog:		null,
		button:		null,

		constructor: function(options) {
			// parent constructor is called automatically
		},

		/************************************************************************************
		 *** overwritten tree methods
		 ************************************************************************************/
		createTree: function() {
			// extend the DndSource object and disable the copy function
			dojo.extend(DndSource, {
				copyState: function(keyPressed, self)
				{
					return false;
				}
			});
			
			return new Tree({
				autoExpand:			this.expanded,
				model:				this.model,
				showRoot:			true,
				persist:			false,
				dndController:		DndSource,
				betweenThreshold:	5,
				checkBoxes:			this.checkboxes,
				onClick:			Lang.hitch(this, function(item, node, evt) {
					if ( this.popup && this.popup.onTagSelected && item.item_id && item.item_id[0] )
					{
						this.popup.onTagSelected(item.item_id[0]);
					}
				}),
				widget: {
					type:			CheckBox,
					args: {
						multiState:		true
					},
					mixin:		function(args) {
						args["value"]	= this.item.item_id[0];
						args["name"]	= "form_data[tags]";
					}
				}
			});
		},

		createModel: function() {
			return new ForestStoreModel({
				store:			this.store,
				checkedAttr:	"match",
				rootLabel:		"",

				// event handling
				onChildrenChange:	Lang.hitch(this, function(parent, newChildrenList) {
					this.onChildrenChange(parent, newChildrenList);
				})
			});
		},

		/************************************************************************************
		 *** main setup routine
		 ************************************************************************************/
		setupTree: function(node, callback) {
			callback = callback || function() {};

			// call parent method - overwrite arguments(add a callback function, when loading is done)
			this.inherited(arguments, [node, Lang.hitch(this, function() {
				// loading is done - now we can safely access this.tree

				// add "+" and "rename" to all node labels
				this.addCreateAndRenameToAllLabels();

				On(this.store, "Set", Lang.hitch(this, function(item, attribute, oldValue, newValue) {
					this.onStoreSet(item, attribute, oldValue, newValue);
				}));

				callback(this);
			}), true]);
		},

		/************************************************************************************
		 *** Event handler
		 ************************************************************************************/
		onChildrenChange: function(parent, newChildrenList) {
			// fetch changes to root node, that are not handled by onStoreSet(dunno why)
			if (parent.root) {
				var rootIds = dojo.map(newChildrenList, Lang.hitch(this, function(child, index, arr) {
					return this.model.getItemAttr(child, "item_id");
				}));

				// send ajax request
				this.AJAXRequest("tags", "updateTreeRoots", { rootIds: rootIds, roomId: this.room_id },
					Lang.hitch(this, function(response) {

					})
				);
			}
		},

		onStoreSet: function(item, attribute, oldValue, newValue) {
			// we are only interested in changes of child relationship
			if(attribute === "children") {
				// get tag id of item
				var parentId = this.model.getItemAttr(item, "item_id");

				// get array of children ids
				var childrenIds = dojo.map(newValue, Lang.hitch(this, function(child, index, arr) {
					return this.model.getItemAttr(child, "item_id");
				}));

				// send ajax request
				this.AJAXRequest("tags", "updateTreeStructure", { parentId: parentId, children: childrenIds, roomId: this.room_id },
					Lang.hitch(this, function(response) {

					})
				);
			}
		},
		
		onCreateEntrySuccessfull: function(newTag)
		{
		},
		
		onDeleteEntrySuccessfull: function(itemId)
		{
		},

		/************************************************************************************
		 *** Tree Actions
		 ************************************************************************************/
		createNewTreeEntry: function(parentId) {
			var model = this.tree.model;

			// get parent item
			var parentItem = model.fetchItem({ item_id: parentId });

			// create a new dialog with an input field for naming
			this.createNewInputDialog(Lang.hitch(this, function(tagName) {
				// create tag and update tree
				this.AJAXRequest("tags", "createNewTag", { tagName: tagName, parentId: parentId, roomId: this.room_id }, Lang.hitch(this, function(response) {
					var newTag = model.newItem( { title: tagName, item_id: response.tagId, children: [] }, parentItem);
					
					this.addCreateAndRenameToAllLabels();
					
					this.onCreateEntrySuccessfull(newTag);
				}));
			}));
		},

		renameTagEntry: function(itemId) {
			var model = this.tree.model;

			// get item
			var item = model.fetchItem({ item_id: itemId });

			// create a new dialog with an input field for renaming
			this.createNewInputDialog(Lang.hitch(this, function(newTagName) {
				// update tag and update tree
				this.AJAXRequest("tags", "renameTag", { newTagName: newTagName, tagId: itemId }, Lang.hitch(this, function(response) {
					model.setItemAttr(item, "title", newTagName);
				}));
			}), model.getItemAttr(item, "title"));
		},

		deleteTagEntry: function(itemId) {
			var model = this.tree.model;

			// get item
			var item = model.fetchItem({ item_id: itemId });

			this.createNewDeleteDialog(Lang.hitch(this, function() {
				// delete tag
				this.AJAXRequest("tags", "deleteTag", { tagId: itemId, roomId: this.room_id },
					Lang.hitch(this, function(response) {
						model.deleteItem(item);
						
						this.onDeleteEntrySuccessfull(itemId);
						
						// publish topic
						Topic.publish("updateTree", { widgetId: this.tree.get("id") });
					})
				);
			}));
		},

		/************************************************************************************
		 *** Helper Functions
		 ************************************************************************************/
		createNewInputDialog: function(submitCallback, value) {
			value = value || "";

			// create the text box
			this.textbox = new dijit.form.TextBox({
				value:		value
			});

			// create the button
			this.button = new dijit.form.Button({
				label:		"Ok",
				onClick:	Lang.hitch(this, function(event) {
					// return the textbox value through callback
					submitCallback(this.textbox.get("value"));

					// destroy the dialog
					this.dialog.destroyRecursive();
				})
			});

			// create and show the dialog
			this.dialog = new dijit.Dialog({
				title:		"Name"
			});
			dojo.place(this.textbox.domNode, this.dialog.containerNode, "last");
			dojo.place(this.button.domNode, this.dialog.containerNode, "last");

			this.dialog.show();
		},

		createNewDeleteDialog: function(submitCallback) {
			// create the button
			// TODO: translate
			this.button = new dijit.form.Button({
				label:		"Löschen",
				onClick:	Lang.hitch(this, function(event) {
					// return the textbox value through callback
					submitCallback();

					// destroy the dialog
					this.dialog.destroyRecursive();
				})
			});

			// create and show the dialog
			// TODO: translate
			this.dialog = new dijit.Dialog({
				title:		"Löschen"
			});
			dojo.place(this.button.domNode, this.dialog.containerNode, "last");

			this.dialog.show();
		},

		addCreateAndRenameToAllLabels: function() {
			// create a link after all labels and connect event handling
			dojo.forEach(Query("div#popup_tabcontent span.dijitTreeLabel, div.portfolioEditWidget span.dijitTreeLabel"), Lang.hitch(this, function(spanNode, index, arr) {

				// check if link wasn't already created
				var nodeCreatorNode = Query("a.nodeCreator", spanNode.parentNode)[0];
				if(!nodeCreatorNode) {

					var createLinkNode = DomConstruct.create("a", {
						className:		"nodeCreator",
						href:			"#",
						innerHTML:		"| Erstellen"
					}, spanNode, "after");
					
					var renameLinkNode = null;
					var deleteLinkNode = null;

					// check if not root node
					if (DomAttr.get(spanNode, "innerHTML") !== "ROOT" && DomAttr.get(spanNode, "innerHTML") !== "") {
						renameLinkNode = DomConstruct.create("a", {
							className:		"nodeCreator",
							href:			"#",
							innerHTML:		" | Bearbeiten"
						}, createLinkNode, "after");

						deleteLinkNode = DomConstruct.create("a", {
							className:		"nodeCreator",
							href:			"#",
							innerHTML:		" | Löschen"
						}, renameLinkNode, "after");
					}

					// get widget id from appropriated dijitTreeNode
					var treeNode = new dojo.NodeList(createLinkNode).parents("div.dijitTreeNode")[0];
					if(treeNode) {
						var widgetId = DomAttr.get(treeNode, "widgetid");

						// extract item id
						var widget = dijit.byId(widgetId);
						var itemId = parseInt(this.tree.model.getItemAttr(widget.item, "item_id"));

						On(createLinkNode, "click", Lang.hitch(this, function(event) {
							this.createNewTreeEntry(itemId);
						}));

						// check if not root node
						if (DomAttr.get(spanNode, "innerHTML") !== "ROOT" && DomAttr.get(spanNode, "innerHTML") !== "") {
							On(renameLinkNode, "click", Lang.hitch(this, function(event) {
								this.renameTagEntry(itemId);
							}));

							On(deleteLinkNode, "click", Lang.hitch(this, function(event) {
								this.deleteTagEntry(itemId);
							}));
						}
					}
				}
			}));
		}
	});
});