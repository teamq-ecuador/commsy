//>>built
define("commsy/widgets/PortfolioItem","dojo/_base/declare,dijit/_WidgetBase,commsy/base,dijit/_TemplatedMixin,dojo/_base/lang,dojo/_base/array,dojo/dom-construct,dojo/dom-attr,dojo/dom-style,dojo/_base/xhr,dojo/query,dojo/on,dojo/topic,dijit/layout/ContentPane,dojo/NodeList-traverse".split(","),function(j,n,o,p,d,r,b,s,h,t,m,u,q){return j([o,n,p],{baseClass:"CommSyPortfolioItemWidget",widgetHandler:null,constructor:function(a){a=a||{};j.safeMixin(this,a);this.isInitialized=!1;this.description=""},
postCreate:function(){this.inherited(arguments)},init:function(a){if(!1===this.isInitialized)this.withEditing=a,this.update(),require(["commsy/popups/ClickPortfolioPopup"],d.hitch(this,function(a){(new a).init(this.editPortfolioNode,{iid:this.portfolioId,module:"portfolioItem"})})),a=m("a.tagEdit",this.portfolioNode),dojo.forEach(a,d.hitch(this,function(a){require(["commsy/popups/ClickTagPortfolioPopup"],d.hitch(this,function(b){var b=new b,d=this.getAttrAsObject(a,"data-custom");d.portfolioId=this.portfolioId;
d&&b.init(a,d)}))})),q.subscribe("updatePortfolio",d.hitch(this,function(a){a.portfolioId==this.portfolioId&&this.update()})),this.isInitialized=!0},update:function(){this.AJAXRequest("portfolio","getPortfolio",{portfolioId:this.portfolioId},d.hitch(this,function(a){this.response=a;this.descriptionNode.innerHTML=a.description;if(!1===this.withEditing)this.creatorNode.innerHTML=a.creator;var f=dojo.filter(a.tags,function(a){return 0<a.row}),a=dojo.filter(a.tags,function(a){return 0<a.column});!1===
this.withEditing&&(h.set(this.lastVerticalTag,"display","none"),h.set(this.portfolioEditDivNode,"display","none"),h.set(this.portfolioEditColumnNode,"display","none"));var c=m(this.lastVerticalTag).prevAll("div.ep_vert_col_cell");dojo.forEach(c,d.hitch(this,function(a){b.destroy(a)}));dojo.forEach(f,d.hitch(this,function(a){var c=b.create("div",{className:"ep_vert_col_cell"},this.lastVerticalTag,"before"),e=b.create("div",{className:"ep_vert_col_title"},c,"last");if(!0===this.withEditing){var i=b.create("a",
{href:"#","data-custom":"tagId: '"+a.t_id+"', position: 'row', module: 'tagPortfolio'"},e,"last");b.create("img",{src:this.from_php.template.tpl_path+"img/ep_icon_editdarkgrey.gif"},i,"last")}b.create("strong",{innerHTML:a.title},e,"last");b.create("div",{className:"clear"},c,"last");!0===this.withEditing&&require(["commsy/popups/ClickTagPortfolioPopup"],d.hitch(this,function(a){var a=new a,b=this.getAttrAsObject(i,"data-custom");b.portfolioId=this.portfolioId;b&&a.init(i,b)}))}));b.empty(this.tableNode);
var g=b.create("tr",{},this.tableNode,"last");dojo.forEach(a,d.hitch(this,function(a){var c=b.create("th",{},g,"last");if(!0===this.withEditing){var e=b.create("a",{className:"ep_edit_head",href:"#","data-custom":"tagId: '"+a.t_id+"', position: 'row', module: 'tagPortfolio'"},c,"last");b.create("img",{src:this.from_php.template.tpl_path+"img/ep_icon_editdarkgrey.gif"},e,"last")}b.create("strong",{innerHTML:a.title.substring(0,14)},c,"last");!0===this.withEditing&&require(["commsy/popups/ClickTagPortfolioPopup"],
d.hitch(this,function(a){var a=new a,b=this.getAttrAsObject(e,"data-custom");b.portfolioId=this.portfolioId;b&&a.init(e,b)}))}));f=f.length*a.length;g=null;for(c=0;c<f;c++)0===c%a.length&&(g=b.create("tr",{},this.tableNode,"last")),this.insertHTMLForTableCell(b.create("td",{},g,"last"),c%a.length+1,parseInt(c/a.length)+1)}))},insertHTMLForTableCell:function(a,f,c){var g=dojo.filter(this.response.tags,d.hitch(this,function(a){return a.column==f&&0==a.row||a.row==c&&0==a.column})),k=[];dojo.forEach(g,
d.hitch(this,function(a){k.push(a.t_id)}));var l=b.create("div",{className:"ep_cell_content"},a,"last"),e=0,g=0;this.response.numAnnotations[c]&&this.response.numAnnotations[c][f]&&(g=this.response.numAnnotations[c][f]);if(2==k.length){var i=b.create("a",{},l,"last"),l=k[0],h=k[1],j=[];this.response.links[l]&&this.response.links[h]&&dojo.forEach(this.response.links[l],d.hitch(this,function(a){var c=a.itemId;dojo.some(this.response.links[h],d.hitch(this,function(a){return a.itemId==c}))&&(3>e&&b.create("span",
{innerHTML:a.title.substring(0,20)},i,"last"),j.push(a.itemId),e++)}));require(["commsy/popups/ClickPortfolioListPopup"],d.hitch(this,function(a){var a=new a,b={};b.portfolioId=this.portfolioId;b.row=c;b.column=f;b.itemIds=j;b&&a.init(i,b)}))}a=b.create("div",{className:"ep_cell_actions"},a,"last");0<e&&(b.create("p",{className:"ep_item_count",innerHTML:e},a,"last"),b.create("p",{className:"ep_item_comment",innerHTML:g},a,"last"));b.create("div",{className:"clear"},a,"last")},startup:function(){this.inherited(arguments)}})});