//>>built
define("commsy/DivToggle","dojo/_base/declare,commsy/base,dojo/query,dojo/dom-attr,dojo/dom-class,dojo/dom-style,dojo/_base/lang,dojo/fx,dojo/on".split(","),function(g,j,e,c,f,k,h,i,l){return g(j,{constructor:function(a){a=a||{};g.safeMixin(this,a)},setup:function(){var a=e("a.divToggle");dojo.forEach(a,h.hitch(this,function(b){if(b){var a=this.getAttrAsObject(b,"data-custom"),c=e("div#"+a.toggleId)[0];c&&l(b,"click",h.hitch(this,function(){this.onClick(b,c)}))}}))},onClick:function(a,b){if(f.contains(b,
"hidden")){c.set(a,"title",this.from_php.translations.common_hide);var d=e("img",a)[0];d&&(c.set(d,"src",this.from_php.template.tpl_path+"img/btn_close_rc.gif"),c.set(d,"alt",this.from_php.translations.common_hide));f.remove(b,"hidden");k.set(b,"height","0px");i.wipeIn({node:b}).play()}else{c.set(a,"title",this.from_php.translations.common_show);if(d=e("img",a)[0])c.set(d,"src",this.from_php.template.tpl_path+"img/btn_open_rc.gif"),c.set(d,"alt",this.from_php.translations.common_show);i.wipeOut({node:b,
onEnd:function(){f.add(b,"hidden")}}).play()}}})});