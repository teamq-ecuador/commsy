//>>built
define("commsy/bar/Calendar","dojo/_base/declare,dijit/_WidgetBase,commsy/base,dijit/_TemplatedMixin,dojo/_base/lang,dojo/dom-construct,dojo/dom-attr,dojo/query,dojo/on,dojo/store/Observable,commsy/store/Json,dojo/topic,dojox/calendar/Calendar,dojo/date/stamp".split(","),function(b,e,f,g,c,k,l,m,n,o,h,i,j,d){return b([f,e,g],{baseClass:"CommSyWidget",widgetHandler:null,itemId:null,constructor:function(a){a=a||{};b.safeMixin(this,a)},postCreate:function(){this.inherited(arguments);this.itemId=this.from_php.ownRoom.id;
var a=this.createCalendar();i.subscribe("updatePrivateCalendar",c.hitch(this,function(){a.set("store",a.store)}))},afterParse:function(){},createCalendar:function(){var a=new j({decodeDate:function(a){return d.fromISOString(a)},encodeDate:function(a){return d.toISOString(a)},store:new h({fct:"myCalendar"}),selectionMode:"none",moveEnabled:!1,dateInterval:"day",style:"position: relative; height: 500px;",columnViewProps:{minHours:0,maxHours:24}},this.calendarNode);a.on("itemClick",c.hitch(function(){}));
return a}})});