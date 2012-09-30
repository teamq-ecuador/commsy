//>>built
define("cbtree/stores/FileStore","dojo/_base/array,dojo/_base/declare,dojo/_base/json,dojo/_base/lang,dojo/_base/window,dojo/_base/xhr,dojo/Evented,./util/filter,./util/sorter".split(","),function(z,C,w,k,j,n,D,A,B){var q="_S",r="_SR",p="_PRM",s="_EX",l="children",o="path";return C([D],{constructor:function(a){this._features={"dojo.data.api.Read":!0,"dojo.data.api.Write":!0,"dojo.data.api.Identity":!0,"dojo.data.api.Notification":!0};this._closePending=this._loadFinished=this._loadInProgress=!1;this._requestQueue=
[];this._authToken=null;this._itemsByIdentity={};this._allFileItems=[];this._privateAttrs=[q,p,s,r];this._readOnlyAttrs=["name","size","modified","directory","icon",l,o];this._rootDir=null;this._rootId=".";for(var b in a)this.set(b,a[b])},authToken:null,basePath:".",cache:!1,clearOnClose:!1,failOk:!1,options:[],url:"",urlPreventCache:!1,moduleName:"cbTree/store/FileStore",_addIconClass:!1,_labelAttr:"name",_validated:!1,_assertIsItem:function(a){if(!this.isItem(a))throw Error(this.moduleName+"::_assertIsItem(): Invalid item argument.");
},_assertIsAttribute:function(a,b){if("string"!==typeof a)throw Error(this.moduleName+"::"+b+"(): Invalid attribute argument.");},_assertSupport:function(a){throw Error(this.moduleName+"::"+a+"(): Function not supported on a File Store.");},_containsValue:function(a,b,c,d){return"undefined"!==typeof a[b]?z.some(this.getValues(a,b),function(a){if(null!==a&&!k.isObject(a)&&d){if(a.toString().match(d))return!0}else if(c===a)return!0}):!1===c||void 0===c},_deleteFromServer:function(a){var b=a.scope||
j.global,c=a.item,d=this.getPath(c),e=this;if(this._loadInProgress)this._queueRequest({args:a,func:this._deleteFromServer,scope:e});else{this._loadInProgress=!0;var g=this._requestToArgs("DELETE",{path:d}),d=n.del(g),f;d.addCallback(function(c){try{f=e._updateFileStore("DELETE",c,a),e._loadInProgress=!1,a.onComplete&&a.onComplete.call(b,f)}catch(d){e._loadInProgress=!1,a.onError?a.onError.call(b,d):console.error(d)}e._handleQueuedRequest()});d.addErrback(function(d){e._loadInProgress=!1;switch(g.status){case 404:case 410:e._resyncStore(c,
!0);break;default:a.onError&&a.onError.call(b,d,g.status)}e._handleQueuedRequest()})}},_deleteFromStore:function(a,b){var c=a[o],d=this.getValue(a,p),e=this.getValues(d,l),g=[];if(a.directory){var f=this.getValues(a,l),v,h;for(h=0;h<f.length;h++)v=this._deleteFromStore(f[h],!1),g=g.concat(v);a[s]=!1}this._removeArrayElement(this._allFileItems,a);this._removeArrayElement(e,a);delete this._itemsByIdentity[c];a[q]=null;a.deleted=!0;g.push(a);this._setValues(d,l,e,b);this.onDelete(a);return g},_fetchFinal:function(a,
b){function c(a,b){var c=(a.queryOptions||{}).ignoreCase||!1,d=a.query||null,g=[];if(b){if(d){var f={},x,y,t,u;for(t in d)u=d[t],"string"===typeof u?f[t]=A.patternToRegExp(u,c):u instanceof RegExp&&(f[t]=u);for(c=0;c<b.length;++c){y=b[c];x=!0;for(t in d)if(u=d[t],!e._containsValue(y,t,u,f[t])){x=!1;break}x&&g.push(y)}}else g=b.slice(0);g.length&&a.sort&&(d=new B(a.sort),g.sort(d.sortFunction()));d=a.start?a.start:0;f=a.count&&isFinite(a.count)?a.count:g.length;if(d||f!==g.length)g=g.slice(d,d+f);
return g}return null}var d=a.scope||j.global,e=this,g=[],f=0;a.abort=function(){this.aborted=!0};a.store=this;if(!0!==a.aborted){f=(g=c(a,b))?g.length:-1;a.onBegin&&a.onBegin.call(d,f,a);if(a.onItem&&0<f)for(i=0;i<f&&!0!==a.aborted;i++)a.onItem.call(d,g[i],a);a.onComplete&&(a.onItem||a.aborted||0>f?a.onComplete.call(d,null,a):a.onComplete.call(d,g,a))}a.onAbort&&a.aborted&&a.onAbort.call(d,a)},_getItemsArray:function(a){return a&&a.deep?this._allFileItems:this._rootDir[l]},_getItemByIdentity:function(a){var b=
null;this._itemsByIdentity&&Object.hasOwnProperty.call(this._itemsByIdentity,a)&&(b=this._itemsByIdentity[a]);return b},_handleQueuedRequest:function(){for(var a,b;0<this._requestQueue.length&&!this._loadInProgress;)b=this._requestQueue.shift(),a=b.func,a.call(b.scope,b.args)},_isPrivateAttr:function(a){for(var b in this._privateAttrs)if(a==this._privateAttrs[b])return!0;return!1},_isReadOnlyAttr:function(a){for(var b in this._readOnlyAttrs)if(a==this._readOnlyAttrs[b])return!0;return!1},_queueRequest:function(a){this._closePending?
(a=a.args,a.closePending=!0,this._fetchFinal(a,null)):this._requestQueue.push(a)},_removeArrayElement:function(a,b){var c=z.indexOf(a,b);return-1!=c?(a.splice(c,1),!0):!1},_renameItem:function(a){var b=a.scope||j.global,c=a.item,d=this.getPath(c),e=this;if(this._loadInProgress)this._queueRequest({args:a,func:this._renameItem,scope:e});else{this._loadInProgress=!0;var g=a.newValue,f=this._requestToArgs("POST",{path:d,newValue:g}),d=n.post(f);d.addCallback(function(c){try{e._updateFileStore("POST",
c,a),e._loadInProgress=!1,a.onItem&&a.onItem.call(b,e._getItemByIdentity(g))}catch(d){if(e._loadInProgress=!1,a.onError)a.onError(d);else console.error(d)}e._handleQueuedRequest()});d.addErrback(function(d){e._loadInProgress=!1;switch(f.status){case 404:case 410:e._resyncStore(c)}a.onError&&a.onError.call(b,d,f.status);e._handleQueuedRequest()})}},_requestToArgs:function(a,b){var c={},d=null,d=!1;if(this.basePath)c.basePath=this.basePath;if(b.path)c.path=b.path;if(this.authToken)c.authToken=w.toJson(this.authToken);
if(b.sync)d=b.sync;switch(a){case "GET":if(b.queryOptions)c.queryOptions=w.toJson(b.queryOptions);if(this.options&&this.options.length)c.options=w.toJson(this.options);break;case "POST":if(b.newValue)c.newValue=b.newValue}return d={url:this.url,handleAs:"json",content:c,preventCache:this.urlPreventCache,handle:function(a,b){this.status=b.xhr.status},failOk:this.failOk,status:200,sync:d}},_resyncStore:function(){var a=this.getParents(item)[0];if(a)this.loadItem({item:a,forceLoad:!0});else{if(item===
this._rootDir)throw Error(this.moduleName+"::_resyncStore(): Store root directory failed to reload.");throw Error(this.moduleName+"::_resyncStore(): File Store is corrupt.");}},_setAuthTokenAttr:function(a){if(k.isObject(a))this.authToken=a;return!1},_setOptionsAttr:function(a){if(k.isArray(a))this.options=a;else if(k.isString(a))this.options=a.split(",");else throw Error(this.moduleName+"::_setOptionsAttr(): Options must be a comma separated string of keywords or an array of keyword strings.");for(a=
0;a<this.options.length;a++)if("iconClass"===this.options[a])this._addIconClass=!0;return this.options},_setValue:function(a,b,c,d){var e;e=a[b];a[b]=c;if(d)this.onSet(a,b,e,c);return!0},_setValues:function(a,b,c,d){var e;e=this.getValues(a,b);if(k.isArray(c))0===c.length&&b!==l?(delete a[b],c=void 0):a[b]=c.slice(0,c.length);else throw Error(this.moduleName+"::setValues(): newValues not an array");if(d)this.onSet(a,b,e,c);return!0},_updateFileStore:function(a,b){function c(a){var b=[];(a=h._getItemByIdentity(a))&&
(b=h._deleteFromStore(a,!0));return b}function d(a){var b=h._getItemByIdentity(a);if(!b)if(b=h._rootDir){var a=a.split("/"),c=".",e=null;for(m=0;m<a.length;m++)"."!==a[m]&&(c=c+"/"+a[m],(e=h._getItemByIdentity(c))?b=e:(e={name:a[m],path:c,directory:!0,size:0},e[l]=[],e[s]=!1,b=g(e,b,!0)))}else return g(null,null,!1),d(a);return b}function e(a,b,c){var d=!1,e={},g,f;for(f in b)if(f!=l&&f!=s&&(g=b[f],!(f in a)||a[f]!==g&&(!(f in e)||e[f]!==g))){d=a[f];a[f]=g;if(c)h.onSet(a,f,d,g);d=!0}return d}function g(a,
b,c){if(b){var d=h.getIdentity(a);if(!h._getItemByIdentity(d)){var e=h.getValues(b,l),g=h.getValues(b,l);a[q]=h;a[p]=b;h._addIconClass&&f(a);h._itemsByIdentity[d]=a;h._allFileItems.push(a);g.push(a);h._setValues(b,l,g,!1);h._loadFinished&&c&&(c={item:b,attribute:l,oldValue:e,newValue:g},h.onNew(a,!b[r]?c:null));return a}throw Error(h.moduleName+"::_updateFileStore:newFile(): item ["+a.path+"] already exist.");}if(!h._rootDir)return a={name:h._rootId,path:h._rootId,directory:!0},a[q]=h,a[l]=[],a[s]=
!1,a[r]=!0,h._addIconClass&&f(a),h._itemsByIdentity[h._rootId]=a,h._allFileItems.push(a),h._rootDir=a;throw Error(h.moduleName+"::_updateFileStore:newFile(): item has no parent.");}function f(a){var b;a.directory?b="fileIconDIR":(b=a.name.lastIndexOf("."),0<b?(b=a.name.substr(b+1).toLowerCase(),b=b.replace(/^[a-z]|-[a-zA-Z]/g,function(a){return a.charAt(a.length-1).toUpperCase()}),b="fileIcon"+b):b="fileIconUnknown");a.icon=b+" fileIcon"}function v(a,b){var c=a[o],f=a.oldPath;if(f&&f!==c){var j=h._getItemByIdentity(f);
if(j)h._deleteFromStore(j,!0),delete a.oldPath;else throw Error(h.moduleName+"::_updateFileStore:updateFile(): Unable to resolve ["+f+"].");}b||(f=c.lastIndexOf("/"),f=c.substr(0,f)||c,b=d(f));if((f=h._getItemByIdentity(c))&&f.directory!==a.directory)h._deleteFromStore(f,!0),f=null;f?e(f,a,!0):a.directory?(f=d(c),e(f,a,!1)):f=g(a,b,!0);if(f.directory&&a[s]){var c=f,j=a[l],k=!1,m,n,p=h.getValues(c,l),q=h.getValues(c,l),r=[];for(n=0;n<j.length;n++)m=v(j[n],c),h._removeArrayElement(q,m),r.push(m);if(0<
q.length){for(m=q.shift();m;)h._deleteFromStore(m,!1),m=q.shift();k=!0}p.length!=r.length&&(k=!0);if(k&&h._loadFinished)h.onSet(c,l,p,r);c[s]=!0}return f}var h=this;if(b){var k=b.items,j,n=[],m;if(k){1<k.length&&(j=new B([{attribute:o}]),k.sort(j.sortFunction()));for(m=0;m<k.length;m++)if(j=h.getIdentity(k[m]))switch(a){case "DELETE":j=c(j);n=n.concat(j);break;case "GET":case "POST":j=v(k[m],null),n.push(j)}this._loadFinished=!0;return n}}throw Error(this.moduleName+"::_updateFileStore(): Empty or malformed server response.");
},attachToRoot:function(a){this._assertIsItem(a);this.renameItem(a,this._rootId+"/"+a.name)},close:function(a){if(this._loadInProgress)this._queueRequest({args:a,func:this.close,scope:this}),this._closePending=!0;else{if(this.clearOnClose)this._closePending=!0,this._queuedFetches=[],this._itemsByIdentity={},this._allFileItems=[],this._rootDir=null,this._validated=this._loadInProgress=this._loadFinished=!1;this.onClose(this.clearOnClose);this._closePending=!1}},containsValue:function(a,b,c){var d=
void 0;"string"===typeof c&&(d=A.patternToRegExp(c,!1));return this._containsValue(a,b,c,d)},deleteItem:function(a,b,c,d,e){e=e||j.global;this._assertIsItem(a);if(!(b&&!0!==b.call(e,a)))return a={item:a,onComplete:c,onError:d,scope:e},this._deleteFromServer(a),a},fetch:function(a){var b=a.scope||j.global,c=a.queryOptions||null,d=c?c.storeOnly:!1,e=this;if((this.cache||d)&&this._loadFinished)this._fetchFinal(a,this._getItemsArray(c));else if(this._loadInProgress)this._queueRequest({args:a,func:this.fetch,
scope:e});else{this._loadInProgress=!0;var g=this._requestToArgs("GET",a),d=n.get(g);d.addCallback(function(d){try{e._updateFileStore("GET",d,a),e._loadInProgress=!1,e._fetchFinal(a,e._getItemsArray(c))}catch(g){e._loadInProgress=!1,a.onError?a.onError.call(b,g):console.error(g)}e._handleQueuedRequest()});d.addErrback(function(c){e._loadInProgress=!1;a.onError?a.onError.call(b,c,g.status):console.error(c);e._handleQueuedRequest()})}a.abort=function(){this.aborted=!0};return a},fetchChildren:function(a){var b=
a.item||this._rootDir,c=this;this._assertIsItem(b);!this.isItemLoaded(b)||a.forceLoad?this.loadItem({item:b,forceLoad:a.forceLoad,onError:a.onError,onItem:function(b){c._fetchFinal(a,b[l])}}):this._fetchFinal(a,b[l]);a.abort=function(){this.aborted=!0};return a},fetchItemByIdentity:function(a){var b=a.scope||j.global,c=a.identity||a[o],d=a.queryOptions||null,d=d?d.storeOnly:!1,e=this,g;g=this._getItemByIdentity(c);if((this.cache||d)&&this._loadFinished&&g)a.onItem&&a.onItem.call(b,g);else if(this._loadInProgress)this._queueRequest({args:a,
func:this.fetchItemByIdentity,scope:e});else{this._loadInProgress=!0;var f=this._requestToArgs("GET",{path:c}),d=n.get(f);d.addCallback(function(d){try{e._updateFileStore("GET",d,a),e._loadInProgress=!1,a.onItem&&(g=e._getItemByIdentity(c),a.onItem.call(b,g))}catch(f){e._loadInProgress=!1,a.onError?a.onError.call(b,f):console.error(f)}e._handleQueuedRequest()});d.addErrback(function(c){e._loadInProgress=!1;switch(f.status){case 404:case 410:g&&e._resyncStore(g);default:a.onError&&a.onError.call(b,
c,f.status)}e._handleQueuedRequest()})}},getAttributes:function(a){this._assertIsItem(a);var b=[],c;for(c in a)this._isPrivateAttr(c)||b.push(c);return b},getDirectory:function(a){this._assertIsItem(a);if(a=this.getValue(a,p))return this.getPath(a)},getFeatures:function(){return this._features},getIdentity:function(a){if(a)return a[o]},getIdentifierAttr:function(){return o},getIdentityAttributes:function(){return[o]},getLabel:function(a){if(this._labelAttr&&this.isItem(a))return this.getValue(a,this._labelAttr)},
getLabelAttributes:function(){return this._labelAttr?[this._labelAttr]:null},getParents:function(a){if(a)return a[p]==this._rootDir?[]:this.getValues(a,p)},getPath:function(a){this._assertIsItem(a);return a[o]},getValue:function(a,b,c){a=this.getValues(a,b);return 0<a.length?a[0]:c},getValues:function(a,b){var c=[];this._assertIsItem(a);this._assertIsAttribute(b,"getValues");void 0!==a[b]&&(c=k.isArray(a[b])?a[b]:[a[b]]);return c.slice(0)},hasAttribute:function(a,b){this._assertIsItem(a);this._assertIsAttribute(b,
"hasAttribute");return b in a},isItem:function(a){return a&&a[q]===this&&this._itemsByIdentity[a[o]]===a?!0:!1},isItemLoaded:function(a){var b=this.isItem(a);b&&a.directory&&!a[s]&&(b=!1);return b},isRootItem:function(a){this._assertIsItem(a);return(a=this.getValue(a,p))&&a[r]?!0:!1},isValidated:function(){return this._validated},loadItem:function(a){var b=a.forceLoad||!1,c=a.scope||j.global,d=a.item,e=this;if(this.isItem(d)&&!(!0!==b&&this.isItemLoaded(d))){var g=this.getIdentity(d);if(this._loadInProgress)this._queueRequest({args:a,
func:this.loadItem,scope:e});else{this._loadInProgress=!0;var f=this._requestToArgs("GET",{path:g}),b=n.get(f);b.addCallback(function(b){try{e._updateFileStore("GET",b,a),e._loadInProgress=!1,a.onItem&&(d=e._getItemByIdentity(g),a.onItem.call(c,d))}catch(f){if(e._loadInProgress=!1,a.onError)a.onError(f);else console.error(f)}e._handleQueuedRequest()});b.addErrback(function(b){e._loadInProgress=!1;if(d!==e._rootDir)switch(f.status){case 404:case 410:e._resyncStore(d)}a.onError&&a.onError.call(c,b,
f.status);e._handleQueuedRequest()})}}},loadStore:function(a){function b(a,b){var c=b.loadArgs||null,e=c.scope;d.onLoad(a);c&&c.onComplete&&c.onComplete.call(e,a)}var c=a.scope||j.global,d=this;if(this._loadFinished)onComplete&&onComplete.call(c,this._allFileItems.length);else if(this._loadInProgress)this._queueRequest({args:a,func:this.loadStore,scope:d});else{a={queryOptions:{deep:!0},loadArgs:a,onBegin:b,onError:a.onError,scope:this};try{this.fetch(a)}catch(e){if(onError)onError.call(c,e);else throw e;
}}},renameItem:function(a,b,c,d,e){e=e||j.global;this._assertIsItem(a);this._assertIsAttribute(b,"renameItem");a!=this._rootDir&&(a[o]!==b?this._renameItem({item:a,newValue:b,onItem:c,onError:d,scope:e}):k.isFunction(c)&&c.call(e,a))},set:function(a,b){if(k.isString(a)){var c="_set"+a.replace(/^[a-z]|-[a-zA-Z]/g,function(a){return a.charAt(a.length-1).toUpperCase()})+"Attr";if(k.isFunction(this[c]))return this[c](b);if(void 0!==this[a])return this[a]=b,this[a]}throw Error(this.moduleName+"::set(): Invalid attribute");
},setValidated:function(a){this._validated=Boolean(a)},setValue:function(a,b,c){this._assertIsItem(a);this._assertIsAttribute(b,"setValue");if("undefined"===typeof c)throw Error(this.moduleName+"::setValue(): newValue is undefined");if(b!==o){if(this._isReadOnlyAttr(b)||this._isPrivateAttr(b))throw Error(this.moduleName+"::setValue(): attribute ["+b+"] is private or read-only");return this._setValue(a,b,c,!0)}a[o]!==c&&this._renameItem({item:a,newValue:c})},setValues:function(a,b,c){this._assertIsItem(a);
this._assertIsAttribute(b,"setValues");if("undefined"===typeof c)throw Error(this.moduleName+"::setValue(): newValue is undefined");if(this._isReadOnlyAttr(b)||this._isPrivateAttr(b))throw Error(this.moduleName+"::setValues(): attribute ["+b+"] is private or read-only");return this._setValues(a,b,c,!0)},unsetAttribute:function(a,b){return this.setValues(a,b,[])},onClose:function(){},onDelete:function(a){var b=a[p];if(b&&b[r])this.onRoot(a,"delete")},onLoad:function(){},onNew:function(a){if(this.isRootItem(a))this.onRoot(a,
"new")},onRoot:function(){},onSet:function(){},isDirty:function(){return!1},newItem:function(){this._assertSupport("newItem")},revert:function(){},save:function(){},addReference:function(){this._assertSupport("addReference")},detachFromRoot:function(){this._assertSupport("detachFromRoot")},itemExist:function(a){if("object"!=typeof a)throw Error(this.moduleName+"::itemExist(): argument is not an object.");a=a[o];if("undefined"===typeof a)throw Error(this.moduleName+"::itemExist(): argument does not include an identity.");
return this._getItemByIdentity(a)},removeReference:function(){this._assertSupport("removeReference")}})});