require([	"dojo/_base/declare",
         	"commsy/base",
         	"dojo/_base/lang"], function(declare, BaseClass, Lang) {
	var Controller = declare(BaseClass, {
		constructor: function(args) {
			
		},
		
		init: function() {
			require([	"dojo/query",
			         	"dojo/dom-attr",
			         	"dojo/on",
			         	"dojo/NodeList-traverse",
			         	"dojo/domReady!"], Lang.hitch(this, function(query, domAttr, On, ready) {
			    
			    var uri_object = this.uri_object;
				
				this.initCommsyBar();
				
				// register event for handling mouse actions outside content div
				On(document.body, "click", Lang.hitch(this, function(event) {
					if(domAttr.get(event.target, "id") === "popup_wrapper") {
						// TODO: create something like a tooltip here
						alert("Bitte schließen Sie zuerst das Popup-Fenster, bevor Sie sonstige Seitenoperationen ausführen");
					}
				}));
				
				/* temporary inline hotfix */
				
				if (this.uri_object.mod == "group") {
					var joinNode = query("a#group_detail_group_enter")[0];
					
					if (joinNode) {
						var customObject = this.getAttrAsObject(joinNode, "data-custom");
						
						var qry = dojo.objectToQuery(this.replaceOrSetURIParam("group_option", "1"));
						
						if (customObject.needsCode) {
							
							require(["dojo/on","dijit/form/TextBox","dijit/form/Button","dijit/Dialog"], function(On, TextBox, Button, Dialog) {
								
								On(joinNode, "click", Lang.hitch(this, function(event) {
									var input = new dijit.form.TextBox({
										
									});
									
									var button = new dijit.form.Button({
										label:	"betreten",
										onClick:	Lang.hitch(this, function(event) {
											location.href = "commsy.php?" + qry + "&code=" + input.value;
											
											dialog.destroyRecursive();
										})
									});
									var dialog = new dijit.Dialog({
										title:		"Teilnahme-Code"
									});
									dojo.place(input.domNode, dialog.containerNode, "last");
									dojo.place(button.domNode, dialog.containerNode, "last");
									
									
									dialog.show();
									
									event.preventDefault();
									return false;
								}));
								
								
							});
						}
					}
				}
				
				/* ~temporary inline hotfix */
				
				// widget popups
				var aStackNode = query("a#tm_stack")[0];
				if (aStackNode) {
					require(["commsy/popups/ToggleStack"], function(StackPopup) {
						var handler = new StackPopup(aStackNode, query("div#tm_menus div#tm_dropmenu_stack")[0]);
					});
				}
				
				var aWidgetsNode = query("a#tm_widgets")[0];
				if (aWidgetsNode) {
					require(["commsy/popups/ToggleWidgets"], function(WidgetsPopup) {
						var handler = new WidgetsPopup(aWidgetsNode, query("div#tm_menus div#tm_dropmenu_widget_bar")[0]);
					});
				}
				
				/*
				var aPortfolioNode = query("a#tm_portfolio")[0];
				if (aPortfolioNode) {
					require(["commsy/popups/TogglePortfolio"], function(PortfolioPopup) {
						var handler = new PortfolioPopup(aPortfolioNode, query("div#tm_menus div#tm_dropmenu_portfolio")[0]);
					});
				}
				
				/*
				
				require(["commsy/popups/ToggleMyCalendar"], function(MyCalendarPopup) {
					var handler = newMyCalendarPopup(query("a#tm_mycalendar")[0], query("div#tm_menus div#tm_dropmenu_mycalendar")[0]);
				});
				*/
				
				// setup rubric forms
				query(".open_popup").forEach(Lang.hitch(this, function(node, index, arr) {
					// get custom data object
					var customObject = this.getAttrAsObject(node, "data-custom");
					
					var module = customObject.module;
					
					require(["commsy/popups/Click" + this.ucFirst(module) + "Popup"], function(ClickPopup) {
						var handler = new ClickPopup();
						handler.init(node, customObject);
					});
				}));
				
				// buzzwords and tags expander
				if (this.uri_object.fct === "index") {
					require(["commsy/DivToggle"], function(DivToggle) {
						var handler = new DivToggle();
						handler.setup();
					});
				}
				
				// ajax actions
				require(["commsy/AjaxActions"], function(AjaxActions) {
					var aNodes = query("a.ajax_action");
					
					if (aNodes) {
						var handler = new AjaxActions();
						handler.setup(aNodes);
					}
				});
				
				// ckeditor
				query("div.ckeditor").forEach(function(node, index, arr) {
					require(["commsy/ckeditor"], function(CKEditor) {
						var handler = new CKEditor();
						handler.create(node);
					});
				});
				
				// tree
				query("div.tree").forEach(function(node, index, arr) {
					require(["commsy/tree"], function(Tree) {
						var handler = new Tree();
						handler.setupTree(node, function() {
							// highlight path
							if (uri_object.seltag) {
								var seltag = uri_object.seltag;
								var path = handler.buildPath(seltag);
								handler.tree.set("paths", [path]);
							}
						}, true);
					});
				});
				
				// threaded discussion tree
				if (this.uri_object.mod == "discussion" && this.uri_object.fct == "detail") {
					var treeNode = query("div#discussion_tree")[0];
					
					if (treeNode) {
						require(["commsy/DiscussionTree"], function(DiscussionTree) {
							var handler = new DiscussionTree();
							handler.setupTree(treeNode);
						});
					}
				}
				
				// calendar scroll bar position and select auto submit - process directly here
				if (this.uri_object.fct == "index" && this.uri_object.mod == "date") {
					require(["commsy/DateCalendar"], function(DateCalendar) {
						var handler = new DateCalendar();
						handler.setup();
					});
				}
				
				// search
				if(this.from_php.dev.indexed_search === true) {
					var inputNode = query("input#search_input")[0];
					
					if (inputNode) {
						require(["commsy/Search"], function(Search) {
							var handler = new Search();
							handler.setup(inputNode);
						});
					}
				}
				
				// overlays
				query("a.new_item_2, a.new_item, a.attachment, span#detail_assessment, div.cal_days_events a, div.cal_days_week_events a").forEach(function(node, index, arr) {
					require(["commsy/Overlay"], function(Overlay) {
						var handler = Overlay();
						handler.setup(node);
					});
				});
				
				// div expander
				if(this.uri_object.mod === "home") {
					var objects = [];
					query("div.content_item div[class^='list_wrap']").forEach(function(node, index, arr) {					
						objects.push({ div: node, actor:	query("a.open_close", node.parentNode)[0] });
					});
					
					require(["commsy/DivExpander"], function(DivExpander) {
						var handler = DivExpander();
						handler.setup(objects);
					});
				}
				
				// lightbox
				require(["commsy/Lightbox"], function(Lightbox) {
					var handler = new Lightbox();
					handler.setup(query("a[class^='lightbox']"));
				});
				
				// progressbar
				query("div.progressbar").forEach(function(node, index, arr) {
					require(["commsy/ProgressBar"], function(ProgressBar) {
						var handler = ProgressBar();
						handler.setup(node);
					});
				});
				
				// on detail context
				if(this.uri_object.fct === "detail") {
					// action expander
					var actors = query(	"div.item_actions a.edit," +
										"div.item_actions a.detail," +
										"div.item_actions a.workflow," +
										"div.item_actions a.linked," + 
										"div.item_actions a.annotations," +
										"div.item_actions a.versions");
						
					require(["commsy/ActionExpander"], function(ActionExpander) {
						var handler = new ActionExpander();
						handler.setup(actors);
					});
					
					var printNode = query("a#printbutton")[0];
					if ( printNode )
					{
						On(printNode, "click", function(event) {
							require(["commsy/PrintDivToggle"], function(PrintDivToggle) {
								var printToggle = new PrintDivToggle();
								printToggle.setup();
							});							
						});
					}
				}
				
				// on list context
				if(this.uri_object.fct === "index") {
					// list selection
					var inputNodes = query("input[type='checkbox'][name^='form_data[attach]']");
					var counterNode = query("div.ii_right span#selected_items")[0];
					
					require(["commsy/ListSelection"], function(ListSelection) {
						var handler = new ListSelection();
						handler.setup(inputNodes, counterNode);
					});
				}
				
				// uploader
				query("div.uploader").forEach(function(node, index, arr) {
					require(["commsy/Uploader"], function(Uploader) {
						var handler = new Uploader();
						handler.setup(node);
					});
				});
				
				// follow anchors
				if(window.location.href.indexOf("#") !== -1) {
					require(["commsy/AnchorFollower"], function(AnchorFollower) {
						var handler = new AnchorFollower();
						
						var anchor = window.location.href.substring(window.location.href.indexOf("#") + 1);
						handler.follow(anchor);
					});
				}
				
				// assessment
				require(["commsy/Assessment"], function(Assessment) {
					var handler = new Assessment();
					handler.setup(query("span#detail_assessment")[0]);
				});
				
				// colorpicker
				query("div.colorpicker").forEach(function(node, index, arr) {
					require(["commsy/Colorpicker"], function(Colorpicker) {
						var handler = new Colorpicker();
						handler.setup(node);
					});
				});
				
				// automatic popup opener
				// should be loaded at the very last
				require(["commsy/popups/TogglePersonalConfiguration", "commsy/AutoOpenPopup"], function(Configuration, AutoOpenPopup) {
					var handler = new AutoOpenPopup();
					handler.setup();
				});
			}));
		},
		
		initCommsyBar: function() {
			require([	"dojo/query",
			         	"dojo/on",
			         	"dojo/NodeList-traverse",
			         	"dojo/domReady!"], Lang.hitch(this, function(Query, On, ready) {
			    
	         	/*
	         	 * initiate popup handler
	         	 * new method: first click is handled here and not by module, so we only need to load it when requested
	         	 */
			    var aConfigurationNode = Query("a#tm_settings")[0];
			    if (aConfigurationNode) {
			    	On.once(aConfigurationNode, "click", Lang.hitch(this, function(event) {
			    		require(["commsy/popups/ToggleRoomConfiguration"], function(RoomConfigurationPopup) {
			    			var handler = new RoomConfigurationPopup(aConfigurationNode, Query("div#tm_menus div#tm_dropmenu_configuration")[0]);
			    			handler.open();
		    			});
			    	}));
			    }
			    
			    var aPersonalNode = Query("a#tm_user")[0];
			    if (aPersonalNode) {
			    	On.once(aPersonalNode, "click", Lang.hitch(this, function(event) {
			    		require(["commsy/popups/TogglePersonalConfiguration"], function(PersonalConfigurationPopup) {
		    				var handler = new PersonalConfigurationPopup(aPersonalNode, Query("div#tm_menus div#tm_dropmenu_pers_bar")[0]);
		    				handler.open();
		    			});
			    	}));
			    }
			    
			    var aBreadcrumbNode = Query("a#tm_bread_crumb")[0];
			    if (aBreadcrumbNode) {
			    	On.once(aBreadcrumbNode, "click", Lang.hitch(this, function(event) {
			    		require(["commsy/popups/ToggleBreadcrumb"], function(BreadcrumbPopup) {
	    					var handler = new BreadcrumbPopup(aBreadcrumbNode, Query("div#tm_menus div#tm_dropmenu_breadcrumb")[0]);
	    					handler.open();
	    				});
			    	}));
    			}
			    
			    var aClipboardNode = Query("a#tm_clipboard")[0];
			    if (aClipboardNode) {
			    	On.once(aClipboardNode, "click", Lang.hitch(this, function(event) {
			    		require(["commsy/popups/ToggleClipboard"], function(ClipboardPopup) {
	    					var handler = new ClipboardPopup(aClipboardNode, Query("div#tm_menus div#tm_dropmenu_clipboard")[0]);
	    					handler.open();
	    				});
			    	}));
    			}
			    
    			var aCalendarNode = Query("a#tm_mycalendar")[0];
    			if (aCalendarNode) {
    				On.once(aCalendarNode, "click", Lang.hitch(this, function(event) {
    					var widgetManager = this.getWidgetManager();
    					
    					widgetManager.GetInstance("commsy/widgets/Calendar/CalendarWidget", {}).then(function(deferred)
						{
							var widgetInstance = deferred.instance;
							
							// register click event
							widgetManager.RegisterOpenCloseClick(widgetInstance, aCalendarNode);
							
							// open widget
							widgetInstance.Open();
						});
    				}));
    			}
    			
    			var aPortfolioNode = Query("a#tm_portfolio")[0];
				if (aPortfolioNode)
				{
					On.once(aPortfolioNode, "click", Lang.hitch(this, function(event)
					{
						var widgetManager = this.getWidgetManager();
						
						widgetManager.GetInstance("commsy/widgets/Portfolio/PortfolioWidget", {}).then(function(deferred)
						{
							var widgetInstance = deferred.instance;
							
							// register click event
							widgetManager.RegisterOpenCloseClick(widgetInstance, aPortfolioNode);
							
							// open widget
							widgetInstance.Open();
						});
					}));
				}
			}));
		}
	});
	
	var ctrl = new Controller;
	ctrl.init();
});