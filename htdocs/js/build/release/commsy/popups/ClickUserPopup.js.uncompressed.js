define("commsy/popups/ClickUserPopup", [	"dojo/_base/declare",
        	"commsy/ClickPopupHandler",
        	"dojo/query",
        	"dojo/dom-class",
        	"dojo/_base/lang",
        	"dojo/dom-construct",
        	"dojo/dom-attr",
        	"dojo/on"], function(declare, ClickPopupHandler, query, dom_class, lang, domConstruct, domAttr, On) {
	return declare(ClickPopupHandler, {
		sendImages: [],
		
		constructor: function() {
			this.sendImages = [];
		},
		
		init: function(triggerNode, customObject) {
			this.triggerNode = triggerNode;
			this.item_id = customObject.iid;
			this.module = "user";
			
			this.features = [ "upload-single", "editor", "netnavigation", "calendar" ];
			
			// register click for node
			this.registerPopupClick();
		},
		
		setupSpecific: function() {
			dojo.ready(lang.hitch(this, function() {
				// setup callback for single upload
				this.featureHandles["upload-single"][0].setCallback(lang.hitch(this, function(fileInfo) {
					// setup preview
					var formNode = this.featureHandles["upload-single"][0].uploader.form;
					var previewNode = query("div.filePreview", formNode)[0];
					
					domConstruct.empty(previewNode);
					
					domConstruct.create("img", {
						src:		"commsy.php?cid=" + this.uri_object.cid + "&mod=picture&fct=getTemp&fileName=" + fileInfo.file
					}, previewNode, "last");
					
					this.sendImages.push({ part: "upload_picture", fileInfo: fileInfo });
				}));
			}));
		},
		
		onPopupSubmit: function(customObject) {
			// add ckeditor data to hidden div
			dojo.forEach(this.featureHandles["editor"], function(editor, index, arr) {
				var instance = editor.getInstance();
				var node = editor.getNode().parentNode;
				
				domAttr.set(query("input[type='hidden']", node)[0], 'value', editor.getInstance().getData());
			});
			
			// setup data to send via ajax
			var search = {
				tabs: [
					{ id: "account_tab" }
				],
				nodeLists: [
				    { query: query("div#popup_content", this.contentNode) }
				]
			};
			
			this.submit(search);
		},
		
		onPopupSubmitSuccess: function(item_id) {
			if (this.sendImages.length > 0) {
				// send ajax request
				var data = {
					module:			"user",
					additional: {
						action:		this.sendImages[0].part,
					    fileInfo:	this.sendImages[0].fileInfo,
					    iid:		item_id
					}
				};
			}
			
			// invoke netnavigation - process after item creation actions
			if(this.item_id === "NEW") {
				this.featureHandles["netnavigation"][0].afterItemCreation(item_id, lang.hitch(this, function() {
					if (this.sendImages.length > 0) {
						this.AJAXRequest("popup", "save", data, lang.hitch(this, function(response) {
							this.reload(item_id);
						}));
					} else {
						this.reload(item_id);
					}
				}));
			} else {
				if (this.sendImages.length > 0) {
					this.AJAXRequest("popup", "save", data, lang.hitch(this, function(response) {
						this.reload(item_id);
					}));
				} else {
					this.reload(item_id);
				}
			}
		}
	});
});