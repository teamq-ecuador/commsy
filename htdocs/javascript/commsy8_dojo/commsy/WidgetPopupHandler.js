define([	"dojo/_base/declare",
        	"commsy/TogglePopupHandler",
        	"dojo/query",
        	"dojo/dom-class",
        	"dojo/dom-attr",
        	"dojo/dom-construct",
        	"dojo/on",
        	"dojo/_base/lang"], function(declare, TogglePopupHandler, Query, DomClass, DomAttr, DomConstruct, On, Lang) {
	return declare(TogglePopupHandler, {
		widgetArray:	[],
		
		constructor: function(button_node, content_node) {
			this.popup_button_node = button_node;
			this.contentNode = content_node;
			
			// register click for node
			this.registerPopupClick();
		},
		
		setupSpecific: function() {
			// get configuration for this popup
			var action = "get" + this.ucFirst(this.module) + "Configuration";
			this.AJAXRequest("widgets", action, {},
				Lang.hitch(this, function(response) {
					// we recieved a list of widgets to display
					this.widgetArray = response.displayConfig;
					
					// load widgets
					this.loadWidgets();
				})
			);
		},
		
		loadWidgets: function() {
			dojo.forEach(this.widgetArray, Lang.hitch(this, function(widget, index, arr) {
				// determ name of widget
				var split = widget.split("_");
				var widgetPath = "widgets/" + this.ucFirst(this.module) + this.ucFirst(split[1]);
				
				if(split[3] && split[3] == "preferences") widgetPath += "Preferences";
				
				require([widgetPath], Lang.hitch(this, function(widgetObject) {
					// get template
					this.AJAXRequest("widgets", "getHTMLForWidget", { widgetPath: widgetPath },
						Lang.hitch(this, function(templateString) {
							// init widget
							var widgetHandler = new widgetObject({
								templateString:		templateString
							});
							
							// place widget
							widgetHandler.placeAt(Query("div.widgetArea", this.contentNode)[0]);
						})
					)
				}));
				
				
				/*
				// check if widget exists
				if (Lang.exists("widgets." + widgetName)) {
					
					console.log(widgetName + " found");
				}
				*/
			}));
		},
		
		onPopupSubmit: function(customObject) {
			// this popup will not be submitted
		},
		
		onPopupSubmitSuccess: function(item_id) {
		}
	});
});