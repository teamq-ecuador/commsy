define(
[
	"dojo/_base/declare",
	"commsy/widgets/PopupBase",
	"dijit/_TemplatedMixin",
	"dojo/text!./templates/LimeSurveyCreate.html",
	"dojo/i18n!./nls/LimeSurveyCreate",
	"dojo/_base/lang",
	"dojo/dom-construct",
	"dojo/dom-attr",
	"dojo/on",
	"dojo/topic",
	"dojo/dom-class",
	"dojo/query",
	"dojo/parser",
	"dojox/form/Manager",
	"dijit/registry",
	"commsy/Calendar",
	"dijit/form/ValidationTextBox",
	"dojox/validate/web"
], function
(
	declare,
	PopupBase,
	TemplatedMixin,
	Template,
	PopupTranslations,
	Lang,
	DomConstruct,
	DomAttr,
	On,
	Topic,
	DomClass,
	Query,
	Parser,
	Manager,
	Registry,
	Calendar
) {
	return declare([PopupBase, TemplatedMixin],
	{
		templateString:		Template,
		baseClass:			"LimeSurveyCreateWidget",
		
		canOverlay:			true,							///< Determs if popup can overlay other popus
		
		// attributes
		title:				"",
		_setTitleAttr:		{ node: "titleNode", type: "innerHTML" },
		
		constructor: function(options)
		{
			options = options || {};
			declare.safeMixin(this, options);
			
			this.popupTranslations = PopupTranslations;
		},
		
		/**
		 * \brief	Processing after the DOM fragment is created
		 * 
		 * Called after the DOM fragment has been created, but not necessarily
		 * added to the document.  Do not include any operations which rely on
		 * node dimensions or placement.
		 */
		postCreate: function()
		{
			// run parent postCreate processes
			this.inherited(arguments);
			
			/************************************************************************************
			 * Initialization is done here
			 ************************************************************************************/
			this.set("title", PopupTranslations.title);
			
			On(this.formNode, "submit", Lang.hitch(this, this.onSubmit));
		},
		
		/**
		 * \brief 	Processing after the DOM fragment is added to the document
		 * 
		 * Called after a widget and its children have been created and added to the page,
		 * and all related widgets have finished their create() cycle, up through postCreate().
		 * This is useful for composite widgets that need to control or layout sub-widgets.
		 * Many layout widgets can use this as a wiring phase.
		 */
		startup: function()
		{
			this.inherited(arguments);
			
			Parser.parse(this.widgetNode);
			
			this.AJAXRequest(	"limesurvey",
								"getTemplates",
								{},
								Lang.hitch(this, function(response)
			{
				// destroy the loading animation
				DomConstruct.destroy(this.loadingTemplatesNode);
				
				// if response is not empty, remove the default select option
				// and enable the submit button
				if ( response.surveys.length > 0 )
				{
					DomConstruct.empty(this.templateSelectNode);
					DomAttr.remove(this.submitNode, "disabled");
				}
				
				// go through all surveys and add them
				dojo.forEach(response.surveys, Lang.hitch(this, function(survey)
				{
					DomConstruct.create("option",
					{
						value:			survey.sid,
						innerHTML:		survey.surveyls_title
					}, this.templateSelectNode, "last");
				}));
				
				// make the select field visible
				DomClass.remove(this.templateSelectNode, "hidden");
			}));
			
			// create the dojo calendar
			var expiresCalendar = new Calendar();
			expiresCalendar.setup(this.expiresInputNode);
		},
		
		/************************************************************************************
		 * Getter / Setter
		 ************************************************************************************/
		
		/************************************************************************************
		 * Helper Functions
		 ************************************************************************************/
		
		/************************************************************************************
		 * Event Handling
		 ************************************************************************************/
		onSubmit: function(event)
		{
			event.preventDefault();
			
			var formManager = Registry.byId("limesurveyCreateForm");
			
			if ( formManager.validate() )
			{
				this.setupLoading();
				var formValues = formManager.gatherFormValues();
				
				this.AJAXRequest(	"limesurvey",
									"createSurvey",
									{
										templateId:		formValues.template,
										surveyTitle:	formValues.title,
										surveyExpires:	DomAttr.get(Query("input[name='expires']")[0], "value")
									},
									Lang.hitch(this, function(response)
				{
					// update the survey list in the main widget
					Topic.publish("updateSurveys", { });
					
					// remove loading indicator and close this popup
					this.destroyLoading();
					this.Close();
				}));
			}
		}
	});
});