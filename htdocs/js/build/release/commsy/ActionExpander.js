//>>built
define("commsy/ActionExpander","dojo/_base/declare,dojo/on,dojo/_base/lang,commsy/base,dojo/fx,dojo/dom-class,dojo/dom-style,dojo/dom-attr,dojo/query".split(","),function(h,l,i,m,j,e,n,f,k){return h(m,{hidden:[],constructor:function(a){a=a||{};h.safeMixin(this,a)},setup:function(a){dojo.forEach(a,i.hitch(this,function(a,c){var d=this.getAttrAsObject(a,"data-custom").expand,b=k("div#"+d)[0];b?(this.hidden[c]=e.contains(b,"hidden"),l(a,"click",i.hitch(this,function(d){this.onClick(a,c,b);d.preventDefault()}))):
console.error("content for action missing")}))},onClick:function(a,g,c){var d=k("span:first",a)[0],b=f.get(d,"class");this.hidden[g]?(e.remove(c,"hidden"),n.set(c,"height","0px"),j.wipeIn({node:c}).play(),e.add(a,"item_actions_glow"),"_ok"!==b.substr(-3,3)&&f.set(d,"class",b+"_ok"),this.scrollToNodeAnimated(c)):(j.wipeOut({node:c}).play(),e.remove(a,"item_actions_glow"),"_ok"===b.substr(-3,3)&&f.set(d,"class",b.substr(0,b.length-3)));this.hidden[g]=!this.hidden[g]}})});