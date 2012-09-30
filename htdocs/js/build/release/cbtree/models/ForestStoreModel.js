//>>built
define("cbtree/models/ForestStoreModel",["dojo/_base/array","dojo/_base/declare","dojo/_base/lang","dojo/_base/window","./TreeStoreModel"],function(c,f,e,g,h){return f([h],{rootLabel:"ROOT",rootId:"$root$",moduleName:"cbTree/models/ForestStoreModel",constructor:function(a){this.root={store:this,root:!0,id:this.rootId,label:this.rootLabel,children:a.rootChildren};this.root[this.checkedAttr]=this.checkedState;this.hasFakeRoot=!0},getChildren:function(a,b,d,i){a===this.root?this.root.children?b(this.root.children):
this.store.fetch(this._mixinFetch({query:this.query,onComplete:e.hitch(this,function(a){this.root.children=a;b(a)}),onError:d})):this.inherited(arguments)},getParents:function(a){var b=[];if(a)return a!==this.root&&(b=this.store.getParents(a),!b.length)?[this.root]:b},mayHaveChildren:function(a){return a===this.root||this.inherited(arguments)},fetchItemByIdentity:function(a){if(a.identity==this.root.id){var b=a.scope?a.scope:g.global;a.onItem&&a.onItem.call(b,this.root)}else this.inherited(arguments)},
getIcon:function(a){if(this.iconAttr)return a!==this.root?this.store.getValue(a,this.iconAttr):this.root[this.iconAttr]},getIdentity:function(a){return a===this.root?this.root.id:this.inherited(arguments)},getLabel:function(a){return a===this.root?this.root.label:this.inherited(arguments)},isItem:function(a){return a===this.root?!0:this.inherited(arguments)},isChildOf:function(a,b){if(a===this.root){if(-1!==c.indexOf(this.root.children,b))return!0}else return this.inherited(arguments)},deleteItem:function(a){if(a===
this.root){var a=this.root.children||[],b;for(b=0;b<a.length;b++)this.store.deleteItem(a[b])}else return this.store.deleteItem(a)},newItem:function(a,b,d,i){if(b===this.root){var c=this.store.newItem(a);this._updateCheckedParent(c);return c}return this.inherited(arguments)},pasteItem:function(a,b,d,c,e,f){b===this.root&&(c||this.store.detachFromRoot(a));d===this.root&&this.store.attachToRoot(a);this.inherited(arguments,[a,b===this.root?null:b,d===this.root?null:d,c,e,f])},onDeleteItem:function(a){-1!=
c.indexOf(this.root.children,a)&&this._requeryTop();this.inherited(arguments)},onNewItem:function(a,b){b?(this.getChildren(b.item,e.hitch(this,function(a){this.onChildrenChange(b.item,a)})),this._updateCheckedParent(a,!0)):this._requeryTop()},onSetItem:function(a,b,d,e){this._queryAttrs.length&&-1!=c.indexOf(this._queryAttrs,b)&&this._requeryTop();this.inherited(arguments)},onRootChange:function(a,b){("attach"===b||"detach"===b)&&this._requeryTop()},_requeryTop:function(){var a=this.root.children||
[];this.store.fetch(this._mixinFetch({query:this.query,onComplete:e.hitch(this,function(b){this.root.children=b;if(a.length!=b.length||c.some(a,function(a,c){return b[c]!=a}))this.onChildrenChange(this.root,b),this._updateCheckedParent(b[0])})}))}})});