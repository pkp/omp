/**
 * @licstart The following is the entire license notice for the
 * JavaScript code in this page
 *
 * Copyright 2024 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @licend The above is the entire license notice for the
 * JavaScript code in this page
 */

/**
 * pdfjsVersion = 6.4.299
 * pdfjsBuild = d0991a0d5
 */
/******/ var __webpack_modules__ = ({

/***/ 306
(module, __unused_webpack_exports, __webpack_require__) {


var isCallable = __webpack_require__(901);
var tryToString = __webpack_require__(823);

var $TypeError = TypeError;

// `Assert: IsCallable(argument) is true`
module.exports = function (argument) {
  if (isCallable(argument)) return argument;
  throw new $TypeError(tryToString(argument) + ' is not a function');
};


/***/ },

/***/ 576
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);

var toString = uncurryThis({}.toString);
var stringSlice = uncurryThis(''.slice);

module.exports = function (it) {
  return stringSlice(toString(it), 8, -1);
};


/***/ },

/***/ 837
(module) {


var $TypeError = TypeError;
var MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF; // 2 ** 53 - 1 == 9007199254740991

module.exports = function (it) {
  if (it > MAX_SAFE_INTEGER) throw new $TypeError('Maximum allowed index exceeded');
  return it;
};


/***/ },

/***/ 39
(module) {


module.exports = function (exec) {
  try {
    return !!exec();
  } catch (error) {
    return true;
  }
};


/***/ },

/***/ 616
(module, __unused_webpack_exports, __webpack_require__) {


var fails = __webpack_require__(39);

module.exports = !fails(function () {
  // eslint-disable-next-line es/no-function-prototype-bind -- safe
  var test = function () { /* empty */ }.bind();
  // eslint-disable-next-line no-prototype-builtins -- safe
  return typeof test != 'function' || test.hasOwnProperty('prototype');
});


/***/ },

/***/ 504
(module, __unused_webpack_exports, __webpack_require__) {


var NATIVE_BIND = __webpack_require__(616);

var FunctionPrototype = Function.prototype;
var call = FunctionPrototype.call;
// eslint-disable-next-line es/no-function-prototype-bind -- safe
var uncurryThisWithBind = NATIVE_BIND && FunctionPrototype.bind.bind(call, call);

module.exports = NATIVE_BIND ? uncurryThisWithBind : function (fn) {
  return function () {
    return call.apply(fn, arguments);
  };
};


/***/ },

/***/ 966
(module, __unused_webpack_exports, __webpack_require__) {


var aCallable = __webpack_require__(306);
var isNullOrUndefined = __webpack_require__(117);

// `GetMethod` abstract operation
// https://tc39.es/ecma262/#sec-getmethod
module.exports = function (V, P) {
  var func = V[P];
  return isNullOrUndefined(func) ? undefined : aCallable(func);
};


/***/ },

/***/ 55
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);
var fails = __webpack_require__(39);
var classof = __webpack_require__(576);

var $Object = Object;
var split = uncurryThis(''.split);

// fallback for non-array-like ES3 and non-enumerable old V8 strings
module.exports = fails(function () {
  // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
  // eslint-disable-next-line no-prototype-builtins -- safe
  return !$Object('z').propertyIsEnumerable(0);
}) ? function (it) {
  return classof(it) === 'String' ? split(it, '') : $Object(it);
} : $Object;


/***/ },

/***/ 376
(module, __unused_webpack_exports, __webpack_require__) {


var classof = __webpack_require__(576);

// `IsArray` abstract operation
// https://tc39.es/ecma262/#sec-isarray
// eslint-disable-next-line es/no-array-isarray -- safe
module.exports = Array.isArray || function isArray(argument) {
  return classof(argument) === 'Array';
};


/***/ },

/***/ 901
(module) {


// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot
var documentAll = typeof document == 'object' && document.all;

// `IsCallable` abstract operation
// https://tc39.es/ecma262/#sec-iscallable
// eslint-disable-next-line unicorn/no-typeof-undefined -- required for testing
module.exports = typeof documentAll == 'undefined' && documentAll !== undefined ? function (argument) {
  return typeof argument == 'function' || argument === documentAll;
} : function (argument) {
  return typeof argument == 'function';
};


/***/ },

/***/ 796
(module, __unused_webpack_exports, __webpack_require__) {


var fails = __webpack_require__(39);
var isCallable = __webpack_require__(901);

var replacement = /#|\.prototype\./;

var isForced = function (feature, detection) {
  var value = data[normalize(feature)];
  return value === POLYFILL ? true
    : value === NATIVE ? false
    : isCallable(detection) ? fails(detection)
    : !!detection;
};

var normalize = isForced.normalize = function (string) {
  return String(string).replace(replacement, '.').toLowerCase();
};

var data = isForced.data = {};
var NATIVE = isForced.NATIVE = 'N';
var POLYFILL = isForced.POLYFILL = 'P';

module.exports = isForced;


/***/ },

/***/ 117
(module) {


// we can't use just `it == null` since of `document.all` special case
// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot-aec
module.exports = function (it) {
  return it === null || it === undefined;
};


/***/ },

/***/ 395
(module) {


module.exports = false;


/***/ },

/***/ 248
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);

// eslint-disable-next-line es/no-map -- safe
var MapPrototype = Map.prototype;

module.exports = {
  // eslint-disable-next-line es/no-map -- safe
  Map: Map,
  set: uncurryThis(MapPrototype.set),
  get: uncurryThis(MapPrototype.get),
  has: uncurryThis(MapPrototype.has),
  remove: uncurryThis(MapPrototype['delete']),
  proto: MapPrototype
};


/***/ },

/***/ 741
(module) {


var ceil = Math.ceil;
var floor = Math.floor;

// `Math.trunc` method
// https://tc39.es/ecma262/#sec-math.trunc
// eslint-disable-next-line es/no-math-trunc -- safe
module.exports = Math.trunc || function trunc(x) {
  var n = +x;
  return (n > 0 ? floor : ceil)(n);
};


/***/ },

/***/ 819
(module, __unused_webpack_exports, __webpack_require__) {


/* eslint-disable es/no-json -- safe */
var fails = __webpack_require__(39);

module.exports = !fails(function () {
  var unsafeInt = '9007199254740993';
  // eslint-disable-next-line es/no-json-rawjson -- feature detection
  var raw = JSON.rawJSON(unsafeInt);
  // eslint-disable-next-line es/no-json-israwjson -- feature detection
  return !JSON.isRawJSON(raw) || JSON.stringify(raw) !== unsafeInt;
});


/***/ },

/***/ 717
(__unused_webpack_module, exports) {


// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
exports.f = Object.getOwnPropertySymbols;


/***/ },

/***/ 625
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);

module.exports = uncurryThis({}.isPrototypeOf);


/***/ },

/***/ 750
(module, __unused_webpack_exports, __webpack_require__) {


var isNullOrUndefined = __webpack_require__(117);

var $TypeError = TypeError;

// `RequireObjectCoercible` abstract operation
// https://tc39.es/ecma262/#sec-requireobjectcoercible
module.exports = function (it) {
  if (isNullOrUndefined(it)) throw new $TypeError("Can't call method on " + it);
  return it;
};


/***/ },

/***/ 240
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);

// `thisNumberValue` abstract operation
// https://tc39.es/ecma262/#sec-thisnumbervalue
module.exports = uncurryThis(1.1.valueOf);


/***/ },

/***/ 291
(module, __unused_webpack_exports, __webpack_require__) {


var trunc = __webpack_require__(741);

// `ToIntegerOrInfinity` abstract operation
// https://tc39.es/ecma262/#sec-tointegerorinfinity
module.exports = function (argument) {
  var number = +argument;
  // eslint-disable-next-line no-self-compare -- NaN check
  return number !== number || number === 0 ? 0 : trunc(number);
};


/***/ },

/***/ 14
(module, __unused_webpack_exports, __webpack_require__) {


var toIntegerOrInfinity = __webpack_require__(291);

var min = Math.min;

// `ToLength` abstract operation
// https://tc39.es/ecma262/#sec-tolength
module.exports = function (argument) {
  var len = toIntegerOrInfinity(argument);
  return len > 0 ? min(len, 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
};


/***/ },

/***/ 981
(module, __unused_webpack_exports, __webpack_require__) {


var requireObjectCoercible = __webpack_require__(750);

var $Object = Object;

// `ToObject` abstract operation
// https://tc39.es/ecma262/#sec-toobject
module.exports = function (argument) {
  return $Object(requireObjectCoercible(argument));
};


/***/ },

/***/ 823
(module) {


var $String = String;

module.exports = function (argument) {
  try {
    return $String(argument);
  } catch (error) {
    return 'Object';
  }
};


/***/ },

/***/ 392
(module, __unused_webpack_exports, __webpack_require__) {


var uncurryThis = __webpack_require__(504);

var id = 0;
var postfix = Math.random();
var toString = uncurryThis(1.1.toString);

module.exports = function (key) {
  return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString(++id + postfix, 36);
};


/***/ }

/******/ });
/************************************************************************/
/******/ // The module cache
/******/ const __webpack_module_cache__ = {};
/******/ 
/******/ // The require function
/******/ function __webpack_require__(moduleId) {
/******/ 	// Check if module is in cache
/******/ 	const cachedModule = __webpack_module_cache__[moduleId];
/******/ 	if (cachedModule !== undefined) {
/******/ 		return cachedModule.exports;
/******/ 	}
/******/ 	// Create a new module (and put it into the cache)
/******/ 	const module = __webpack_module_cache__[moduleId] = {
/******/ 		// no module.id needed
/******/ 		// no module.loaded needed
/******/ 		exports: {}
/******/ 	};
/******/ 
/******/ 	// Execute the module function
/******/ 	__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 
/******/ 	// Return the exports of the module
/******/ 	return module.exports;
/******/ }
/******/ 
/************************************************************************/
/******/ /* webpack/runtime/concatenation wrap */
/******/ // wrap a concatenated module body as a lazy, memoized accessor; mod is
/******/ // set before the body runs so re-entrant calls (require cycles) observe
/******/ // the partial exports like Node.js
/******/ __webpack_require__.cw = (body) => {
/******/ 	var mod;
/******/ 	return () => {
/******/ 		if (body) {
/******/ 			var fn = body;
/******/ 			body = 0;
/******/ 			mod = { exports: {} };
/******/ 			fn.call(mod.exports, mod, mod.exports);
/******/ 		}
/******/ 		return mod.exports;
/******/ 	};
/******/ };
/******/ 
/******/ /* webpack/runtime/define property getters */
/******/ // define getter/value functions for harmony exports
/******/ __webpack_require__.d = (exports, definition) => {
/******/ 	for(var key in definition) {
/******/ 		if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 			Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 		}
/******/ 	}
/******/ };
/******/ 
/******/ /* webpack/runtime/hasOwnProperty shorthand */
/******/ __webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop));
/******/ 
/************************************************************************/

// EXTERNAL MODULE: ./node_modules/core-js/internals/a-callable.js
var a_callable_namespaceFn = () => {
	return __webpack_require__(306);
};

// MODULE: ./node_modules/core-js/internals/an-object.js
var an_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var isObject = (is_object_namespaceFn());

var $String = String;
var $TypeError = TypeError;

// `Assert: Type(argument) is Object`
module.exports = function (argument) {
  if (isObject(argument)) return argument;
  throw new $TypeError($String(argument) + ' is not an object');
};

});

// MODULE: ./node_modules/core-js/internals/array-includes.js
var array_includes_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var toIndexedObject = (to_indexed_object_namespaceFn());
var toAbsoluteIndex = (to_absolute_index_namespaceFn());
var lengthOfArrayLike = (length_of_array_like_namespaceFn());

// `Array.prototype.{ indexOf, includes }` methods implementation
var createMethod = function (IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIndexedObject($this);
    var length = lengthOfArrayLike(O);
    if (length === 0) return !IS_INCLUDES && -1;
    var index = toAbsoluteIndex(fromIndex, length);
    var value;
    // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare -- NaN check
    if (IS_INCLUDES && el !== el) while (length > index) {
      value = O[index++];
      // eslint-disable-next-line no-self-compare -- NaN check
      if (value !== value) return true;
    // Array#indexOf ignores holes, Array#includes - not
    } else for (;length > index; index++) {
      if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
    } return !IS_INCLUDES && -1;
  };
};

module.exports = {
  // `Array.prototype.includes` method
  // https://tc39.es/ecma262/#sec-array.prototype.includes
  includes: createMethod(true),
  // `Array.prototype.indexOf` method
  // https://tc39.es/ecma262/#sec-array.prototype.indexof
  indexOf: createMethod(false)
};

});

// MODULE: ./node_modules/core-js/internals/array-set-length.js
var array_set_length_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var isArray = (is_array_namespaceFn());

var $TypeError = TypeError;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// Safari < 13 does not throw an error in this case
var SILENT_ON_NON_WRITABLE_LENGTH_SET = DESCRIPTORS && !function () {
  // makes no sense without proper strict mode support
  if (this !== undefined) return true;
  try {
    // eslint-disable-next-line es/no-object-defineproperty -- safe
    Object.defineProperty([], 'length', { writable: false }).length = 1;
  } catch (error) {
    return error instanceof TypeError;
  }
}();

module.exports = SILENT_ON_NON_WRITABLE_LENGTH_SET ? function (O, length) {
  if (isArray(O) && !getOwnPropertyDescriptor(O, 'length').writable) {
    throw new $TypeError('Cannot set read only .length');
  } return O.length = length;
} : function (O, length) {
  return O.length = length;
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/classof-raw.js
var classof_raw_namespaceFn = () => {
	return __webpack_require__(576);
};

// MODULE: ./node_modules/core-js/internals/classof.js
var classof_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var TO_STRING_TAG_SUPPORT = (to_string_tag_support_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var classofRaw = (classof_raw_namespaceFn());
var wellKnownSymbol = (well_known_symbol_namespaceFn());

var TO_STRING_TAG = wellKnownSymbol('toStringTag');
var $Object = Object;

// ES3 wrong here
var CORRECT_ARGUMENTS = classofRaw(function () { return arguments; }()) === 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (error) { /* empty */ }
};

// getting tag from ES6+ `Object.prototype.toString`
module.exports = TO_STRING_TAG_SUPPORT ? classofRaw : function (it) {
  var O, tag, result;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (tag = tryGet(O = $Object(it), TO_STRING_TAG)) == 'string' ? tag
    // builtinTag case
    : CORRECT_ARGUMENTS ? classofRaw(O)
    // ES3 arguments fallback
    : (result = classofRaw(O)) === 'Object' && isCallable(O.callee) ? 'Arguments' : result;
};

});

// MODULE: ./node_modules/core-js/internals/copy-constructor-properties.js
var copy_constructor_properties_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var hasOwn = (has_own_property_namespaceFn());
var ownKeys = (own_keys_namespaceFn());
var getOwnPropertyDescriptorModule = (object_get_own_property_descriptor_namespaceFn());
var definePropertyModule = (object_define_property_namespaceFn());

module.exports = function (target, source, exceptions) {
  var keys = ownKeys(source);
  var defineProperty = definePropertyModule.f;
  var getOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
  for (var i = 0; i < keys.length; i++) {
    var key = keys[i];
    if (!hasOwn(target, key) && !(exceptions && hasOwn(exceptions, key))) {
      defineProperty(target, key, getOwnPropertyDescriptor(source, key));
    }
  }
};

});

// MODULE: ./node_modules/core-js/internals/create-non-enumerable-property.js
var create_non_enumerable_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var definePropertyModule = (object_define_property_namespaceFn());
var createPropertyDescriptor = (create_property_descriptor_namespaceFn());

module.exports = DESCRIPTORS ? function (object, key, value) {
  return definePropertyModule.f(object, key, createPropertyDescriptor(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};

});

// MODULE: ./node_modules/core-js/internals/create-property-descriptor.js
var create_property_descriptor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};

});

// MODULE: ./node_modules/core-js/internals/define-built-in.js
var define_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var isCallable = (is_callable_namespaceFn());
var definePropertyModule = (object_define_property_namespaceFn());
var makeBuiltIn = (make_built_in_namespaceFn());
var defineGlobalProperty = (define_global_property_namespaceFn());

module.exports = function (O, key, value, options) {
  if (!options) options = {};
  var simple = options.enumerable;
  var name = options.name !== undefined ? options.name : key;
  if (isCallable(value)) makeBuiltIn(value, name, options);
  if (options.global) {
    if (simple) O[key] = value;
    else defineGlobalProperty(key, value);
  } else {
    try {
      if (!options.unsafe) delete O[key];
      else if (O[key]) simple = true;
    } catch (error) { /* empty */ }
    if (simple) O[key] = value;
    else definePropertyModule.f(O, key, {
      value: value,
      enumerable: false,
      configurable: !options.nonConfigurable,
      writable: !options.nonWritable
    });
  } return O;
};

});

// MODULE: ./node_modules/core-js/internals/define-global-property.js
var define_global_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());

// eslint-disable-next-line es/no-object-defineproperty -- safe
var defineProperty = Object.defineProperty;

module.exports = function (key, value) {
  try {
    defineProperty(globalThis, key, { value: value, configurable: true, writable: true });
  } catch (error) {
    globalThis[key] = value;
  } return value;
};

});

// MODULE: ./node_modules/core-js/internals/descriptors.js
var descriptors_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var fails = (fails_namespaceFn());

// Detect IE8's incomplete defineProperty implementation
module.exports = !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty({}, 1, { get: function () { return 7; } })[1] !== 7;
});

});

// MODULE: ./node_modules/core-js/internals/document-create-element.js
var document_create_element_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var isObject = (is_object_namespaceFn());

var document = globalThis.document;
// typeof document.createElement is 'object' in old IE
var EXISTS = isObject(document) && isObject(document.createElement);

module.exports = function (it) {
  return EXISTS ? document.createElement(it) : {};
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/does-not-exceed-safe-integer.js
var does_not_exceed_safe_integer_namespaceFn = () => {
	return __webpack_require__(837);
};

// MODULE: ./node_modules/core-js/internals/enum-bug-keys.js
var enum_bug_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

// IE8- don't enum bug keys
module.exports = [
  'constructor',
  'hasOwnProperty',
  'isPrototypeOf',
  'propertyIsEnumerable',
  'toLocaleString',
  'toString',
  'valueOf'
];

});

// MODULE: ./node_modules/core-js/internals/environment-user-agent.js
var environment_user_agent_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());

var navigator = globalThis.navigator;
var userAgent = navigator && navigator.userAgent;

module.exports = userAgent ? String(userAgent) : '';

});

// MODULE: ./node_modules/core-js/internals/environment-v8-version.js
var environment_v8_version_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var userAgent = (environment_user_agent_namespaceFn());

var process = globalThis.process;
var Deno = globalThis.Deno;
var versions = process && process.versions || Deno && Deno.version;
var v8 = versions && versions.v8;
var match, version;

if (v8) {
  match = v8.split('.');
  // in old Chrome, versions of V8 isn't V8 = Chrome / 10
  // but their correct versions are not interesting for us
  version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
}

// BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`
// so check `userAgent` even if `.v8` exists, but 0
if (!version && userAgent) {
  match = userAgent.match(/Edge\/(\d+)/);
  if (!match || match[1] >= 74) {
    match = userAgent.match(/Chrome\/(\d+)/);
    if (match) version = +match[1];
  }
}

module.exports = version;

});

// MODULE: ./node_modules/core-js/internals/export.js
var export_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var getOwnPropertyDescriptor = (object_get_own_property_descriptor_namespaceFn().f);
var createNonEnumerableProperty = (create_non_enumerable_property_namespaceFn());
var defineBuiltIn = (define_built_in_namespaceFn());
var defineGlobalProperty = (define_global_property_namespaceFn());
var copyConstructorProperties = (copy_constructor_properties_namespaceFn());
var isForced = (is_forced_namespaceFn());

/*
  options.target         - name of the target object
  options.global         - target is the global object
  options.stat           - export as static methods of target
  options.proto          - export as prototype methods of target
  options.real           - real prototype method for the `pure` version
  options.forced         - export even if the native feature is available
  options.bind           - bind methods to the target, required for the `pure` version
  options.wrap           - wrap constructors to preventing global pollution, required for the `pure` version
  options.unsafe         - use the simple assignment of property instead of delete + defineProperty
  options.sham           - add a flag to not completely full polyfills
  options.enumerable     - export as enumerable property
  options.dontCallGetSet - prevent calling a getter on target
  options.name           - the .name of the function if it does not match the key
*/
module.exports = function (options, source) {
  var TARGET = options.target;
  var GLOBAL = options.global;
  var STATIC = options.stat;
  var FORCED, target, key, targetProperty, sourceProperty, descriptor;
  if (GLOBAL) {
    target = globalThis;
  } else if (STATIC) {
    target = globalThis[TARGET] || defineGlobalProperty(TARGET, {});
  } else {
    target = globalThis[TARGET] && globalThis[TARGET].prototype;
  }
  if (target) for (key in source) {
    sourceProperty = source[key];
    if (options.dontCallGetSet) {
      descriptor = getOwnPropertyDescriptor(target, key);
      targetProperty = descriptor && descriptor.value;
    } else targetProperty = target[key];
    FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);
    // contained in target
    if (!FORCED && targetProperty !== undefined) {
      if (typeof sourceProperty == typeof targetProperty) continue;
      copyConstructorProperties(sourceProperty, targetProperty);
    }
    // add a flag to not completely full polyfills
    if (options.sham || (targetProperty && targetProperty.sham)) {
      createNonEnumerableProperty(sourceProperty, 'sham', true);
    }
    defineBuiltIn(target, key, sourceProperty, options);
  }
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/fails.js
var fails_namespaceFn = () => {
	return __webpack_require__(39);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/function-bind-native.js
var function_bind_native_namespaceFn = () => {
	return __webpack_require__(616);
};

// MODULE: ./node_modules/core-js/internals/function-call.js
var function_call_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var NATIVE_BIND = (function_bind_native_namespaceFn());

var call = Function.prototype.call;
// eslint-disable-next-line es/no-function-prototype-bind -- safe
module.exports = NATIVE_BIND ? call.bind(call) : function () {
  return call.apply(call, arguments);
};

});

// MODULE: ./node_modules/core-js/internals/function-name.js
var function_name_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());

var FunctionPrototype = Function.prototype;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getDescriptor = DESCRIPTORS && Object.getOwnPropertyDescriptor;

var EXISTS = hasOwn(FunctionPrototype, 'name');
// additional protection from minified / mangled / dropped function names
var PROPER = EXISTS && function something() { /* empty */ }.name === 'something';
var CONFIGURABLE = EXISTS && (!DESCRIPTORS || (DESCRIPTORS && getDescriptor(FunctionPrototype, 'name').configurable));

module.exports = {
  ...(/* unused pure expression */ null && (EXISTS)),
  ...(/* unused pure expression */ null && (PROPER)),
  CONFIGURABLE: CONFIGURABLE
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/function-uncurry-this.js
var function_uncurry_this_namespaceFn = () => {
	return __webpack_require__(504);
};

// MODULE: ./node_modules/core-js/internals/get-built-in.js
var get_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var isCallable = (is_callable_namespaceFn());

var aFunction = function (argument) {
  return isCallable(argument) ? argument : undefined;
};

module.exports = function (namespace, method) {
  return arguments.length < 2 ? aFunction(globalThis[namespace]) : globalThis[namespace] && globalThis[namespace][method];
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/get-method.js
var get_method_namespaceFn = () => {
	return __webpack_require__(966);
};

// MODULE: ./node_modules/core-js/internals/global-this.js
var global_this_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var check = function (it) {
  return it && it.Math === Math && it;
};

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
module.exports =
  // eslint-disable-next-line es/no-global-this -- safe
  check(typeof globalThis == 'object' && globalThis) ||
  check(typeof window == 'object' && window) ||
  // eslint-disable-next-line no-restricted-globals -- safe
  check(typeof self == 'object' && self) ||
  check(typeof global == 'object' && global) ||
  check(typeof this == 'object' && this) ||
  // eslint-disable-next-line no-new-func -- fallback
  (function () { return this; })() || Function('return this')();

});

// MODULE: ./node_modules/core-js/internals/has-own-property.js
var has_own_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var uncurryThis = (function_uncurry_this_namespaceFn());
var toObject = (to_object_namespaceFn());

var hasOwnProperty = uncurryThis({}.hasOwnProperty);

// `HasOwnProperty` abstract operation
// https://tc39.es/ecma262/#sec-hasownproperty
// eslint-disable-next-line es/no-object-hasown -- safe
module.exports = Object.hasOwn || function hasOwn(it, key) {
  return hasOwnProperty(toObject(it), key);
};

});

// MODULE: ./node_modules/core-js/internals/hidden-keys.js
var hidden_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

module.exports = {};

});

// MODULE: ./node_modules/core-js/internals/html.js
var html_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var getBuiltIn = (get_built_in_namespaceFn());

module.exports = getBuiltIn('document', 'documentElement');

});

// MODULE: ./node_modules/core-js/internals/ie8-dom-define.js
var ie8_dom_define_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var fails = (fails_namespaceFn());
var createElement = (document_create_element_namespaceFn());

// Thanks to IE8 for its funny defineProperty
module.exports = !DESCRIPTORS && !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty(createElement('div'), 'a', {
    get: function () { return 7; }
  }).a !== 7;
});

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/indexed-object.js
var indexed_object_namespaceFn = () => {
	return __webpack_require__(55);
};

// MODULE: ./node_modules/core-js/internals/inspect-source.js
var inspect_source_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var uncurryThis = (function_uncurry_this_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var store = (shared_store_namespaceFn());

var functionToString = uncurryThis(Function.toString);

// this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper
if (!isCallable(store.inspectSource)) {
  store.inspectSource = function (it) {
    return functionToString(it);
  };
}

module.exports = store.inspectSource;

});

// MODULE: ./node_modules/core-js/internals/internal-state.js
var internal_state_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var NATIVE_WEAK_MAP = (weak_map_basic_detection_namespaceFn());
var globalThis = (global_this_namespaceFn());
var isObject = (is_object_namespaceFn());
var createNonEnumerableProperty = (create_non_enumerable_property_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());
var shared = (shared_store_namespaceFn());
var sharedKey = (shared_key_namespaceFn());
var hiddenKeys = (hidden_keys_namespaceFn());

var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
var TypeError = globalThis.TypeError;
var WeakMap = globalThis.WeakMap;
var set, get, has;

var enforce = function (it) {
  return has(it) ? get(it) : set(it, {});
};

var getterFor = function (TYPE) {
  return function (it) {
    var state;
    if (!isObject(it) || (state = get(it)).type !== TYPE) {
      throw new TypeError('Incompatible receiver, ' + TYPE + ' required');
    } return state;
  };
};

if (NATIVE_WEAK_MAP || shared.state) {
  var store = shared.state || (shared.state = new WeakMap());
  /* eslint-disable no-self-assign -- prototype methods protection */
  store.get = store.get;
  store.has = store.has;
  store.set = store.set;
  /* eslint-enable no-self-assign -- prototype methods protection */
  set = function (it, metadata) {
    if (store.has(it)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    store.set(it, metadata);
    return metadata;
  };
  get = function (it) {
    return store.get(it) || {};
  };
  has = function (it) {
    return store.has(it);
  };
} else {
  var STATE = sharedKey('state');
  hiddenKeys[STATE] = true;
  set = function (it, metadata) {
    if (hasOwn(it, STATE)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    createNonEnumerableProperty(it, STATE, metadata);
    return metadata;
  };
  get = function (it) {
    return hasOwn(it, STATE) ? it[STATE] : {};
  };
  has = function (it) {
    return hasOwn(it, STATE);
  };
}

module.exports = {
  set: set,
  get: get,
  has: has,
  enforce: enforce,
  getterFor: getterFor
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/is-array.js
var is_array_namespaceFn = () => {
	return __webpack_require__(376);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/is-callable.js
var is_callable_namespaceFn = () => {
	return __webpack_require__(901);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/is-forced.js
var is_forced_namespaceFn = () => {
	return __webpack_require__(796);
};

// MODULE: ./node_modules/core-js/internals/is-object.js
var is_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var isCallable = (is_callable_namespaceFn());

module.exports = function (it) {
  return typeof it == 'object' ? it !== null : isCallable(it);
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/is-pure.js
var is_pure_namespaceFn = () => {
	return __webpack_require__(395);
};

// MODULE: ./node_modules/core-js/internals/is-raw-json.js
var is_raw_json_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var isObject = (is_object_namespaceFn());
var getInternalState = (internal_state_namespaceFn().get);

module.exports = function isRawJSON(O) {
  if (!isObject(O)) return false;
  var state = getInternalState(O);
  return !!state && state.type === 'RawJSON';
};

});

// MODULE: ./node_modules/core-js/internals/is-symbol.js
var is_symbol_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var getBuiltIn = (get_built_in_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var isPrototypeOf = (object_is_prototype_of_namespaceFn());
var USE_SYMBOL_AS_UID = (use_symbol_as_uid_namespaceFn());

var $Object = Object;

module.exports = USE_SYMBOL_AS_UID ? function (it) {
  return typeof it == 'symbol';
} : function (it) {
  var $Symbol = getBuiltIn('Symbol');
  return isCallable($Symbol) && isPrototypeOf($Symbol.prototype, $Object(it));
};

});

// MODULE: ./node_modules/core-js/internals/length-of-array-like.js
var length_of_array_like_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var toLength = (to_length_namespaceFn());

// `LengthOfArrayLike` abstract operation
// https://tc39.es/ecma262/#sec-lengthofarraylike
module.exports = function (obj) {
  return toLength(obj.length);
};

});

// MODULE: ./node_modules/core-js/internals/make-built-in.js
var make_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var uncurryThis = (function_uncurry_this_namespaceFn());
var fails = (fails_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());
var DESCRIPTORS = (descriptors_namespaceFn());
var CONFIGURABLE_FUNCTION_NAME = (function_name_namespaceFn().CONFIGURABLE);
var inspectSource = (inspect_source_namespaceFn());
var InternalStateModule = (internal_state_namespaceFn());

var enforceInternalState = InternalStateModule.enforce;
var getInternalState = InternalStateModule.get;
var $String = String;
// eslint-disable-next-line es/no-object-defineproperty -- safe
var defineProperty = Object.defineProperty;
var stringSlice = uncurryThis(''.slice);
var replace = uncurryThis(''.replace);
var join = uncurryThis([].join);

var CONFIGURABLE_LENGTH = DESCRIPTORS && !fails(function () {
  return defineProperty(function () { /* empty */ }, 'length', { value: 8 }).length !== 8;
});

var TEMPLATE = String(String).split('String');

var makeBuiltIn = module.exports = function (value, name, options) {
  if (stringSlice($String(name), 0, 7) === 'Symbol(') {
    name = '[' + replace($String(name), /^Symbol\(([^)]*)\).*$/, '$1') + ']';
  }
  if (options && options.getter) name = 'get ' + name;
  if (options && options.setter) name = 'set ' + name;
  if (!hasOwn(value, 'name') || (CONFIGURABLE_FUNCTION_NAME && value.name !== name)) {
    if (DESCRIPTORS) defineProperty(value, 'name', { value: name, configurable: true });
    else value.name = name;
  }
  if (CONFIGURABLE_LENGTH && options && hasOwn(options, 'arity') && value.length !== options.arity) {
    defineProperty(value, 'length', { value: options.arity });
  }
  try {
    if (options && hasOwn(options, 'constructor') && options.constructor) {
      if (DESCRIPTORS) defineProperty(value, 'prototype', { writable: false });
    // in V8 ~ Chrome 53, prototypes of some methods, like `Array.prototype.values`, are non-writable
    } else if (value.prototype) value.prototype = undefined;
  } catch (error) { /* empty */ }
  var state = enforceInternalState(value);
  if (!hasOwn(state, 'source')) {
    state.source = join(TEMPLATE, typeof name == 'string' ? name : '');
  } return value;
};

// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
// eslint-disable-next-line no-extend-native -- required
Function.prototype.toString = makeBuiltIn(function toString() {
  return isCallable(this) && getInternalState(this).source || inspectSource(this);
}, 'toString');

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/map-helpers.js
var map_helpers_namespaceFn = () => {
	return __webpack_require__(248);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/native-raw-json.js
var native_raw_json_namespaceFn = () => {
	return __webpack_require__(819);
};

// MODULE: ./node_modules/core-js/internals/object-create.js
var object_create_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

/* global ActiveXObject -- old IE, WSH */
var anObject = (an_object_namespaceFn());
var definePropertiesModule = (object_define_properties_namespaceFn());
var enumBugKeys = (enum_bug_keys_namespaceFn());
var hiddenKeys = (hidden_keys_namespaceFn());
var html = (html_namespaceFn());
var documentCreateElement = (document_create_element_namespaceFn());
var sharedKey = (shared_key_namespaceFn());

var GT = '>';
var LT = '<';
var PROTOTYPE = 'prototype';
var SCRIPT = 'script';
var IE_PROTO = sharedKey('IE_PROTO');

var EmptyConstructor = function () { /* empty */ };

var scriptTag = function (content) {
  return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
};

// Create object with fake `null` prototype: use ActiveX Object with cleared prototype
var NullProtoObjectViaActiveX = function (activeXDocument) {
  activeXDocument.write(scriptTag(''));
  activeXDocument.close();
  var temp = activeXDocument.parentWindow.Object;
  // eslint-disable-next-line no-useless-assignment -- avoid memory leak
  activeXDocument = null;
  return temp;
};

// Create object with fake `null` prototype: use iframe Object with cleared prototype
var NullProtoObjectViaIFrame = function () {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = documentCreateElement('iframe');
  var JS = 'java' + SCRIPT + ':';
  var iframeDocument;
  iframe.style.display = 'none';
  html.appendChild(iframe);
  // https://github.com/zloirock/core-js/issues/475
  iframe.src = String(JS);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(scriptTag('document.F=Object'));
  iframeDocument.close();
  return iframeDocument.F;
};

// Check for document.domain and active x support
// No need to use active x approach when document.domain is not set
// see https://github.com/es-shims/es5-shim/issues/150
// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
// avoid IE GC bug
var activeXDocument;
var NullProtoObject = function () {
  try {
    activeXDocument = new ActiveXObject('htmlfile');
  } catch (error) { /* ignore */ }
  NullProtoObject = typeof document != 'undefined'
    ? document.domain && activeXDocument
      ? NullProtoObjectViaActiveX(activeXDocument) // old IE
      : NullProtoObjectViaIFrame()
    : NullProtoObjectViaActiveX(activeXDocument); // WSH
  var length = enumBugKeys.length;
  while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];
  return NullProtoObject();
};

hiddenKeys[IE_PROTO] = true;

// `Object.create` method
// https://tc39.es/ecma262/#sec-object.create
// eslint-disable-next-line es/no-object-create -- safe
module.exports = Object.create || function create(O, Properties) {
  var result;
  if (O !== null) {
    EmptyConstructor[PROTOTYPE] = anObject(O);
    result = new EmptyConstructor();
    EmptyConstructor[PROTOTYPE] = null;
    // add "__proto__" for Object.getPrototypeOf polyfill
    result[IE_PROTO] = O;
  } else result = NullProtoObject();
  return Properties === undefined ? result : definePropertiesModule.f(result, Properties);
};

});

// MODULE: ./node_modules/core-js/internals/object-define-properties.js
var object_define_properties_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var V8_PROTOTYPE_DEFINE_BUG = (v8_prototype_define_bug_namespaceFn());
var definePropertyModule = (object_define_property_namespaceFn());
var anObject = (an_object_namespaceFn());
var toIndexedObject = (to_indexed_object_namespaceFn());
var objectKeys = (object_keys_namespaceFn());

// `Object.defineProperties` method
// https://tc39.es/ecma262/#sec-object.defineproperties
// eslint-disable-next-line es/no-object-defineproperties -- safe
exports.f = DESCRIPTORS && !V8_PROTOTYPE_DEFINE_BUG ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var props = toIndexedObject(Properties);
  var keys = objectKeys(Properties);
  var length = keys.length;
  var index = 0;
  var key;
  while (length > index) definePropertyModule.f(O, key = keys[index++], props[key]);
  return O;
};

});

// MODULE: ./node_modules/core-js/internals/object-define-property.js
var object_define_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var IE8_DOM_DEFINE = (ie8_dom_define_namespaceFn());
var V8_PROTOTYPE_DEFINE_BUG = (v8_prototype_define_bug_namespaceFn());
var anObject = (an_object_namespaceFn());
var toPropertyKey = (to_property_key_namespaceFn());

var $TypeError = TypeError;
// eslint-disable-next-line es/no-object-defineproperty -- safe
var $defineProperty = Object.defineProperty;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
var ENUMERABLE = 'enumerable';
var CONFIGURABLE = 'configurable';
var WRITABLE = 'writable';

// `Object.defineProperty` method
// https://tc39.es/ecma262/#sec-object.defineproperty
exports.f = DESCRIPTORS ? V8_PROTOTYPE_DEFINE_BUG ? function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPropertyKey(P);
  anObject(Attributes);
  if (typeof O === 'function' && P === 'prototype' && 'value' in Attributes && WRITABLE in Attributes && !Attributes[WRITABLE]) {
    var current = $getOwnPropertyDescriptor(O, P);
    if (current && current[WRITABLE]) {
      O[P] = Attributes.value;
      Attributes = {
        configurable: CONFIGURABLE in Attributes ? Attributes[CONFIGURABLE] : current[CONFIGURABLE],
        enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],
        writable: false
      };
    }
  } return $defineProperty(O, P, Attributes);
} : $defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPropertyKey(P);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return $defineProperty(O, P, Attributes);
  } catch (error) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw new $TypeError('Accessors not supported');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};

});

// MODULE: ./node_modules/core-js/internals/object-get-own-property-descriptor.js
var object_get_own_property_descriptor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var call = (function_call_namespaceFn());
var propertyIsEnumerableModule = (object_property_is_enumerable_namespaceFn());
var createPropertyDescriptor = (create_property_descriptor_namespaceFn());
var toIndexedObject = (to_indexed_object_namespaceFn());
var toPropertyKey = (to_property_key_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());
var IE8_DOM_DEFINE = (ie8_dom_define_namespaceFn());

// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// `Object.getOwnPropertyDescriptor` method
// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor
exports.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
  O = toIndexedObject(O);
  P = toPropertyKey(P);
  if (IE8_DOM_DEFINE) try {
    return $getOwnPropertyDescriptor(O, P);
  } catch (error) { /* empty */ }
  if (hasOwn(O, P)) return createPropertyDescriptor(!call(propertyIsEnumerableModule.f, O, P), O[P]);
};

});

// MODULE: ./node_modules/core-js/internals/object-get-own-property-names.js
var object_get_own_property_names_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var internalObjectKeys = (object_keys_internal_namespaceFn());
var enumBugKeys = (enum_bug_keys_namespaceFn());

var hiddenKeys = enumBugKeys.concat('length', 'prototype');

// `Object.getOwnPropertyNames` method
// https://tc39.es/ecma262/#sec-object.getownpropertynames
// eslint-disable-next-line es/no-object-getownpropertynames -- safe
exports.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
  return internalObjectKeys(O, hiddenKeys);
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/object-get-own-property-symbols.js
var object_get_own_property_symbols_namespaceFn = () => {
	return __webpack_require__(717);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/object-is-prototype-of.js
var object_is_prototype_of_namespaceFn = () => {
	return __webpack_require__(625);
};

// MODULE: ./node_modules/core-js/internals/object-keys-internal.js
var object_keys_internal_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var uncurryThis = (function_uncurry_this_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());
var toIndexedObject = (to_indexed_object_namespaceFn());
var indexOf = (array_includes_namespaceFn().indexOf);
var hiddenKeys = (hidden_keys_namespaceFn());

var push = uncurryThis([].push);

module.exports = function (object, names) {
  var O = toIndexedObject(object);
  var i = 0;
  var result = [];
  var key;
  for (key in O) !hasOwn(hiddenKeys, key) && hasOwn(O, key) && push(result, key);
  // Don't enum bug & hidden keys
  while (names.length > i) if (hasOwn(O, key = names[i++])) {
    ~indexOf(result, key) || push(result, key);
  }
  return result;
};

});

// MODULE: ./node_modules/core-js/internals/object-keys.js
var object_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var internalObjectKeys = (object_keys_internal_namespaceFn());
var enumBugKeys = (enum_bug_keys_namespaceFn());

// `Object.keys` method
// https://tc39.es/ecma262/#sec-object.keys
// eslint-disable-next-line es/no-object-keys -- safe
module.exports = Object.keys || function keys(O) {
  return internalObjectKeys(O, enumBugKeys);
};

});

// MODULE: ./node_modules/core-js/internals/object-property-is-enumerable.js
var object_property_is_enumerable_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var $propertyIsEnumerable = {}.propertyIsEnumerable;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// Nashorn ~ JDK8 bug
var NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({ 1: 2 }, 1);

// `Object.prototype.propertyIsEnumerable` method implementation
// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable
exports.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
  var descriptor = getOwnPropertyDescriptor(this, V);
  return !!descriptor && descriptor.enumerable;
} : $propertyIsEnumerable;

});

// MODULE: ./node_modules/core-js/internals/ordinary-to-primitive.js
var ordinary_to_primitive_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var call = (function_call_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var isObject = (is_object_namespaceFn());

var $TypeError = TypeError;

// `OrdinaryToPrimitive` abstract operation
// https://tc39.es/ecma262/#sec-ordinarytoprimitive
module.exports = function (input, pref) {
  var fn, val;
  if (pref === 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
  if (isCallable(fn = input.valueOf) && !isObject(val = call(fn, input))) return val;
  if (pref !== 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
  throw new $TypeError("Can't convert object to primitive value");
};

});

// MODULE: ./node_modules/core-js/internals/own-keys.js
var own_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var getBuiltIn = (get_built_in_namespaceFn());
var uncurryThis = (function_uncurry_this_namespaceFn());
var getOwnPropertyNamesModule = (object_get_own_property_names_namespaceFn());
var getOwnPropertySymbolsModule = (object_get_own_property_symbols_namespaceFn());
var anObject = (an_object_namespaceFn());

var concat = uncurryThis([].concat);

// all object keys, includes non-enumerable and symbols
module.exports = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {
  var keys = getOwnPropertyNamesModule.f(anObject(it));
  var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
  return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;
};

});

// MODULE: ./node_modules/core-js/internals/parse-json-string.js
var parse_json_string_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var uncurryThis = (function_uncurry_this_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());

var $SyntaxError = SyntaxError;
var $parseInt = parseInt;
var fromCharCode = String.fromCharCode;
var at = uncurryThis(''.charAt);
var slice = uncurryThis(''.slice);
var exec = uncurryThis(/./.exec);

var codePoints = {
  '\\"': '"',
  '\\\\': '\\',
  '\\/': '/',
  '\\b': '\b',
  '\\f': '\f',
  '\\n': '\n',
  '\\r': '\r',
  '\\t': '\t'
};

var IS_4_HEX_DIGITS = /^[\da-f]{4}$/i;
// eslint-disable-next-line regexp/no-control-character -- safe
var IS_C0_CONTROL_CODE = /^[\u0000-\u001F]$/;

module.exports = function (source, i) {
  var unterminated = true;
  var value = '';
  while (i < source.length) {
    var chr = at(source, i);
    if (chr === '\\') {
      var twoChars = slice(source, i, i + 2);
      if (hasOwn(codePoints, twoChars)) {
        value += codePoints[twoChars];
        i += 2;
      } else if (twoChars === '\\u') {
        i += 2;
        var fourHexDigits = slice(source, i, i + 4);
        if (!exec(IS_4_HEX_DIGITS, fourHexDigits)) throw new $SyntaxError('Bad Unicode escape at: ' + i);
        value += fromCharCode($parseInt(fourHexDigits, 16));
        i += 4;
      } else throw new $SyntaxError('Unknown escape sequence: "' + twoChars + '"');
    } else if (chr === '"') {
      unterminated = false;
      i++;
      break;
    } else {
      if (exec(IS_C0_CONTROL_CODE, chr)) throw new $SyntaxError('Bad control character in string literal at: ' + i);
      value += chr;
      i++;
    }
  }
  if (unterminated) throw new $SyntaxError('Unterminated string at: ' + i);
  return { value: value, end: i };
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/require-object-coercible.js
var require_object_coercible_namespaceFn = () => {
	return __webpack_require__(750);
};

// MODULE: ./node_modules/core-js/internals/shared-key.js
var shared_key_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var shared = (shared_namespaceFn());
var uid = (uid_namespaceFn());

var keys = shared('keys');

module.exports = function (key) {
  return keys[key] || (keys[key] = uid(key));
};

});

// MODULE: ./node_modules/core-js/internals/shared-store.js
var shared_store_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var IS_PURE = (is_pure_namespaceFn());
var globalThis = (global_this_namespaceFn());
var defineGlobalProperty = (define_global_property_namespaceFn());

var SHARED = '__core-js_shared__';
var store = module.exports = globalThis[SHARED] || defineGlobalProperty(SHARED, {});

(store.versions || (store.versions = [])).push({
  version: '3.50.0',
  mode: IS_PURE ? 'pure' : 'global',
  copyright: '© 2013–2025 Denis Pushkarev (zloirock.ru), 2025–2026 CoreJS Company (core-js.io). All rights reserved.',
  license: 'https://github.com/zloirock/core-js/blob/v3.50.0/LICENSE',
  source: 'https://github.com/zloirock/core-js'
});

});

// MODULE: ./node_modules/core-js/internals/shared.js
var shared_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var store = (shared_store_namespaceFn());
// eslint-disable-next-line es/no-object-create -- safe
var create = Object.create || Object;

module.exports = function (key, value) {
  return store[key] || (store[key] = value || create(null));
};

});

// MODULE: ./node_modules/core-js/internals/symbol-constructor-detection.js
var symbol_constructor_detection_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

/* eslint-disable es/no-symbol -- required for testing */
var V8_VERSION = (environment_v8_version_namespaceFn());
var fails = (fails_namespaceFn());
var globalThis = (global_this_namespaceFn());

var $String = globalThis.String;

// eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing
module.exports = !!Object.getOwnPropertySymbols && !fails(function () {
  var symbol = Symbol('symbol detection');
  // Chrome 38 Symbol has incorrect toString conversion
  // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances
  // nb: Do not call `String` directly to avoid this being optimized out to `symbol+''` which will,
  // of course, fail.
  return !$String(symbol) || !(Object(symbol) instanceof Symbol) ||
    // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
    !Symbol.sham && V8_VERSION && V8_VERSION < 41;
});

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/this-number-value.js
var this_number_value_namespaceFn = () => {
	return __webpack_require__(240);
};

// MODULE: ./node_modules/core-js/internals/to-absolute-index.js
var to_absolute_index_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var toIntegerOrInfinity = (to_integer_or_infinity_namespaceFn());

var max = Math.max;
var min = Math.min;

// Helper for a popular repeating case of the spec:
// Let integer be ? ToInteger(index).
// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).
module.exports = function (index, length) {
  var integer = toIntegerOrInfinity(index);
  return integer < 0 ? max(integer + length, 0) : min(integer, length);
};

});

// MODULE: ./node_modules/core-js/internals/to-indexed-object.js
var to_indexed_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

// toObject with fallback for non-array-like ES3 strings
var IndexedObject = (indexed_object_namespaceFn());
var requireObjectCoercible = (require_object_coercible_namespaceFn());

module.exports = function (it) {
  return IndexedObject(requireObjectCoercible(it));
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/to-integer-or-infinity.js
var to_integer_or_infinity_namespaceFn = () => {
	return __webpack_require__(291);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/to-length.js
var to_length_namespaceFn = () => {
	return __webpack_require__(14);
};

// EXTERNAL MODULE: ./node_modules/core-js/internals/to-object.js
var to_object_namespaceFn = () => {
	return __webpack_require__(981);
};

// MODULE: ./node_modules/core-js/internals/to-primitive.js
var to_primitive_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var call = (function_call_namespaceFn());
var isObject = (is_object_namespaceFn());
var isSymbol = (is_symbol_namespaceFn());
var getMethod = (get_method_namespaceFn());
var ordinaryToPrimitive = (ordinary_to_primitive_namespaceFn());
var wellKnownSymbol = (well_known_symbol_namespaceFn());

var $TypeError = TypeError;
var TO_PRIMITIVE = wellKnownSymbol('toPrimitive');

// `ToPrimitive` abstract operation
// https://tc39.es/ecma262/#sec-toprimitive
module.exports = function (input, pref) {
  if (!isObject(input) || isSymbol(input)) return input;
  var exoticToPrim = getMethod(input, TO_PRIMITIVE);
  var result;
  if (exoticToPrim) {
    if (pref === undefined) pref = 'default';
    result = call(exoticToPrim, input, pref);
    if (!isObject(result) || isSymbol(result)) return result;
    throw new $TypeError("Can't convert object to primitive value");
  }
  if (pref === undefined) pref = 'number';
  return ordinaryToPrimitive(input, pref);
};

});

// MODULE: ./node_modules/core-js/internals/to-property-key.js
var to_property_key_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var toPrimitive = (to_primitive_namespaceFn());
var isSymbol = (is_symbol_namespaceFn());

// `ToPropertyKey` abstract operation
// https://tc39.es/ecma262/#sec-topropertykey
module.exports = function (argument) {
  var key = toPrimitive(argument, 'string');
  return isSymbol(key) ? key : key + '';
};

});

// MODULE: ./node_modules/core-js/internals/to-string-tag-support.js
var to_string_tag_support_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var wellKnownSymbol = (well_known_symbol_namespaceFn());

var TO_STRING_TAG = wellKnownSymbol('toStringTag');
var test = {};
// eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation
test[TO_STRING_TAG] = 'z';

module.exports = String(test) === '[object z]';

});

// MODULE: ./node_modules/core-js/internals/to-string.js
var to_string_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var classof = (classof_namespaceFn());

var $String = String;

module.exports = function (argument) {
  if (classof(argument) === 'Symbol') throw new TypeError('Cannot convert a Symbol value to a string');
  return $String(argument);
};

});

// EXTERNAL MODULE: ./node_modules/core-js/internals/uid.js
var uid_namespaceFn = () => {
	return __webpack_require__(392);
};

// MODULE: ./node_modules/core-js/internals/use-symbol-as-uid.js
var use_symbol_as_uid_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

/* eslint-disable es/no-symbol -- required for testing */
var NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());

module.exports = NATIVE_SYMBOL &&
  !Symbol.sham &&
  typeof Symbol.iterator == 'symbol';

});

// MODULE: ./node_modules/core-js/internals/v8-prototype-define-bug.js
var v8_prototype_define_bug_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var DESCRIPTORS = (descriptors_namespaceFn());
var fails = (fails_namespaceFn());

// V8 ~ Chrome 36-
// https://bugs.chromium.org/p/v8/issues/detail?id=3334
module.exports = DESCRIPTORS && fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty(function () { /* empty */ }, 'prototype', {
    value: 42,
    writable: false
  }).prototype !== 42;
});

});

// MODULE: ./node_modules/core-js/internals/weak-map-basic-detection.js
var weak_map_basic_detection_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var isCallable = (is_callable_namespaceFn());

var WeakMap = globalThis.WeakMap;

module.exports = isCallable(WeakMap) && /native code/.test(String(WeakMap));

});

// MODULE: ./node_modules/core-js/internals/well-known-symbol.js
var well_known_symbol_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var globalThis = (global_this_namespaceFn());
var shared = (shared_namespaceFn());
var hasOwn = (has_own_property_namespaceFn());
var uid = (uid_namespaceFn());
var NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());
var USE_SYMBOL_AS_UID = (use_symbol_as_uid_namespaceFn());

var Symbol = globalThis.Symbol;
var WellKnownSymbolsStore = shared('wks');
var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol['for'] || Symbol : Symbol && Symbol.withoutSetter || uid;

module.exports = function (name) {
  if (!hasOwn(WellKnownSymbolsStore, name)) {
    WellKnownSymbolsStore[name] = NATIVE_SYMBOL && hasOwn(Symbol, name)
      ? Symbol[name]
      : createWellKnownSymbol('Symbol.' + name);
  } return WellKnownSymbolsStore[name];
};

});

// MODULE: ./node_modules/core-js/modules/es.array.push.js
var es_array_push_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var $ = (export_namespaceFn());
var toObject = (to_object_namespaceFn());
var lengthOfArrayLike = (length_of_array_like_namespaceFn());
var setArrayLength = (array_set_length_namespaceFn());
var doesNotExceedSafeInteger = (does_not_exceed_safe_integer_namespaceFn());
var fails = (fails_namespaceFn());

var INCORRECT_TO_LENGTH = fails(function () {
  return [].push.call({ length: 0x100000000 }, 1) !== 4294967297;
});

// V8 <= 121 and Safari <= 15.4; FF < 23 throws InternalError
// https://bugs.chromium.org/p/v8/issues/detail?id=12681
var properErrorOnNonWritableLength = function () {
  try {
    // eslint-disable-next-line es/no-object-defineproperty -- safe
    Object.defineProperty([], 'length', { writable: false }).push();
  } catch (error) {
    return error instanceof TypeError;
  }
};

var FORCED = INCORRECT_TO_LENGTH || !properErrorOnNonWritableLength();

// `Array.prototype.push` method
// https://tc39.es/ecma262/#sec-array.prototype.push
$({ target: 'Array', proto: true, arity: 1, forced: FORCED }, {
  // eslint-disable-next-line no-unused-vars -- required for `.length`
  push: function push(item) {
    var O = toObject(this);
    var len = lengthOfArrayLike(O);
    var argCount = arguments.length;
    doesNotExceedSafeInteger(len + argCount);
    for (var i = 0; i < argCount; i++) {
      O[len] = arguments[i];
      len++;
    }
    setArrayLength(O, len);
    return len;
  }
});

});

// MODULE: ./node_modules/core-js/modules/es.json.stringify.js
var es_json_stringify_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var $ = (export_namespaceFn());
var getBuiltIn = (get_built_in_namespaceFn());
var call = (function_call_namespaceFn());
var uncurryThis = (function_uncurry_this_namespaceFn());
var fails = (fails_namespaceFn());
var isArray = (is_array_namespaceFn());
var isCallable = (is_callable_namespaceFn());
var isObject = (is_object_namespaceFn());
var create = (object_create_namespaceFn());
var isRawJSON = (is_raw_json_namespaceFn());
var isSymbol = (is_symbol_namespaceFn());
var classof = (classof_raw_namespaceFn());
var thisNumberValue = (this_number_value_namespaceFn());
var includes = (array_includes_namespaceFn().includes);
var hasOwn = (has_own_property_namespaceFn());
var toString = (to_string_namespaceFn());
var parseJSONString = (parse_json_string_namespaceFn());
var uid = (uid_namespaceFn());
var NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());
var NATIVE_RAW_JSON = (native_raw_json_namespaceFn());

var $String = String;
var $TypeError = TypeError;
var $stringify = getBuiltIn('JSON', 'stringify');
var $BigInt = getBuiltIn('BigInt');
var stringValueOf = uncurryThis(''.valueOf);
var booleanValueOf = uncurryThis(true.valueOf);
var bigIntValueOf = $BigInt && uncurryThis($BigInt.prototype.valueOf);
var exec = uncurryThis(/./.exec);
var charAt = uncurryThis(''.charAt);
var charCodeAt = uncurryThis(''.charCodeAt);
var replace = uncurryThis(''.replace);
var slice = uncurryThis(''.slice);
var push = uncurryThis([].push);
var pop = uncurryThis([].pop);
var numberToString = uncurryThis(1.1.toString);

var surrogates = /[\uD800-\uDFFF]/g;
var leadingSurrogates = /^[\uD800-\uDBFF]$/;
var trailingSurrogates = /^[\uDC00-\uDFFF]$/;
var digits = /^\d+$/;

// a placeholder of a raw JSON value
var RAW_MARK = uid();
// a prefix of keys of a reordered object, see `createOrderedObject`
var KEY_MARK = uid();
// the last key of a reordered object, marks the end of its serialization
var END_MARK = uid();
var RAW_MARK_LENGTH = RAW_MARK.length;
var KEY_MARK_LENGTH = KEY_MARK.length;

var WRONG_SYMBOLS_CONVERSION = !NATIVE_SYMBOL || fails(function () {
  var symbol = getBuiltIn('Symbol')('stringify detection');
  // MS Edge converts symbol values to JSON as {}
  return $stringify([symbol]) !== '[null]'
    // WebKit converts symbol values to JSON as null
    || $stringify({ a: symbol }) !== '{}'
    // V8 throws on boxed symbols
    || $stringify(Object(symbol)) !== '{}';
});

// https://github.com/tc39/proposal-well-formed-stringify
var ILL_FORMED_UNICODE = fails(function () {
  return $stringify('\uDF06\uD834') !== '"\\udf06\\ud834"'
    || $stringify('\uDEAD') !== '"\\udead"';
});

var isRawJSONValue = NATIVE_RAW_JSON ? getBuiltIn('JSON', 'isRawJSON') : isRawJSON;

var stringifyWithProperSymbolsConversion = WRONG_SYMBOLS_CONVERSION ? function (it, replacer, space) {
  return $stringify(it, function (key, value) {
    var replaced = call(replacer, this, key, value);
    if (!isSymbol(replaced)) return replaced;
  }, space);
} : $stringify;

var fixIllFormedJSON = function (match, offset, string) {
  var prev = charAt(string, offset - 1);
  var next = charAt(string, offset + 1);
  if (
    (exec(leadingSurrogates, match) && !exec(trailingSurrogates, next)) ||
    (exec(trailingSurrogates, match) && !exec(leadingSurrogates, prev))
  ) {
    return '\\u' + numberToString(charCodeAt(match, 0), 16);
  } return match;
};

// `PropertyList` of `JSON.stringify`
// https://tc39.es/ecma262/#sec-json.stringify
var getPropertyList = function (replacer) {
  if (!isArray(replacer)) return;
  var rawLength = replacer.length;
  var propertyList = [];
  // a null prototype object is used as a set of already added keys to keep the deduplication linear
  var addedKeys = create(null);
  for (var i = 0; i < rawLength; i++) {
    var element = replacer[i];
    var key;
    if (typeof element == 'string') key = element;
    else if (typeof element == 'number' || classof(element) === 'Number' || classof(element) === 'String') key = toString(element);
    else continue;
    if (!hasOwn(addedKeys, key)) {
      addedKeys[key] = true;
      push(propertyList, key);
    }
  }
  return propertyList;
};

// values with such an internal slot are unwrapped by `SerializeJSONProperty` instead of being serialized as objects
var hasInternalSlot = function (valueOf, it) {
  try {
    valueOf(it);
    return true;
  } catch (error) {
    return false;
  }
};

// the slot check is expensive, so it's performed only for the kind reported by the value itself -
// a value lying about its kind via `Symbol.toStringTag` is serialized as an ordinary object
var isBoxedPrimitive = function (it) {
  var kind = classof(it);
  return (kind === 'Number' && hasInternalSlot(thisNumberValue, it))
    || (kind === 'String' && hasInternalSlot(stringValueOf, it))
    || (kind === 'Boolean' && hasInternalSlot(booleanValueOf, it))
    || (!!bigIntValueOf && kind === 'BigInt' && hasInternalSlot(bigIntValueOf, it));
};

// only objects serialized by `SerializeJSONObject` are affected by the property list
var isSerializedAsObject = function (it) {
  if (!isObject(it) || isCallable(it) || isArray(it)) return false;
  try {
    return !isBoxedPrimitive(it);
  // `classof` reads `Symbol.toStringTag`, so a proxy could throw - it has no internal slots anyway
  } catch (error) {
    return true;
  }
};

// the engine unwraps it in the same order as it would read the original property,
// so the property is read lazily and `toJSON` is called once and with the original key
var createElementHolder = function (holder, key) {
  return {
    toJSON: function () {
      var element = holder[key];
      if (isObject(element) || typeof element == 'bigint') {
        var elementToJSON = element.toJSON;
        if (isCallable(elementToJSON)) element = call(elementToJSON, element, key);
      } return element;
    }
  };
};

// own keys of objects are sorted - integer-like keys are moved to the beginning,
// so such keys should be marked and restored in the serialized string
var getKeyPrefix = function (propertyList) {
  for (var i = 0, length = propertyList.length; i < length; i++) {
    if (exec(digits, propertyList[i])) return KEY_MARK;
  } return '';
};

// `SerializeJSONObject` iterates the property list, so the value is replaced with an object with keys in this order
var createOrderedObject = function (value, propertyList, keyPrefix) {
  // keys are not marked if the property list has no integer-like keys, so `Object.prototype`
  // with a setter, a non-writable property or `__proto__` should not intercept the assignment
  var ordered = create(null);
  for (var i = 0, length = propertyList.length; i < length; i++) {
    var key = propertyList[i];
    ordered[keyPrefix + key] = createElementHolder(value, key);
  }
  ordered[END_MARK] = null;
  return ordered;
};

// `JSON.stringify` method
// https://tc39.es/ecma262/#sec-json.stringify
// https://github.com/tc39/proposal-json-parse-with-source
if ($stringify) $({ target: 'JSON', stat: true, arity: 3, forced: WRONG_SYMBOLS_CONVERSION || ILL_FORMED_UNICODE || !NATIVE_RAW_JSON }, {
  stringify: function stringify(text, replacer, space) {
    var replacerFunction = isCallable(replacer) ? replacer : undefined;
    var propertyList = replacerFunction ? undefined : getPropertyList(replacer);
    var keyPrefix = propertyList && getKeyPrefix(propertyList);
    var rawStrings = [];
    var openObjects = [];
    var parentOrdered = [];
    var currentOrdered;
    var marked = false;
    var root = true;

    var json = stringifyWithProperSymbolsConversion(text, function (key, value) {
      // some old implementations (like WebKit) could pass numbers as keys
      key = $String(key);

      if (propertyList) {
        if (key === END_MARK) {
          pop(openObjects);
          currentOrdered = pop(parentOrdered);
          return;
        }
        if (root) root = false;
        // the innermost reordered object already contains only keys of the property list and arrays are not
        // affected by it, the rest of objects (like objects with a fake `Symbol.toStringTag`) are filtered here
        else if (this !== currentOrdered && !isArray(this) && !includes(propertyList, key)) return;
      } else if (replacerFunction) value = call(replacerFunction, this, key, value);

      if (isRawJSONValue(value)) {
        if (NATIVE_RAW_JSON) return value;
        marked = true;
        return RAW_MARK + (push(rawStrings, value.rawJSON) - 1);
      }

      if (propertyList && isSerializedAsObject(value)) {
        // reordered objects are new each time, so cycles should be detected before the engine does it
        if (includes(openObjects, value)) throw new $TypeError('Converting circular structure to JSON');
        var ordered = createOrderedObject(value, propertyList, keyPrefix);
        push(openObjects, value);
        push(parentOrdered, currentOrdered);
        currentOrdered = ordered;
        if (keyPrefix) marked = true;
        return ordered;
      }

      return value;
    }, space);

    if (typeof json != 'string') return json;

    if (ILL_FORMED_UNICODE) json = replace(json, surrogates, fixIllFormedJSON);

    if (!marked) return json;

    var result = '';
    var length = json.length;

    for (var i = 0; i < length; i++) {
      var chr = charAt(json, i);
      if (chr === '"') {
        var end = parseJSONString(json, ++i).end - 1;
        var string = slice(json, i, end);
        if (slice(string, 0, RAW_MARK_LENGTH) === RAW_MARK) result += rawStrings[slice(string, RAW_MARK_LENGTH)];
        else if (slice(string, 0, KEY_MARK_LENGTH) === KEY_MARK) result += '"' + slice(string, KEY_MARK_LENGTH) + '"';
        else result += '"' + string + '"';
        i = end;
      } else result += chr;
    }

    return result;
  }
});

});

// MODULE: ./node_modules/core-js/modules/es.map.get-or-insert-computed.js
var es_map_get_or_insert_computed_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var $ = (export_namespaceFn());
var aCallable = (a_callable_namespaceFn());
var MapHelpers = (map_helpers_namespaceFn());
var IS_PURE = (is_pure_namespaceFn());

var get = MapHelpers.get;
var has = MapHelpers.has;
var set = MapHelpers.set;

// `Map.prototype.getOrInsertComputed` method
// https://tc39.es/ecma262/#sec-map.prototype.getorinsertcomputed
$({ target: 'Map', proto: true, real: true, forced: IS_PURE }, {
  getOrInsertComputed: function getOrInsertComputed(key, callbackfn) {
    var hasKey = has(this, key);
    aCallable(callbackfn);
    if (hasKey) return get(this, key);
    // CanonicalizeKeyedCollectionKey
    if (key === 0 && 1 / key === -Infinity) key = 0;
    var value = callbackfn(key);
    set(this, key, value);
    return value;
  }
});

});

// MODULE: ./node_modules/core-js/modules/es.map.get-or-insert.js
var es_map_get_or_insert_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {

var $ = (export_namespaceFn());
var MapHelpers = (map_helpers_namespaceFn());
var IS_PURE = (is_pure_namespaceFn());

var get = MapHelpers.get;
var has = MapHelpers.has;
var set = MapHelpers.set;

// `Map.prototype.getOrInsert` method
// https://tc39.es/ecma262/#sec-map.prototype.getorinsert
$({ target: 'Map', proto: true, real: true, forced: IS_PURE }, {
  getOrInsert: function getOrInsert(key, value) {
    if (has(this, key)) return get(this, key);
    set(this, key, value);
    return value;
  }
});

});

;// ./node_modules/core-js/modules/es.array.push.js
es_array_push_namespaceFn();

;// ./node_modules/core-js/modules/es.json.stringify.js
es_json_stringify_namespaceFn();

;// ./node_modules/core-js/modules/es.map.get-or-insert.js
es_map_get_or_insert_namespaceFn();

;// ./node_modules/core-js/modules/es.map.get-or-insert-computed.js
es_map_get_or_insert_computed_namespaceFn();

;// ./src/pdf.sandbox.external.js


class SandboxSupportBase {
  constructor(win) {
    this.win = win;
    this.timeoutIds = new Map();
    this.commFun = null;
  }
  destroy() {
    this.commFun = null;
    for (const id of this.timeoutIds.values()) {
      this.win.clearTimeout(id);
    }
    this.timeoutIds = null;
  }
  exportValueToSandbox(val) {
    throw new Error("Not implemented");
  }
  importValueFromSandbox(val) {
    throw new Error("Not implemented");
  }
  createErrorForSandbox(errorMessage) {
    throw new Error("Not implemented");
  }
  callSandboxFunction(name, args) {
    if (!this.commFun) {
      return;
    }
    try {
      args = this.exportValueToSandbox(args);
      this.commFun(name, args);
    } catch (e) {
      this.win.console.error(e);
    }
  }
  createSandboxExternals() {
    const externals = {
      setTimeout: (callbackId, nMilliseconds) => {
        if (typeof callbackId !== "number" || typeof nMilliseconds !== "number") {
          return;
        }
        if (callbackId === 0) {
          this.win.clearTimeout(this.timeoutIds.get(callbackId));
        }
        const id = this.win.setTimeout(() => {
          this.timeoutIds.delete(callbackId);
          this.callSandboxFunction("timeoutCb", {
            callbackId,
            interval: false
          });
        }, nMilliseconds);
        this.timeoutIds.set(callbackId, id);
      },
      clearTimeout: callbackId => {
        this.win.clearTimeout(this.timeoutIds.get(callbackId));
        this.timeoutIds.delete(callbackId);
      },
      setInterval: (callbackId, nMilliseconds) => {
        if (typeof callbackId !== "number" || typeof nMilliseconds !== "number") {
          return;
        }
        const id = this.win.setInterval(() => {
          this.callSandboxFunction("timeoutCb", {
            callbackId,
            interval: true
          });
        }, nMilliseconds);
        this.timeoutIds.set(callbackId, id);
      },
      clearInterval: callbackId => {
        this.win.clearInterval(this.timeoutIds.get(callbackId));
        this.timeoutIds.delete(callbackId);
      },
      alert: cMsg => {
        if (typeof cMsg !== "string") {
          return;
        }
        this.win.alert(cMsg);
      },
      confirm: cMsg => typeof cMsg !== "string" ? false : this.win.confirm(cMsg),
      prompt: (cQuestion, cDefault) => typeof cQuestion !== "string" || typeof cDefault !== "string" ? null : this.win.prompt(cQuestion, cDefault),
      parseURL: cUrl => {
        const url = new this.win.URL(cUrl);
        const props = ["hash", "host", "hostname", "href", "origin", "password", "pathname", "port", "protocol", "search", "searchParams", "username"];
        return Object.fromEntries(props.map(name => [name, url[name].toString()]));
      },
      send: data => {
        if (!data) {
          return;
        }
        const event = new this.win.CustomEvent("updatefromsandbox", {
          detail: this.importValueFromSandbox(data)
        });
        this.win.dispatchEvent(event);
      }
    };
    Object.setPrototypeOf(externals, null);
    return (name, args) => {
      try {
        const result = externals[name](...args);
        return this.exportValueToSandbox(result);
      } catch (error) {
        throw this.createErrorForSandbox(error?.toString() ?? "");
      }
    };
  }
}
;// ./src/pdf.sandbox.js





class SandboxSupport extends SandboxSupportBase {
  exportValueToSandbox(val) {
    return JSON.stringify(val, (k, v) => v instanceof Map ? Object.fromEntries(v) : v);
  }
  importValueFromSandbox(val) {
    return val;
  }
  createErrorForSandbox(errorMessage) {
    return new Error(errorMessage);
  }
}
class Sandbox {
  constructor(win, module) {
    this.support = new SandboxSupport(win, this);
    module.externalCall = this.support.createSandboxExternals();
    this._module = module;
    this._alertOnError = 0;
  }
  create(data) {
    const code = ["/******/ var __webpack_modules__ = ({\n\n/***/ 529\n(module) {\n\n\n// `CreateIterResultObject` abstract operation\n// https://tc39.es/ecma262/#sec-createiterresultobject\nmodule.exports = function (value, done) {\n  return { value: value, done: done };\n};\n\n\n/***/ },\n\n/***/ 837\n(module) {\n\n\nvar $TypeError = TypeError;\nvar MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF; // 2 ** 53 - 1 == 9007199254740991\n\nmodule.exports = function (it) {\n  if (it > MAX_SAFE_INTEGER) throw new $TypeError('Maximum allowed index exceeded');\n  return it;\n};\n\n\n/***/ },\n\n/***/ 767\n(module) {\n\n\n// `GetIteratorDirect(obj)` abstract operation\n// https://tc39.es/ecma262/#sec-getiteratordirect\nmodule.exports = function (obj) {\n  return {\n    iterator: obj,\n    next: obj.next,\n    done: false\n  };\n};\n\n\n/***/ },\n\n/***/ 117\n(module) {\n\n\n// we can't use just `it == null` since of `document.all` special case\n// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot-aec\nmodule.exports = function (it) {\n  return it === null || it === undefined;\n};\n\n\n/***/ },\n\n/***/ 859\n(module) {\n\n\n// release references held by exhausted / closed iterator helpers to allow GC of the source chain\nmodule.exports = function (state) {\n  state.iterator = state.next = state.nextHandler = state.mapper = state.predicate = state.inner =\n    state.iterables = state.iters = state.openIters = state.padding = state.finishResults = state.buffer = null;\n};\n\n\n/***/ },\n\n/***/ 684\n(module) {\n\n\n// Should throw an error on invalid iterator\n// https://issues.chromium.org/issues/336839115\nmodule.exports = function (methodName, argument) {\n  // eslint-disable-next-line es/no-iterator -- required for testing\n  var method = typeof Iterator == 'function' && Iterator.prototype[methodName];\n  if (method) try {\n    method.call({ next: null }, argument).next();\n  } catch (error) {\n    return true;\n  }\n};\n\n\n/***/ },\n\n/***/ 269\n(module) {\n\n\nmodule.exports = Object.create ? Object.create(null) : {};\n\n\n/***/ },\n\n/***/ 741\n(module) {\n\n\nvar ceil = Math.ceil;\nvar floor = Math.floor;\n\n// `Math.trunc` method\n// https://tc39.es/ecma262/#sec-math.trunc\n// eslint-disable-next-line es/no-math-trunc -- safe\nmodule.exports = Math.trunc || function trunc(x) {\n  var n = +x;\n  return (n > 0 ? floor : ceil)(n);\n};\n\n\n/***/ },\n\n/***/ 717\n(__unused_webpack_module, exports) {\n\n\n// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe\nexports.f = Object.getOwnPropertySymbols;\n\n\n/***/ },\n\n/***/ 750\n(module, __unused_webpack_exports, __webpack_require__) {\n\n\nvar isNullOrUndefined = __webpack_require__(117);\n\nvar $TypeError = TypeError;\n\n// `RequireObjectCoercible` abstract operation\n// https://tc39.es/ecma262/#sec-requireobjectcoercible\nmodule.exports = function (it) {\n  if (isNullOrUndefined(it)) throw new $TypeError(\"Can't call method on \" + it);\n  return it;\n};\n\n\n/***/ },\n\n/***/ 835\n(module) {\n\n\n// Should get iterator record of a set-like object before cloning this\n// https://bugs.webkit.org/show_bug.cgi?id=289430\nmodule.exports = function (METHOD_NAME) {\n  try {\n    // eslint-disable-next-line es/no-set -- needed for test\n    var baseSet = new Set();\n    var setLike = {\n      size: 0,\n      has: function () { return true; },\n      keys: function () {\n        // eslint-disable-next-line es/no-object-defineproperty -- needed for test\n        return Object.defineProperty({}, 'next', {\n          get: function () {\n            baseSet.clear();\n            baseSet.add(4);\n            return function () {\n              return { done: true };\n            };\n          }\n        });\n      }\n    };\n    var result = baseSet[METHOD_NAME](setLike);\n\n    return result.size === 1 && result.values().next().value === 4;\n  } catch (error) {\n    return false;\n  }\n};\n\n\n/***/ },\n\n/***/ 291\n(module, __unused_webpack_exports, __webpack_require__) {\n\n\nvar trunc = __webpack_require__(741);\n\n// `ToIntegerOrInfinity` abstract operation\n// https://tc39.es/ecma262/#sec-tointegerorinfinity\nmodule.exports = function (argument) {\n  var number = +argument;\n  // eslint-disable-next-line no-self-compare -- NaN check\n  return number !== number || number === 0 ? 0 : trunc(number);\n};\n\n\n/***/ },\n\n/***/ 14\n(module, __unused_webpack_exports, __webpack_require__) {\n\n\nvar toIntegerOrInfinity = __webpack_require__(291);\n\nvar min = Math.min;\n\n// `ToLength` abstract operation\n// https://tc39.es/ecma262/#sec-tolength\nmodule.exports = function (argument) {\n  var len = toIntegerOrInfinity(argument);\n  return len > 0 ? min(len, 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991\n};\n\n\n/***/ },\n\n/***/ 981\n(module, __unused_webpack_exports, __webpack_require__) {\n\n\nvar requireObjectCoercible = __webpack_require__(750);\n\nvar $Object = Object;\n\n// `ToObject` abstract operation\n// https://tc39.es/ecma262/#sec-toobject\nmodule.exports = function (argument) {\n  return $Object(requireObjectCoercible(argument));\n};\n\n\n/***/ },\n\n/***/ 823\n(module) {\n\n\nvar $String = String;\n\nmodule.exports = function (argument) {\n  try {\n    return $String(argument);\n  } catch (error) {\n    return 'Object';\n  }\n};\n\n\n/***/ }\n\n/******/ });\n/************************************************************************/\n/******/ // The module cache\n/******/ const __webpack_module_cache__ = {};\n/******/ \n/******/ // The require function\n/******/ function __webpack_require__(moduleId) {\n/******/ \t// Check if module is in cache\n/******/ \tconst cachedModule = __webpack_module_cache__[moduleId];\n/******/ \tif (cachedModule !== undefined) {\n/******/ \t\treturn cachedModule.exports;\n/******/ \t}\n/******/ \t// Create a new module (and put it into the cache)\n/******/ \tconst module = __webpack_module_cache__[moduleId] = {\n/******/ \t\t// no module.id needed\n/******/ \t\t// no module.loaded needed\n/******/ \t\texports: {}\n/******/ \t};\n/******/ \n/******/ \t// Execute the module function\n/******/ \t__webpack_modules__[moduleId](module, module.exports, __webpack_require__);\n/******/ \n/******/ \t// Return the exports of the module\n/******/ \treturn module.exports;\n/******/ }\n/******/ \n/************************************************************************/\n/******/ /* webpack/runtime/concatenation wrap */\n/******/ // wrap a concatenated module body as a lazy, memoized accessor; mod is\n/******/ // set before the body runs so re-entrant calls (require cycles) observe\n/******/ // the partial exports like Node.js\n/******/ __webpack_require__.cw = (body) => {\n/******/ \tvar mod;\n/******/ \treturn () => {\n/******/ \t\tif (body) {\n/******/ \t\t\tvar fn = body;\n/******/ \t\t\tbody = 0;\n/******/ \t\t\tmod = { exports: {} };\n/******/ \t\t\tfn.call(mod.exports, mod, mod.exports);\n/******/ \t\t}\n/******/ \t\treturn mod.exports;\n/******/ \t};\n/******/ };\n/******/ \n/************************************************************************/\n\n// MODULE: ./node_modules/core-js/internals/a-callable.js\nvar a_callable_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isCallable = (is_callable_namespaceFn());\nvar tryToString = (try_to_string_namespaceFn());\n\nvar $TypeError = TypeError;\n\n// `Assert: IsCallable(argument) is true`\nmodule.exports = function (argument) {\n  if (isCallable(argument)) return argument;\n  throw new $TypeError(tryToString(argument) + ' is not a function');\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/a-set.js\nvar a_set_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar has = (set_helpers_namespaceFn().has);\n\n// Perform ? RequireInternalSlot(M, [[SetData]])\nmodule.exports = function (it) {\n  has(it);\n  return it;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/a-weak-key.js\nvar a_weak_key_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar WeakMapHelpers = (weak_map_helpers_namespaceFn());\n\nvar weakmap = new WeakMapHelpers.WeakMap();\nvar set = WeakMapHelpers.set;\nvar remove = WeakMapHelpers.remove;\n\nmodule.exports = function (key) {\n  set(weakmap, key, 1);\n  remove(weakmap, key);\n  return key;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/a-weak-map.js\nvar a_weak_map_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar has = (weak_map_helpers_namespaceFn().has);\n\n// Perform ? RequireInternalSlot(M, [[WeakMapData]])\nmodule.exports = function (it) {\n  has(it);\n  return it;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/add-to-unscopables.js\nvar add_to_unscopables_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\nvar create = (object_create_namespaceFn());\nvar defineProperty = (object_define_property_namespaceFn().f);\n\nvar UNSCOPABLES = wellKnownSymbol('unscopables');\nvar ArrayPrototype = Array.prototype;\n\n// Array.prototype[@@unscopables]\n// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables\nif (ArrayPrototype[UNSCOPABLES] === undefined) {\n  defineProperty(ArrayPrototype, UNSCOPABLES, {\n    configurable: true,\n    value: create(null)\n  });\n}\n\n// add a key to Array.prototype[@@unscopables]\nmodule.exports = function (key) {\n  ArrayPrototype[UNSCOPABLES][key] = true;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/an-instance.js\nvar an_instance_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isPrototypeOf = (object_is_prototype_of_namespaceFn());\n\nvar $TypeError = TypeError;\n\nmodule.exports = function (it, Prototype) {\n  if (isPrototypeOf(Prototype, it)) return it;\n  throw new $TypeError('Incorrect invocation');\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/an-object.js\nvar an_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isObject = (is_object_namespaceFn());\n\nvar $String = String;\nvar $TypeError = TypeError;\n\n// `Assert: Type(argument) is Object`\nmodule.exports = function (argument) {\n  if (isObject(argument)) return argument;\n  throw new $TypeError($String(argument) + ' is not an object');\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/array-includes.js\nvar array_includes_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar toIndexedObject = (to_indexed_object_namespaceFn());\nvar toAbsoluteIndex = (to_absolute_index_namespaceFn());\nvar lengthOfArrayLike = (length_of_array_like_namespaceFn());\n\n// `Array.prototype.{ indexOf, includes }` methods implementation\nvar createMethod = function (IS_INCLUDES) {\n  return function ($this, el, fromIndex) {\n    var O = toIndexedObject($this);\n    var length = lengthOfArrayLike(O);\n    if (length === 0) return !IS_INCLUDES && -1;\n    var index = toAbsoluteIndex(fromIndex, length);\n    var value;\n    // Array#includes uses SameValueZero equality algorithm\n    // eslint-disable-next-line no-self-compare -- NaN check\n    if (IS_INCLUDES && el !== el) while (length > index) {\n      value = O[index++];\n      // eslint-disable-next-line no-self-compare -- NaN check\n      if (value !== value) return true;\n    // Array#indexOf ignores holes, Array#includes - not\n    } else for (;length > index; index++) {\n      if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;\n    } return !IS_INCLUDES && -1;\n  };\n};\n\nmodule.exports = {\n  // `Array.prototype.includes` method\n  // https://tc39.es/ecma262/#sec-array.prototype.includes\n  includes: createMethod(true),\n  // `Array.prototype.indexOf` method\n  // https://tc39.es/ecma262/#sec-array.prototype.indexof\n  indexOf: createMethod(false)\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/array-set-length.js\nvar array_set_length_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar isArray = (is_array_namespaceFn());\n\nvar $TypeError = TypeError;\n// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\nvar getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;\n\n// Safari < 13 does not throw an error in this case\nvar SILENT_ON_NON_WRITABLE_LENGTH_SET = DESCRIPTORS && !function () {\n  // makes no sense without proper strict mode support\n  if (this !== undefined) return true;\n  try {\n    // eslint-disable-next-line es/no-object-defineproperty -- safe\n    Object.defineProperty([], 'length', { writable: false }).length = 1;\n  } catch (error) {\n    return error instanceof TypeError;\n  }\n}();\n\nmodule.exports = SILENT_ON_NON_WRITABLE_LENGTH_SET ? function (O, length) {\n  if (isArray(O) && !getOwnPropertyDescriptor(O, 'length').writable) {\n    throw new $TypeError('Cannot set read only .length');\n  } return O.length = length;\n} : function (O, length) {\n  return O.length = length;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/call-with-safe-iteration-closing.js\nvar call_with_safe_iteration_closing_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar anObject = (an_object_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\n\n// call something on iterator step with safe closing on error\nmodule.exports = function (iterator, fn, value, ENTRIES) {\n  try {\n    return ENTRIES ? fn(anObject(value)[0], value[1]) : fn(value);\n  } catch (error) {\n    iteratorClose(iterator, 'throw', error);\n  }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/classof-raw.js\nvar classof_raw_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\nvar toString = uncurryThis({}.toString);\nvar stringSlice = uncurryThis(''.slice);\n\nmodule.exports = function (it) {\n  return stringSlice(toString(it), 8, -1);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/classof.js\nvar classof_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar TO_STRING_TAG_SUPPORT = (to_string_tag_support_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar classofRaw = (classof_raw_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\n\nvar TO_STRING_TAG = wellKnownSymbol('toStringTag');\nvar $Object = Object;\n\n// ES3 wrong here\nvar CORRECT_ARGUMENTS = classofRaw(function () { return arguments; }()) === 'Arguments';\n\n// fallback for IE11 Script Access Denied error\nvar tryGet = function (it, key) {\n  try {\n    return it[key];\n  } catch (error) { /* empty */ }\n};\n\n// getting tag from ES6+ `Object.prototype.toString`\nmodule.exports = TO_STRING_TAG_SUPPORT ? classofRaw : function (it) {\n  var O, tag, result;\n  return it === undefined ? 'Undefined' : it === null ? 'Null'\n    // @@toStringTag case\n    : typeof (tag = tryGet(O = $Object(it), TO_STRING_TAG)) == 'string' ? tag\n    // builtinTag case\n    : CORRECT_ARGUMENTS ? classofRaw(O)\n    // ES3 arguments fallback\n    : (result = classofRaw(O)) === 'Object' && isCallable(O.callee) ? 'Arguments' : result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/copy-constructor-properties.js\nvar copy_constructor_properties_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar hasOwn = (has_own_property_namespaceFn());\nvar ownKeys = (own_keys_namespaceFn());\nvar getOwnPropertyDescriptorModule = (object_get_own_property_descriptor_namespaceFn());\nvar definePropertyModule = (object_define_property_namespaceFn());\n\nmodule.exports = function (target, source, exceptions) {\n  var keys = ownKeys(source);\n  var defineProperty = definePropertyModule.f;\n  var getOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;\n  for (var i = 0; i < keys.length; i++) {\n    var key = keys[i];\n    if (!hasOwn(target, key) && !(exceptions && hasOwn(exceptions, key))) {\n      defineProperty(target, key, getOwnPropertyDescriptor(source, key));\n    }\n  }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/correct-prototype-getter.js\nvar correct_prototype_getter_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar fails = (fails_namespaceFn());\n\nmodule.exports = !fails(function () {\n  function F() { /* empty */ }\n  F.prototype.constructor = null;\n  // eslint-disable-next-line es/no-object-getprototypeof -- required for testing\n  return Object.getPrototypeOf(new F()) !== F.prototype;\n});\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/create-iter-result-object.js\nvar create_iter_result_object_namespaceFn = () => {\n\treturn __webpack_require__(529);\n};\n\n// MODULE: ./node_modules/core-js/internals/create-non-enumerable-property.js\nvar create_non_enumerable_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar definePropertyModule = (object_define_property_namespaceFn());\nvar createPropertyDescriptor = (create_property_descriptor_namespaceFn());\n\nmodule.exports = DESCRIPTORS ? function (object, key, value) {\n  return definePropertyModule.f(object, key, createPropertyDescriptor(1, value));\n} : function (object, key, value) {\n  object[key] = value;\n  return object;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/create-property-descriptor.js\nvar create_property_descriptor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nmodule.exports = function (bitmap, value) {\n  return {\n    enumerable: !(bitmap & 1),\n    configurable: !(bitmap & 2),\n    writable: !(bitmap & 4),\n    value: value\n  };\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/create-property.js\nvar create_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar definePropertyModule = (object_define_property_namespaceFn());\nvar createPropertyDescriptor = (create_property_descriptor_namespaceFn());\n\nmodule.exports = function (object, key, value) {\n  if (DESCRIPTORS) definePropertyModule.f(object, key, createPropertyDescriptor(0, value));\n  else object[key] = value;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/define-built-in-accessor.js\nvar define_built_in_accessor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar makeBuiltIn = (make_built_in_namespaceFn());\nvar defineProperty = (object_define_property_namespaceFn());\n\nmodule.exports = function (target, name, descriptor) {\n  if (descriptor.get) makeBuiltIn(descriptor.get, name, { getter: true });\n  if (descriptor.set) makeBuiltIn(descriptor.set, name, { setter: true });\n  return defineProperty.f(target, name, descriptor);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/define-built-in.js\nvar define_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isCallable = (is_callable_namespaceFn());\nvar definePropertyModule = (object_define_property_namespaceFn());\nvar makeBuiltIn = (make_built_in_namespaceFn());\nvar defineGlobalProperty = (define_global_property_namespaceFn());\n\nmodule.exports = function (O, key, value, options) {\n  if (!options) options = {};\n  var simple = options.enumerable;\n  var name = options.name !== undefined ? options.name : key;\n  if (isCallable(value)) makeBuiltIn(value, name, options);\n  if (options.global) {\n    if (simple) O[key] = value;\n    else defineGlobalProperty(key, value);\n  } else {\n    try {\n      if (!options.unsafe) delete O[key];\n      else if (O[key]) simple = true;\n    } catch (error) { /* empty */ }\n    if (simple) O[key] = value;\n    else definePropertyModule.f(O, key, {\n      value: value,\n      enumerable: false,\n      configurable: !options.nonConfigurable,\n      writable: !options.nonWritable\n    });\n  } return O;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/define-built-ins.js\nvar define_built_ins_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar defineBuiltIn = (define_built_in_namespaceFn());\n\nmodule.exports = function (target, src, options) {\n  for (var key in src) defineBuiltIn(target, key, src[key], options);\n  return target;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/define-global-property.js\nvar define_global_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\n\n// eslint-disable-next-line es/no-object-defineproperty -- safe\nvar defineProperty = Object.defineProperty;\n\nmodule.exports = function (key, value) {\n  try {\n    defineProperty(globalThis, key, { value: value, configurable: true, writable: true });\n  } catch (error) {\n    globalThis[key] = value;\n  } return value;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/descriptors.js\nvar descriptors_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar fails = (fails_namespaceFn());\n\n// Detect IE8's incomplete defineProperty implementation\nmodule.exports = !fails(function () {\n  // eslint-disable-next-line es/no-object-defineproperty -- required for testing\n  return Object.defineProperty({}, 1, { get: function () { return 7; } })[1] !== 7;\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/document-create-element.js\nvar document_create_element_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar isObject = (is_object_namespaceFn());\n\nvar document = globalThis.document;\n// typeof document.createElement is 'object' in old IE\nvar EXISTS = isObject(document) && isObject(document.createElement);\n\nmodule.exports = function (it) {\n  return EXISTS ? document.createElement(it) : {};\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/does-not-exceed-safe-integer.js\nvar does_not_exceed_safe_integer_namespaceFn = () => {\n\treturn __webpack_require__(837);\n};\n\n// MODULE: ./node_modules/core-js/internals/enum-bug-keys.js\nvar enum_bug_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n// IE8- don't enum bug keys\nmodule.exports = [\n  'constructor',\n  'hasOwnProperty',\n  'isPrototypeOf',\n  'propertyIsEnumerable',\n  'toLocaleString',\n  'toString',\n  'valueOf'\n];\n\n});\n\n// MODULE: ./node_modules/core-js/internals/environment-user-agent.js\nvar environment_user_agent_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\n\nvar navigator = globalThis.navigator;\nvar userAgent = navigator && navigator.userAgent;\n\nmodule.exports = userAgent ? String(userAgent) : '';\n\n});\n\n// MODULE: ./node_modules/core-js/internals/environment-v8-version.js\nvar environment_v8_version_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar userAgent = (environment_user_agent_namespaceFn());\n\nvar process = globalThis.process;\nvar Deno = globalThis.Deno;\nvar versions = process && process.versions || Deno && Deno.version;\nvar v8 = versions && versions.v8;\nvar match, version;\n\nif (v8) {\n  match = v8.split('.');\n  // in old Chrome, versions of V8 isn't V8 = Chrome / 10\n  // but their correct versions are not interesting for us\n  version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);\n}\n\n// BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`\n// so check `userAgent` even if `.v8` exists, but 0\nif (!version && userAgent) {\n  match = userAgent.match(/Edge\\/(\\d+)/);\n  if (!match || match[1] >= 74) {\n    match = userAgent.match(/Chrome\\/(\\d+)/);\n    if (match) version = +match[1];\n  }\n}\n\nmodule.exports = version;\n\n});\n\n// MODULE: ./node_modules/core-js/internals/export.js\nvar export_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar getOwnPropertyDescriptor = (object_get_own_property_descriptor_namespaceFn().f);\nvar createNonEnumerableProperty = (create_non_enumerable_property_namespaceFn());\nvar defineBuiltIn = (define_built_in_namespaceFn());\nvar defineGlobalProperty = (define_global_property_namespaceFn());\nvar copyConstructorProperties = (copy_constructor_properties_namespaceFn());\nvar isForced = (is_forced_namespaceFn());\n\n/*\n  options.target         - name of the target object\n  options.global         - target is the global object\n  options.stat           - export as static methods of target\n  options.proto          - export as prototype methods of target\n  options.real           - real prototype method for the `pure` version\n  options.forced         - export even if the native feature is available\n  options.bind           - bind methods to the target, required for the `pure` version\n  options.wrap           - wrap constructors to preventing global pollution, required for the `pure` version\n  options.unsafe         - use the simple assignment of property instead of delete + defineProperty\n  options.sham           - add a flag to not completely full polyfills\n  options.enumerable     - export as enumerable property\n  options.dontCallGetSet - prevent calling a getter on target\n  options.name           - the .name of the function if it does not match the key\n*/\nmodule.exports = function (options, source) {\n  var TARGET = options.target;\n  var GLOBAL = options.global;\n  var STATIC = options.stat;\n  var FORCED, target, key, targetProperty, sourceProperty, descriptor;\n  if (GLOBAL) {\n    target = globalThis;\n  } else if (STATIC) {\n    target = globalThis[TARGET] || defineGlobalProperty(TARGET, {});\n  } else {\n    target = globalThis[TARGET] && globalThis[TARGET].prototype;\n  }\n  if (target) for (key in source) {\n    sourceProperty = source[key];\n    if (options.dontCallGetSet) {\n      descriptor = getOwnPropertyDescriptor(target, key);\n      targetProperty = descriptor && descriptor.value;\n    } else targetProperty = target[key];\n    FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);\n    // contained in target\n    if (!FORCED && targetProperty !== undefined) {\n      if (typeof sourceProperty == typeof targetProperty) continue;\n      copyConstructorProperties(sourceProperty, targetProperty);\n    }\n    // add a flag to not completely full polyfills\n    if (options.sham || (targetProperty && targetProperty.sham)) {\n      createNonEnumerableProperty(sourceProperty, 'sham', true);\n    }\n    defineBuiltIn(target, key, sourceProperty, options);\n  }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/fails.js\nvar fails_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nmodule.exports = function (exec) {\n  try {\n    return !!exec();\n  } catch (error) {\n    return true;\n  }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-apply.js\nvar function_apply_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar NATIVE_BIND = (function_bind_native_namespaceFn());\n\nvar FunctionPrototype = Function.prototype;\nvar apply = FunctionPrototype.apply;\nvar call = FunctionPrototype.call;\n\n// eslint-disable-next-line es/no-function-prototype-bind, es/no-reflect -- safe\nmodule.exports = typeof Reflect == 'object' && Reflect.apply || (NATIVE_BIND ? call.bind(apply) : function () {\n  return call.apply(apply, arguments);\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-bind-context.js\nvar function_bind_context_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_clause_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar NATIVE_BIND = (function_bind_native_namespaceFn());\n\nvar bind = uncurryThis(uncurryThis.bind);\n\n// optional / simple context binding\nmodule.exports = function (fn, that) {\n  aCallable(fn);\n  return that === undefined ? fn : NATIVE_BIND ? bind(fn, that) : function (/* ...args */) {\n    return fn.apply(that, arguments);\n  };\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-bind-native.js\nvar function_bind_native_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar fails = (fails_namespaceFn());\n\nmodule.exports = !fails(function () {\n  // eslint-disable-next-line es/no-function-prototype-bind -- safe\n  var test = function () { /* empty */ }.bind();\n  // eslint-disable-next-line no-prototype-builtins -- safe\n  return typeof test != 'function' || test.hasOwnProperty('prototype');\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-call.js\nvar function_call_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar NATIVE_BIND = (function_bind_native_namespaceFn());\n\nvar call = Function.prototype.call;\n// eslint-disable-next-line es/no-function-prototype-bind -- safe\nmodule.exports = NATIVE_BIND ? call.bind(call) : function () {\n  return call.apply(call, arguments);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-name.js\nvar function_name_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\n\nvar FunctionPrototype = Function.prototype;\n// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\nvar getDescriptor = DESCRIPTORS && Object.getOwnPropertyDescriptor;\n\nvar EXISTS = hasOwn(FunctionPrototype, 'name');\n// additional protection from minified / mangled / dropped function names\nvar PROPER = EXISTS && function something() { /* empty */ }.name === 'something';\nvar CONFIGURABLE = EXISTS && (!DESCRIPTORS || (DESCRIPTORS && getDescriptor(FunctionPrototype, 'name').configurable));\n\nmodule.exports = {\n  ...(/* unused pure expression */ null && (EXISTS)),\n  ...(/* unused pure expression */ null && (PROPER)),\n  CONFIGURABLE: CONFIGURABLE\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-uncurry-this-accessor.js\nvar function_uncurry_this_accessor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\n\nmodule.exports = function (object, key, method) {\n  try {\n    // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\n    return uncurryThis(aCallable(Object.getOwnPropertyDescriptor(object, key)[method]));\n  } catch (error) { /* empty */ }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-uncurry-this-clause.js\nvar function_uncurry_this_clause_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar classofRaw = (classof_raw_namespaceFn());\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\nmodule.exports = function (fn) {\n  // Nashorn bug:\n  //   https://github.com/zloirock/core-js/issues/1128\n  //   https://github.com/zloirock/core-js/issues/1130\n  if (classofRaw(fn) === 'Function') return uncurryThis(fn);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/function-uncurry-this.js\nvar function_uncurry_this_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar NATIVE_BIND = (function_bind_native_namespaceFn());\n\nvar FunctionPrototype = Function.prototype;\nvar call = FunctionPrototype.call;\n// eslint-disable-next-line es/no-function-prototype-bind -- safe\nvar uncurryThisWithBind = NATIVE_BIND && FunctionPrototype.bind.bind(call, call);\n\nmodule.exports = NATIVE_BIND ? uncurryThisWithBind : function (fn) {\n  return function () {\n    return call.apply(fn, arguments);\n  };\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/get-built-in.js\nvar get_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\n\nvar aFunction = function (argument) {\n  return isCallable(argument) ? argument : undefined;\n};\n\nmodule.exports = function (namespace, method) {\n  return arguments.length < 2 ? aFunction(globalThis[namespace]) : globalThis[namespace] && globalThis[namespace][method];\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/get-iterator-direct.js\nvar get_iterator_direct_namespaceFn = () => {\n\treturn __webpack_require__(767);\n};\n\n// MODULE: ./node_modules/core-js/internals/get-iterator-internal.js\nvar get_iterator_internal_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar tryToString = (try_to_string_namespaceFn());\nvar getIteratorMethod = (get_iterator_method_internal_namespaceFn());\n\nvar $TypeError = TypeError;\n\nmodule.exports = function (argument, usingIterator) {\n  var iteratorMethod = arguments.length < 2 ? getIteratorMethod(argument) : usingIterator;\n  if (isCallable(iteratorMethod)) return anObject(call(iteratorMethod, argument));\n  throw new $TypeError(tryToString(argument) + ' is not iterable');\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/get-iterator-method-internal.js\nvar get_iterator_method_internal_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar classof = (classof_raw_namespaceFn());\nvar isNullOrUndefined = (is_null_or_undefined_namespaceFn());\nvar getMethod = (get_method_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\n\nvar ITERATOR = wellKnownSymbol('iterator');\nvar ArrayPrototype = Array.prototype;\n\nmodule.exports = function (it) {\n  if (!isNullOrUndefined(it)) return getMethod(it, ITERATOR)\n    || getMethod(it, '@@iterator')\n    || (classof(it) === 'Arguments' ? ArrayPrototype[ITERATOR] : undefined);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/get-method.js\nvar get_method_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aCallable = (a_callable_namespaceFn());\nvar isNullOrUndefined = (is_null_or_undefined_namespaceFn());\n\n// `GetMethod` abstract operation\n// https://tc39.es/ecma262/#sec-getmethod\nmodule.exports = function (V, P) {\n  var func = V[P];\n  return isNullOrUndefined(func) ? undefined : aCallable(func);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/get-set-record.js\nvar get_set_record_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar toIntegerOrInfinity = (to_integer_or_infinity_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\n\nvar INVALID_SIZE = 'Invalid size';\nvar $RangeError = RangeError;\nvar $TypeError = TypeError;\nvar max = Math.max;\n\nvar SetRecord = function (set, intSize) {\n  this.set = set;\n  this.size = max(intSize, 0);\n  this.has = aCallable(set.has);\n  this.keys = aCallable(set.keys);\n};\n\nSetRecord.prototype = {\n  getIterator: function () {\n    return getIteratorDirect(anObject(call(this.keys, this.set)));\n  },\n  includes: function (it) {\n    return call(this.has, this.set, it);\n  }\n};\n\n// `GetSetRecord` abstract operation\n// https://tc39.es/proposal-set-methods/#sec-getsetrecord\nmodule.exports = function (obj) {\n  anObject(obj);\n  var numSize = +obj.size;\n  // NOTE: If size is undefined, then numSize will be NaN\n  // eslint-disable-next-line no-self-compare -- NaN check\n  if (numSize !== numSize) throw new $TypeError(INVALID_SIZE);\n  var intSize = toIntegerOrInfinity(numSize);\n  if (intSize < 0) throw new $RangeError(INVALID_SIZE);\n  return new SetRecord(obj, intSize);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/global-this.js\nvar global_this_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar check = function (it) {\n  return it && it.Math === Math && it;\n};\n\n// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028\nmodule.exports =\n  // eslint-disable-next-line es/no-global-this -- safe\n  check(typeof globalThis == 'object' && globalThis) ||\n  check(typeof window == 'object' && window) ||\n  // eslint-disable-next-line no-restricted-globals -- safe\n  check(typeof self == 'object' && self) ||\n  check(typeof global == 'object' && global) ||\n  check(typeof this == 'object' && this) ||\n  // eslint-disable-next-line no-new-func -- fallback\n  (function () { return this; })() || Function('return this')();\n\n});\n\n// MODULE: ./node_modules/core-js/internals/has-own-property.js\nvar has_own_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar toObject = (to_object_namespaceFn());\n\nvar hasOwnProperty = uncurryThis({}.hasOwnProperty);\n\n// `HasOwnProperty` abstract operation\n// https://tc39.es/ecma262/#sec-hasownproperty\n// eslint-disable-next-line es/no-object-hasown -- safe\nmodule.exports = Object.hasOwn || function hasOwn(it, key) {\n  return hasOwnProperty(toObject(it), key);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/hidden-keys.js\nvar hidden_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nmodule.exports = {};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/html.js\nvar html_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar getBuiltIn = (get_built_in_namespaceFn());\n\nmodule.exports = getBuiltIn('document', 'documentElement');\n\n});\n\n// MODULE: ./node_modules/core-js/internals/ie8-dom-define.js\nvar ie8_dom_define_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar createElement = (document_create_element_namespaceFn());\n\n// Thanks to IE8 for its funny defineProperty\nmodule.exports = !DESCRIPTORS && !fails(function () {\n  // eslint-disable-next-line es/no-object-defineproperty -- required for testing\n  return Object.defineProperty(createElement('div'), 'a', {\n    get: function () { return 7; }\n  }).a !== 7;\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/indexed-object.js\nvar indexed_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar classof = (classof_raw_namespaceFn());\n\nvar $Object = Object;\nvar split = uncurryThis(''.split);\n\n// fallback for non-array-like ES3 and non-enumerable old V8 strings\nmodule.exports = fails(function () {\n  // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346\n  // eslint-disable-next-line no-prototype-builtins -- safe\n  return !$Object('z').propertyIsEnumerable(0);\n}) ? function (it) {\n  return classof(it) === 'String' ? split(it, '') : $Object(it);\n} : $Object;\n\n});\n\n// MODULE: ./node_modules/core-js/internals/inspect-source.js\nvar inspect_source_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar store = (shared_store_namespaceFn());\n\nvar functionToString = uncurryThis(Function.toString);\n\n// this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper\nif (!isCallable(store.inspectSource)) {\n  store.inspectSource = function (it) {\n    return functionToString(it);\n  };\n}\n\nmodule.exports = store.inspectSource;\n\n});\n\n// MODULE: ./node_modules/core-js/internals/internal-state.js\nvar internal_state_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar NATIVE_WEAK_MAP = (weak_map_basic_detection_namespaceFn());\nvar globalThis = (global_this_namespaceFn());\nvar isObject = (is_object_namespaceFn());\nvar createNonEnumerableProperty = (create_non_enumerable_property_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar shared = (shared_store_namespaceFn());\nvar sharedKey = (shared_key_namespaceFn());\nvar hiddenKeys = (hidden_keys_namespaceFn());\n\nvar OBJECT_ALREADY_INITIALIZED = 'Object already initialized';\nvar TypeError = globalThis.TypeError;\nvar WeakMap = globalThis.WeakMap;\nvar set, get, has;\n\nvar enforce = function (it) {\n  return has(it) ? get(it) : set(it, {});\n};\n\nvar getterFor = function (TYPE) {\n  return function (it) {\n    var state;\n    if (!isObject(it) || (state = get(it)).type !== TYPE) {\n      throw new TypeError('Incompatible receiver, ' + TYPE + ' required');\n    } return state;\n  };\n};\n\nif (NATIVE_WEAK_MAP || shared.state) {\n  var store = shared.state || (shared.state = new WeakMap());\n  /* eslint-disable no-self-assign -- prototype methods protection */\n  store.get = store.get;\n  store.has = store.has;\n  store.set = store.set;\n  /* eslint-enable no-self-assign -- prototype methods protection */\n  set = function (it, metadata) {\n    if (store.has(it)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);\n    metadata.facade = it;\n    store.set(it, metadata);\n    return metadata;\n  };\n  get = function (it) {\n    return store.get(it) || {};\n  };\n  has = function (it) {\n    return store.has(it);\n  };\n} else {\n  var STATE = sharedKey('state');\n  hiddenKeys[STATE] = true;\n  set = function (it, metadata) {\n    if (hasOwn(it, STATE)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);\n    metadata.facade = it;\n    createNonEnumerableProperty(it, STATE, metadata);\n    return metadata;\n  };\n  get = function (it) {\n    return hasOwn(it, STATE) ? it[STATE] : {};\n  };\n  has = function (it) {\n    return hasOwn(it, STATE);\n  };\n}\n\nmodule.exports = {\n  set: set,\n  get: get,\n  has: has,\n  enforce: enforce,\n  getterFor: getterFor\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-array-iterator-method.js\nvar is_array_iterator_method_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\nvar Iterators = (iterators_namespaceFn());\n\nvar ITERATOR = wellKnownSymbol('iterator');\nvar ArrayPrototype = Array.prototype;\n\n// check on default Array iterator\nmodule.exports = function (it) {\n  return it !== undefined && (Iterators.Array === it || ArrayPrototype[ITERATOR] === it);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-array.js\nvar is_array_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar classof = (classof_raw_namespaceFn());\n\n// `IsArray` abstract operation\n// https://tc39.es/ecma262/#sec-isarray\n// eslint-disable-next-line es/no-array-isarray -- safe\nmodule.exports = Array.isArray || function isArray(argument) {\n  return classof(argument) === 'Array';\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-callable.js\nvar is_callable_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot\nvar documentAll = typeof document == 'object' && document.all;\n\n// `IsCallable` abstract operation\n// https://tc39.es/ecma262/#sec-iscallable\n// eslint-disable-next-line unicorn/no-typeof-undefined -- required for testing\nmodule.exports = typeof documentAll == 'undefined' && documentAll !== undefined ? function (argument) {\n  return typeof argument == 'function' || argument === documentAll;\n} : function (argument) {\n  return typeof argument == 'function';\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-forced.js\nvar is_forced_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar fails = (fails_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\n\nvar replacement = /#|\\.prototype\\./;\n\nvar isForced = function (feature, detection) {\n  var value = data[normalize(feature)];\n  return value === POLYFILL ? true\n    : value === NATIVE ? false\n    : isCallable(detection) ? fails(detection)\n    : !!detection;\n};\n\nvar normalize = isForced.normalize = function (string) {\n  return String(string).replace(replacement, '.').toLowerCase();\n};\n\nvar data = isForced.data = {};\nvar NATIVE = isForced.NATIVE = 'N';\nvar POLYFILL = isForced.POLYFILL = 'P';\n\nmodule.exports = isForced;\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/is-null-or-undefined.js\nvar is_null_or_undefined_namespaceFn = () => {\n\treturn __webpack_require__(117);\n};\n\n// MODULE: ./node_modules/core-js/internals/is-object.js\nvar is_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isCallable = (is_callable_namespaceFn());\n\nmodule.exports = function (it) {\n  return typeof it == 'object' ? it !== null : isCallable(it);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-pure.js\nvar is_pure_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nmodule.exports = false;\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-raw-json.js\nvar is_raw_json_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar isObject = (is_object_namespaceFn());\nvar getInternalState = (internal_state_namespaceFn().get);\n\nmodule.exports = function isRawJSON(O) {\n  if (!isObject(O)) return false;\n  var state = getInternalState(O);\n  return !!state && state.type === 'RawJSON';\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/is-symbol.js\nvar is_symbol_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar getBuiltIn = (get_built_in_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar isPrototypeOf = (object_is_prototype_of_namespaceFn());\nvar USE_SYMBOL_AS_UID = (use_symbol_as_uid_namespaceFn());\n\nvar $Object = Object;\n\nmodule.exports = USE_SYMBOL_AS_UID ? function (it) {\n  return typeof it == 'symbol';\n} : function (it) {\n  var $Symbol = getBuiltIn('Symbol');\n  return isCallable($Symbol) && isPrototypeOf($Symbol.prototype, $Object(it));\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/iterate-simple.js\nvar iterate_simple_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\n\nmodule.exports = function (record, fn, ITERATOR_INSTEAD_OF_RECORD) {\n  var iterator = ITERATOR_INSTEAD_OF_RECORD ? record : record.iterator;\n  var next = record.next;\n  var step, result;\n  while (!(step = call(next, iterator)).done) {\n    result = fn(step.value);\n    if (result !== undefined) return result;\n  }\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/iterate.js\nvar iterate_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar bind = (function_bind_context_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar tryToString = (try_to_string_namespaceFn());\nvar isArrayIteratorMethod = (is_array_iterator_method_namespaceFn());\nvar lengthOfArrayLike = (length_of_array_like_namespaceFn());\nvar isPrototypeOf = (object_is_prototype_of_namespaceFn());\nvar getIterator = (get_iterator_internal_namespaceFn());\nvar getIteratorMethod = (get_iterator_method_internal_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\n\nvar $TypeError = TypeError;\n\nvar Result = function (stopped, result) {\n  this.stopped = stopped;\n  this.result = result;\n};\n\nvar ResultPrototype = Result.prototype;\n\nmodule.exports = function (iterable, unboundFunction, options) {\n  var that = options && options.that;\n  var AS_ENTRIES = !!(options && options.AS_ENTRIES);\n  var IS_RECORD = !!(options && options.IS_RECORD);\n  var IS_ITERATOR = !!(options && options.IS_ITERATOR);\n  var INTERRUPTED = !!(options && options.INTERRUPTED);\n  var fn = bind(unboundFunction, that);\n  var iterator, iterFn, index, length, result, next, step;\n\n  var stop = function (condition) {\n    var $iterator = iterator;\n    iterator = undefined;\n    if ($iterator) iteratorClose($iterator, 'normal');\n    return new Result(true, condition);\n  };\n\n  var callFn = function (value) {\n    if (AS_ENTRIES) {\n      anObject(value);\n      return INTERRUPTED ? fn(value[0], value[1], stop) : fn(value[0], value[1]);\n    } return INTERRUPTED ? fn(value, stop) : fn(value);\n  };\n\n  if (IS_RECORD) {\n    iterator = iterable.iterator;\n  } else if (IS_ITERATOR) {\n    iterator = iterable;\n  } else {\n    iterFn = getIteratorMethod(iterable);\n    if (!iterFn) throw new $TypeError(tryToString(iterable) + ' is not iterable');\n    // optimisation for array iterators\n    if (isArrayIteratorMethod(iterFn)) {\n      for (index = 0, length = lengthOfArrayLike(iterable); length > index; index++) {\n        result = callFn(iterable[index]);\n        if (result && isPrototypeOf(ResultPrototype, result)) return result;\n      } return new Result(false);\n    }\n    iterator = getIterator(iterable, iterFn);\n  }\n\n  next = IS_RECORD ? iterable.next : iterator.next;\n  while (!(step = call(next, iterator)).done) {\n    // `IteratorValue` errors should propagate without closing the iterator\n    var value = step.value;\n    try {\n      result = callFn(value);\n    } catch (error) {\n      if (iterator) iteratorClose(iterator, 'throw', error);\n      else throw error;\n    }\n    if (typeof result == 'object' && result && isPrototypeOf(ResultPrototype, result)) return result;\n  } return new Result(false);\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/iterator-cleanup-state.js\nvar iterator_cleanup_state_namespaceFn = () => {\n\treturn __webpack_require__(859);\n};\n\n// MODULE: ./node_modules/core-js/internals/iterator-close-all.js\nvar iterator_close_all_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar iteratorClose = (iterator_close_namespaceFn());\n\nmodule.exports = function (iters, kind, value) {\n  for (var i = iters.length - 1; i >= 0; i--) {\n    if (iters[i] === undefined) continue;\n    try {\n      value = iteratorClose(iters[i].iterator, kind, value);\n    } catch (error) {\n      kind = 'throw';\n      value = error;\n    }\n  }\n  if (kind === 'throw') throw value;\n  return value;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/iterator-close.js\nvar iterator_close_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getMethod = (get_method_namespaceFn());\n\nmodule.exports = function (iterator, kind, value) {\n  var innerResult, innerError;\n  anObject(iterator);\n  try {\n    innerResult = getMethod(iterator, 'return');\n    if (!innerResult) {\n      if (kind === 'throw') throw value;\n      return value;\n    }\n    innerResult = call(innerResult, iterator);\n  } catch (error) {\n    innerError = true;\n    innerResult = error;\n  }\n  if (kind === 'throw') throw value;\n  if (innerError) throw innerResult;\n  anObject(innerResult);\n  return value;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/iterator-create-proxy.js\nvar iterator_create_proxy_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\nvar create = (object_create_namespaceFn());\nvar createNonEnumerableProperty = (create_non_enumerable_property_namespaceFn());\nvar defineBuiltIns = (define_built_ins_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\nvar InternalStateModule = (internal_state_namespaceFn());\nvar getMethod = (get_method_namespaceFn());\nvar IteratorPrototype = (iterators_core_namespaceFn().IteratorPrototype);\nvar createIterResultObject = (create_iter_result_object_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorCloseAll = (iterator_close_all_namespaceFn());\nvar cleanupState = (iterator_cleanup_state_namespaceFn());\n\nvar TO_STRING_TAG = wellKnownSymbol('toStringTag');\nvar ITERATOR_HELPER = 'IteratorHelper';\nvar WRAP_FOR_VALID_ITERATOR = 'WrapForValidIterator';\nvar NORMAL = 'normal';\nvar THROW = 'throw';\nvar setInternalState = InternalStateModule.set;\n\nvar createIteratorProxyPrototype = function (IS_ITERATOR) {\n  var getInternalState = InternalStateModule.getterFor(IS_ITERATOR ? WRAP_FOR_VALID_ITERATOR : ITERATOR_HELPER);\n\n  return defineBuiltIns(create(IteratorPrototype), {\n    next: function next() {\n      var state = getInternalState(this);\n      // for simplification:\n      //   for `%WrapForValidIteratorPrototype%.next` or with `state.returnHandlerResult` our `nextHandler` returns `IterResultObject`\n      //   for `%IteratorHelperPrototype%.next` - just a value\n      if (IS_ITERATOR) return state.nextHandler();\n      if (state.done) return createIterResultObject(undefined, true);\n      try {\n        var result = state.nextHandler();\n        if (state.done) cleanupState(state);\n        return state.returnHandlerResult ? result : createIterResultObject(result, state.done);\n      } catch (error) {\n        state.done = true;\n        cleanupState(state);\n        throw error;\n      }\n    },\n    'return': function () {\n      var state = getInternalState(this);\n      var iterator = state.iterator;\n      var inner = state.inner;\n      var openIters = state.openIters;\n      var done = state.done;\n      state.done = true;\n      if (IS_ITERATOR) {\n        var returnMethod = getMethod(iterator, 'return');\n        return returnMethod ? call(returnMethod, iterator) : createIterResultObject(undefined, true);\n      }\n      cleanupState(state);\n      if (done) return createIterResultObject(undefined, true);\n      if (inner) try {\n        iteratorClose(inner.iterator, NORMAL);\n      } catch (error) {\n        return iteratorClose(iterator, THROW, error);\n      }\n      if (openIters) try {\n        iteratorCloseAll(openIters, NORMAL);\n      } catch (error) {\n        if (iterator) return iteratorClose(iterator, THROW, error);\n        throw error;\n      }\n      if (iterator) iteratorClose(iterator, NORMAL);\n      return createIterResultObject(undefined, true);\n    }\n  });\n};\n\nvar WrapForValidIteratorPrototype = createIteratorProxyPrototype(true);\nvar IteratorHelperPrototype = createIteratorProxyPrototype(false);\n\ncreateNonEnumerableProperty(IteratorHelperPrototype, TO_STRING_TAG, 'Iterator Helper');\n\nmodule.exports = function (nextHandler, IS_ITERATOR, RETURN_HANDLER_RESULT) {\n  var IteratorProxy = function Iterator(record, state) {\n    if (state) {\n      state.iterator = record.iterator;\n      state.next = record.next;\n    } else state = record;\n    state.type = IS_ITERATOR ? WRAP_FOR_VALID_ITERATOR : ITERATOR_HELPER;\n    state.returnHandlerResult = !!RETURN_HANDLER_RESULT;\n    state.nextHandler = nextHandler;\n    state.counter = 0;\n    state.done = false;\n    setInternalState(this, state);\n  };\n\n  IteratorProxy.prototype = IS_ITERATOR ? WrapForValidIteratorPrototype : IteratorHelperPrototype;\n\n  return IteratorProxy;\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/iterator-helper-throws-on-invalid-iterator.js\nvar iterator_helper_throws_on_invalid_iterator_namespaceFn = () => {\n\treturn __webpack_require__(684);\n};\n\n// MODULE: ./node_modules/core-js/internals/iterator-helper-without-closing-on-early-error.js\nvar iterator_helper_without_closing_on_early_error_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\n\n// https://github.com/tc39/ecma262/pull/3467\nmodule.exports = function (METHOD_NAME, ExpectedError) {\n  var Iterator = globalThis.Iterator;\n  var IteratorPrototype = Iterator && Iterator.prototype;\n  var method = IteratorPrototype && IteratorPrototype[METHOD_NAME];\n\n  var CLOSED = false;\n\n  if (method) try {\n    method.call({\n      next: function () { return { done: true }; },\n      'return': function () { CLOSED = true; }\n    }, -1);\n  } catch (error) {\n    // https://bugs.webkit.org/show_bug.cgi?id=291195\n    if (!(error instanceof ExpectedError)) CLOSED = false;\n  }\n\n  if (!CLOSED) return method;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/iterators-core.js\nvar iterators_core_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar fails = (fails_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar isObject = (is_object_namespaceFn());\nvar create = (object_create_namespaceFn());\nvar getPrototypeOf = (object_get_prototype_of_namespaceFn());\nvar defineBuiltIn = (define_built_in_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar ITERATOR = wellKnownSymbol('iterator');\nvar BUGGY_SAFARI_ITERATORS = false;\n\n// `%IteratorPrototype%` object\n// https://tc39.es/ecma262/#sec-%iteratorprototype%-object\nvar IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;\n\n/* eslint-disable es/no-array-prototype-keys -- safe */\nif ([].keys) {\n  arrayIterator = [].keys();\n  // Safari 8 has buggy iterators w/o `next`\n  if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;\n  else {\n    PrototypeOfArrayIteratorPrototype = getPrototypeOf(getPrototypeOf(arrayIterator));\n    if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;\n  }\n}\n\nvar NEW_ITERATOR_PROTOTYPE = !isObject(IteratorPrototype) || fails(function () {\n  var test = {};\n  // FF44- legacy iterators case\n  return IteratorPrototype[ITERATOR].call(test) !== test;\n});\n\nif (NEW_ITERATOR_PROTOTYPE) IteratorPrototype = {};\nelse if (IS_PURE) IteratorPrototype = create(IteratorPrototype);\n\n// `%IteratorPrototype%[@@iterator]()` method\n// https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator\nif (!isCallable(IteratorPrototype[ITERATOR])) {\n  defineBuiltIn(IteratorPrototype, ITERATOR, function () {\n    return this;\n  });\n}\n\nmodule.exports = {\n  IteratorPrototype: IteratorPrototype,\n  ...(/* unused pure expression */ null && (BUGGY_SAFARI_ITERATORS))\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/iterators.js\nvar iterators_namespaceFn = () => {\n\treturn __webpack_require__(269);\n};\n\n// MODULE: ./node_modules/core-js/internals/length-of-array-like.js\nvar length_of_array_like_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar toLength = (to_length_namespaceFn());\n\n// `LengthOfArrayLike` abstract operation\n// https://tc39.es/ecma262/#sec-lengthofarraylike\nmodule.exports = function (obj) {\n  return toLength(obj.length);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/make-built-in.js\nvar make_built_in_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar CONFIGURABLE_FUNCTION_NAME = (function_name_namespaceFn().CONFIGURABLE);\nvar inspectSource = (inspect_source_namespaceFn());\nvar InternalStateModule = (internal_state_namespaceFn());\n\nvar enforceInternalState = InternalStateModule.enforce;\nvar getInternalState = InternalStateModule.get;\nvar $String = String;\n// eslint-disable-next-line es/no-object-defineproperty -- safe\nvar defineProperty = Object.defineProperty;\nvar stringSlice = uncurryThis(''.slice);\nvar replace = uncurryThis(''.replace);\nvar join = uncurryThis([].join);\n\nvar CONFIGURABLE_LENGTH = DESCRIPTORS && !fails(function () {\n  return defineProperty(function () { /* empty */ }, 'length', { value: 8 }).length !== 8;\n});\n\nvar TEMPLATE = String(String).split('String');\n\nvar makeBuiltIn = module.exports = function (value, name, options) {\n  if (stringSlice($String(name), 0, 7) === 'Symbol(') {\n    name = '[' + replace($String(name), /^Symbol\\(([^)]*)\\).*$/, '$1') + ']';\n  }\n  if (options && options.getter) name = 'get ' + name;\n  if (options && options.setter) name = 'set ' + name;\n  if (!hasOwn(value, 'name') || (CONFIGURABLE_FUNCTION_NAME && value.name !== name)) {\n    if (DESCRIPTORS) defineProperty(value, 'name', { value: name, configurable: true });\n    else value.name = name;\n  }\n  if (CONFIGURABLE_LENGTH && options && hasOwn(options, 'arity') && value.length !== options.arity) {\n    defineProperty(value, 'length', { value: options.arity });\n  }\n  try {\n    if (options && hasOwn(options, 'constructor') && options.constructor) {\n      if (DESCRIPTORS) defineProperty(value, 'prototype', { writable: false });\n    // in V8 ~ Chrome 53, prototypes of some methods, like `Array.prototype.values`, are non-writable\n    } else if (value.prototype) value.prototype = undefined;\n  } catch (error) { /* empty */ }\n  var state = enforceInternalState(value);\n  if (!hasOwn(state, 'source')) {\n    state.source = join(TEMPLATE, typeof name == 'string' ? name : '');\n  } return value;\n};\n\n// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative\n// eslint-disable-next-line no-extend-native -- required\nFunction.prototype.toString = makeBuiltIn(function toString() {\n  return isCallable(this) && getInternalState(this).source || inspectSource(this);\n}, 'toString');\n\n});\n\n// MODULE: ./node_modules/core-js/internals/map-helpers.js\nvar map_helpers_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\n// eslint-disable-next-line es/no-map -- safe\nvar MapPrototype = Map.prototype;\n\nmodule.exports = {\n  // eslint-disable-next-line es/no-map -- safe\n  Map: Map,\n  set: uncurryThis(MapPrototype.set),\n  get: uncurryThis(MapPrototype.get),\n  has: uncurryThis(MapPrototype.has),\n  remove: uncurryThis(MapPrototype['delete']),\n  proto: MapPrototype\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/native-raw-json.js\nvar native_raw_json_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n/* eslint-disable es/no-json -- safe */\nvar fails = (fails_namespaceFn());\n\nmodule.exports = !fails(function () {\n  var unsafeInt = '9007199254740993';\n  // eslint-disable-next-line es/no-json-rawjson -- feature detection\n  var raw = JSON.rawJSON(unsafeInt);\n  // eslint-disable-next-line es/no-json-israwjson -- feature detection\n  return !JSON.isRawJSON(raw) || JSON.stringify(raw) !== unsafeInt;\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-create.js\nvar object_create_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n/* global ActiveXObject -- old IE, WSH */\nvar anObject = (an_object_namespaceFn());\nvar definePropertiesModule = (object_define_properties_namespaceFn());\nvar enumBugKeys = (enum_bug_keys_namespaceFn());\nvar hiddenKeys = (hidden_keys_namespaceFn());\nvar html = (html_namespaceFn());\nvar documentCreateElement = (document_create_element_namespaceFn());\nvar sharedKey = (shared_key_namespaceFn());\n\nvar GT = '>';\nvar LT = '<';\nvar PROTOTYPE = 'prototype';\nvar SCRIPT = 'script';\nvar IE_PROTO = sharedKey('IE_PROTO');\n\nvar EmptyConstructor = function () { /* empty */ };\n\nvar scriptTag = function (content) {\n  return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;\n};\n\n// Create object with fake `null` prototype: use ActiveX Object with cleared prototype\nvar NullProtoObjectViaActiveX = function (activeXDocument) {\n  activeXDocument.write(scriptTag(''));\n  activeXDocument.close();\n  var temp = activeXDocument.parentWindow.Object;\n  // eslint-disable-next-line no-useless-assignment -- avoid memory leak\n  activeXDocument = null;\n  return temp;\n};\n\n// Create object with fake `null` prototype: use iframe Object with cleared prototype\nvar NullProtoObjectViaIFrame = function () {\n  // Thrash, waste and sodomy: IE GC bug\n  var iframe = documentCreateElement('iframe');\n  var JS = 'java' + SCRIPT + ':';\n  var iframeDocument;\n  iframe.style.display = 'none';\n  html.appendChild(iframe);\n  // https://github.com/zloirock/core-js/issues/475\n  iframe.src = String(JS);\n  iframeDocument = iframe.contentWindow.document;\n  iframeDocument.open();\n  iframeDocument.write(scriptTag('document.F=Object'));\n  iframeDocument.close();\n  return iframeDocument.F;\n};\n\n// Check for document.domain and active x support\n// No need to use active x approach when document.domain is not set\n// see https://github.com/es-shims/es5-shim/issues/150\n// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346\n// avoid IE GC bug\nvar activeXDocument;\nvar NullProtoObject = function () {\n  try {\n    activeXDocument = new ActiveXObject('htmlfile');\n  } catch (error) { /* ignore */ }\n  NullProtoObject = typeof document != 'undefined'\n    ? document.domain && activeXDocument\n      ? NullProtoObjectViaActiveX(activeXDocument) // old IE\n      : NullProtoObjectViaIFrame()\n    : NullProtoObjectViaActiveX(activeXDocument); // WSH\n  var length = enumBugKeys.length;\n  while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];\n  return NullProtoObject();\n};\n\nhiddenKeys[IE_PROTO] = true;\n\n// `Object.create` method\n// https://tc39.es/ecma262/#sec-object.create\n// eslint-disable-next-line es/no-object-create -- safe\nmodule.exports = Object.create || function create(O, Properties) {\n  var result;\n  if (O !== null) {\n    EmptyConstructor[PROTOTYPE] = anObject(O);\n    result = new EmptyConstructor();\n    EmptyConstructor[PROTOTYPE] = null;\n    // add \"__proto__\" for Object.getPrototypeOf polyfill\n    result[IE_PROTO] = O;\n  } else result = NullProtoObject();\n  return Properties === undefined ? result : definePropertiesModule.f(result, Properties);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-define-properties.js\nvar object_define_properties_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar V8_PROTOTYPE_DEFINE_BUG = (v8_prototype_define_bug_namespaceFn());\nvar definePropertyModule = (object_define_property_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar toIndexedObject = (to_indexed_object_namespaceFn());\nvar objectKeys = (object_keys_namespaceFn());\n\n// `Object.defineProperties` method\n// https://tc39.es/ecma262/#sec-object.defineproperties\n// eslint-disable-next-line es/no-object-defineproperties -- safe\nexports.f = DESCRIPTORS && !V8_PROTOTYPE_DEFINE_BUG ? Object.defineProperties : function defineProperties(O, Properties) {\n  anObject(O);\n  var props = toIndexedObject(Properties);\n  var keys = objectKeys(Properties);\n  var length = keys.length;\n  var index = 0;\n  var key;\n  while (length > index) definePropertyModule.f(O, key = keys[index++], props[key]);\n  return O;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-define-property.js\nvar object_define_property_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar IE8_DOM_DEFINE = (ie8_dom_define_namespaceFn());\nvar V8_PROTOTYPE_DEFINE_BUG = (v8_prototype_define_bug_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar toPropertyKey = (to_property_key_namespaceFn());\n\nvar $TypeError = TypeError;\n// eslint-disable-next-line es/no-object-defineproperty -- safe\nvar $defineProperty = Object.defineProperty;\n// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\nvar $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;\nvar ENUMERABLE = 'enumerable';\nvar CONFIGURABLE = 'configurable';\nvar WRITABLE = 'writable';\n\n// `Object.defineProperty` method\n// https://tc39.es/ecma262/#sec-object.defineproperty\nexports.f = DESCRIPTORS ? V8_PROTOTYPE_DEFINE_BUG ? function defineProperty(O, P, Attributes) {\n  anObject(O);\n  P = toPropertyKey(P);\n  anObject(Attributes);\n  if (typeof O === 'function' && P === 'prototype' && 'value' in Attributes && WRITABLE in Attributes && !Attributes[WRITABLE]) {\n    var current = $getOwnPropertyDescriptor(O, P);\n    if (current && current[WRITABLE]) {\n      O[P] = Attributes.value;\n      Attributes = {\n        configurable: CONFIGURABLE in Attributes ? Attributes[CONFIGURABLE] : current[CONFIGURABLE],\n        enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],\n        writable: false\n      };\n    }\n  } return $defineProperty(O, P, Attributes);\n} : $defineProperty : function defineProperty(O, P, Attributes) {\n  anObject(O);\n  P = toPropertyKey(P);\n  anObject(Attributes);\n  if (IE8_DOM_DEFINE) try {\n    return $defineProperty(O, P, Attributes);\n  } catch (error) { /* empty */ }\n  if ('get' in Attributes || 'set' in Attributes) throw new $TypeError('Accessors not supported');\n  if ('value' in Attributes) O[P] = Attributes.value;\n  return O;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-get-own-property-descriptor.js\nvar object_get_own_property_descriptor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar propertyIsEnumerableModule = (object_property_is_enumerable_namespaceFn());\nvar createPropertyDescriptor = (create_property_descriptor_namespaceFn());\nvar toIndexedObject = (to_indexed_object_namespaceFn());\nvar toPropertyKey = (to_property_key_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar IE8_DOM_DEFINE = (ie8_dom_define_namespaceFn());\n\n// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\nvar $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;\n\n// `Object.getOwnPropertyDescriptor` method\n// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor\nexports.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {\n  O = toIndexedObject(O);\n  P = toPropertyKey(P);\n  if (IE8_DOM_DEFINE) try {\n    return $getOwnPropertyDescriptor(O, P);\n  } catch (error) { /* empty */ }\n  if (hasOwn(O, P)) return createPropertyDescriptor(!call(propertyIsEnumerableModule.f, O, P), O[P]);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-get-own-property-names.js\nvar object_get_own_property_names_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar internalObjectKeys = (object_keys_internal_namespaceFn());\nvar enumBugKeys = (enum_bug_keys_namespaceFn());\n\nvar hiddenKeys = enumBugKeys.concat('length', 'prototype');\n\n// `Object.getOwnPropertyNames` method\n// https://tc39.es/ecma262/#sec-object.getownpropertynames\n// eslint-disable-next-line es/no-object-getownpropertynames -- safe\nexports.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {\n  return internalObjectKeys(O, hiddenKeys);\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/object-get-own-property-symbols.js\nvar object_get_own_property_symbols_namespaceFn = () => {\n\treturn __webpack_require__(717);\n};\n\n// MODULE: ./node_modules/core-js/internals/object-get-prototype-of.js\nvar object_get_prototype_of_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar hasOwn = (has_own_property_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar toObject = (to_object_namespaceFn());\nvar sharedKey = (shared_key_namespaceFn());\nvar CORRECT_PROTOTYPE_GETTER = (correct_prototype_getter_namespaceFn());\n\nvar IE_PROTO = sharedKey('IE_PROTO');\nvar $Object = Object;\nvar ObjectPrototype = $Object.prototype;\n\n// `Object.getPrototypeOf` method\n// https://tc39.es/ecma262/#sec-object.getprototypeof\n// eslint-disable-next-line es/no-object-getprototypeof -- safe\nmodule.exports = CORRECT_PROTOTYPE_GETTER ? $Object.getPrototypeOf : function (O) {\n  var object = toObject(O);\n  if (hasOwn(object, IE_PROTO)) return object[IE_PROTO];\n  var constructor = object.constructor;\n  if (isCallable(constructor) && object instanceof constructor) {\n    return constructor.prototype;\n  } return object instanceof $Object ? ObjectPrototype : null;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-is-prototype-of.js\nvar object_is_prototype_of_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\nmodule.exports = uncurryThis({}.isPrototypeOf);\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-keys-internal.js\nvar object_keys_internal_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar toIndexedObject = (to_indexed_object_namespaceFn());\nvar indexOf = (array_includes_namespaceFn().indexOf);\nvar hiddenKeys = (hidden_keys_namespaceFn());\n\nvar push = uncurryThis([].push);\n\nmodule.exports = function (object, names) {\n  var O = toIndexedObject(object);\n  var i = 0;\n  var result = [];\n  var key;\n  for (key in O) !hasOwn(hiddenKeys, key) && hasOwn(O, key) && push(result, key);\n  // Don't enum bug & hidden keys\n  while (names.length > i) if (hasOwn(O, key = names[i++])) {\n    ~indexOf(result, key) || push(result, key);\n  }\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-keys.js\nvar object_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar internalObjectKeys = (object_keys_internal_namespaceFn());\nvar enumBugKeys = (enum_bug_keys_namespaceFn());\n\n// `Object.keys` method\n// https://tc39.es/ecma262/#sec-object.keys\n// eslint-disable-next-line es/no-object-keys -- safe\nmodule.exports = Object.keys || function keys(O) {\n  return internalObjectKeys(O, enumBugKeys);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/object-property-is-enumerable.js\nvar object_property_is_enumerable_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $propertyIsEnumerable = {}.propertyIsEnumerable;\n// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe\nvar getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;\n\n// Nashorn ~ JDK8 bug\nvar NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({ 1: 2 }, 1);\n\n// `Object.prototype.propertyIsEnumerable` method implementation\n// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable\nexports.f = NASHORN_BUG ? function propertyIsEnumerable(V) {\n  var descriptor = getOwnPropertyDescriptor(this, V);\n  return !!descriptor && descriptor.enumerable;\n} : $propertyIsEnumerable;\n\n});\n\n// MODULE: ./node_modules/core-js/internals/ordinary-to-primitive.js\nvar ordinary_to_primitive_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar isObject = (is_object_namespaceFn());\n\nvar $TypeError = TypeError;\n\n// `OrdinaryToPrimitive` abstract operation\n// https://tc39.es/ecma262/#sec-ordinarytoprimitive\nmodule.exports = function (input, pref) {\n  var fn, val;\n  if (pref === 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;\n  if (isCallable(fn = input.valueOf) && !isObject(val = call(fn, input))) return val;\n  if (pref !== 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;\n  throw new $TypeError(\"Can't convert object to primitive value\");\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/own-keys.js\nvar own_keys_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar getBuiltIn = (get_built_in_namespaceFn());\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar getOwnPropertyNamesModule = (object_get_own_property_names_namespaceFn());\nvar getOwnPropertySymbolsModule = (object_get_own_property_symbols_namespaceFn());\nvar anObject = (an_object_namespaceFn());\n\nvar concat = uncurryThis([].concat);\n\n// all object keys, includes non-enumerable and symbols\nmodule.exports = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {\n  var keys = getOwnPropertyNamesModule.f(anObject(it));\n  var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;\n  return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/parse-json-string.js\nvar parse_json_string_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\n\nvar $SyntaxError = SyntaxError;\nvar $parseInt = parseInt;\nvar fromCharCode = String.fromCharCode;\nvar at = uncurryThis(''.charAt);\nvar slice = uncurryThis(''.slice);\nvar exec = uncurryThis(/./.exec);\n\nvar codePoints = {\n  '\\\\\"': '\"',\n  '\\\\\\\\': '\\\\',\n  '\\\\/': '/',\n  '\\\\b': '\\b',\n  '\\\\f': '\\f',\n  '\\\\n': '\\n',\n  '\\\\r': '\\r',\n  '\\\\t': '\\t'\n};\n\nvar IS_4_HEX_DIGITS = /^[\\da-f]{4}$/i;\n// eslint-disable-next-line regexp/no-control-character -- safe\nvar IS_C0_CONTROL_CODE = /^[\\u0000-\\u001F]$/;\n\nmodule.exports = function (source, i) {\n  var unterminated = true;\n  var value = '';\n  while (i < source.length) {\n    var chr = at(source, i);\n    if (chr === '\\\\') {\n      var twoChars = slice(source, i, i + 2);\n      if (hasOwn(codePoints, twoChars)) {\n        value += codePoints[twoChars];\n        i += 2;\n      } else if (twoChars === '\\\\u') {\n        i += 2;\n        var fourHexDigits = slice(source, i, i + 4);\n        if (!exec(IS_4_HEX_DIGITS, fourHexDigits)) throw new $SyntaxError('Bad Unicode escape at: ' + i);\n        value += fromCharCode($parseInt(fourHexDigits, 16));\n        i += 4;\n      } else throw new $SyntaxError('Unknown escape sequence: \"' + twoChars + '\"');\n    } else if (chr === '\"') {\n      unterminated = false;\n      i++;\n      break;\n    } else {\n      if (exec(IS_C0_CONTROL_CODE, chr)) throw new $SyntaxError('Bad control character in string literal at: ' + i);\n      value += chr;\n      i++;\n    }\n  }\n  if (unterminated) throw new $SyntaxError('Unterminated string at: ' + i);\n  return { value: value, end: i };\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/require-object-coercible.js\nvar require_object_coercible_namespaceFn = () => {\n\treturn __webpack_require__(750);\n};\n\n// MODULE: ./node_modules/core-js/internals/set-clone.js\nvar set_clone_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar SetHelpers = (set_helpers_namespaceFn());\nvar iterate = (set_iterate_namespaceFn());\n\nvar Set = SetHelpers.Set;\nvar add = SetHelpers.add;\n\nmodule.exports = function (set) {\n  var result = new Set();\n  iterate(set, function (it) {\n    add(result, it);\n  });\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-difference.js\nvar set_difference_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar SetHelpers = (set_helpers_namespaceFn());\nvar clone = (set_clone_namespaceFn());\nvar size = (set_size_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSet = (set_iterate_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\n\nvar has = SetHelpers.has;\nvar remove = SetHelpers.remove;\n\n// `Set.prototype.difference` method\n// https://tc39.es/ecma262/#sec-set.prototype.difference\nmodule.exports = function difference(other) {\n  var O = aSet(this);\n  var otherRec = getSetRecord(other);\n  var result = clone(O);\n  if (size(result) <= otherRec.size) iterateSet(result, function (e) {\n    if (otherRec.includes(e)) remove(result, e);\n  });\n  else iterateSimple(otherRec.getIterator(), function (e) {\n    if (has(result, e)) remove(result, e);\n  });\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-helpers.js\nvar set_helpers_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\n// eslint-disable-next-line es/no-set -- safe\nvar SetPrototype = Set.prototype;\n\nmodule.exports = {\n  // eslint-disable-next-line es/no-set -- safe\n  Set: Set,\n  add: uncurryThis(SetPrototype.add),\n  has: uncurryThis(SetPrototype.has),\n  remove: uncurryThis(SetPrototype['delete']),\n  proto: SetPrototype\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-intersection.js\nvar set_intersection_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar SetHelpers = (set_helpers_namespaceFn());\nvar size = (set_size_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSet = (set_iterate_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\n\nvar Set = SetHelpers.Set;\nvar add = SetHelpers.add;\nvar has = SetHelpers.has;\n\n// `Set.prototype.intersection` method\n// https://tc39.es/ecma262/#sec-set.prototype.intersection\nmodule.exports = function intersection(other) {\n  var O = aSet(this);\n  var otherRec = getSetRecord(other);\n  var result = new Set();\n\n  if (size(O) > otherRec.size) {\n    iterateSimple(otherRec.getIterator(), function (e) {\n      if (has(O, e)) add(result, e);\n    });\n  } else {\n    iterateSet(O, function (e) {\n      if (otherRec.includes(e)) add(result, e);\n    });\n  }\n\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-is-disjoint-from.js\nvar set_is_disjoint_from_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar has = (set_helpers_namespaceFn().has);\nvar size = (set_size_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSet = (set_iterate_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\n\n// `Set.prototype.isDisjointFrom` method\n// https://tc39.es/ecma262/#sec-set.prototype.isdisjointfrom\nmodule.exports = function isDisjointFrom(other) {\n  var O = aSet(this);\n  var otherRec = getSetRecord(other);\n  if (size(O) <= otherRec.size) return iterateSet(O, function (e) {\n    if (otherRec.includes(e)) return false;\n  }, true) !== false;\n  var iterator = otherRec.getIterator();\n  return iterateSimple(iterator, function (e) {\n    if (has(O, e)) return iteratorClose(iterator.iterator, 'normal', false);\n  }) !== false;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-is-subset-of.js\nvar set_is_subset_of_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar size = (set_size_namespaceFn());\nvar iterate = (set_iterate_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\n\n// `Set.prototype.isSubsetOf` method\n// https://tc39.es/ecma262/#sec-set.prototype.issubsetof\nmodule.exports = function isSubsetOf(other) {\n  var O = aSet(this);\n  var otherRec = getSetRecord(other);\n  if (size(O) > otherRec.size) return false;\n  return iterate(O, function (e) {\n    if (!otherRec.includes(e)) return false;\n  }, true) !== false;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-is-superset-of.js\nvar set_is_superset_of_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar has = (set_helpers_namespaceFn().has);\nvar size = (set_size_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\n\n// `Set.prototype.isSupersetOf` method\n// https://tc39.es/ecma262/#sec-set.prototype.issupersetof\nmodule.exports = function isSupersetOf(other) {\n  var O = aSet(this);\n  var otherRec = getSetRecord(other);\n  if (size(O) < otherRec.size) return false;\n  var iterator = otherRec.getIterator();\n  return iterateSimple(iterator, function (e) {\n    if (!has(O, e)) return iteratorClose(iterator.iterator, 'normal', false);\n  }) !== false;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-iterate.js\nvar set_iterate_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\nvar SetHelpers = (set_helpers_namespaceFn());\n\nvar Set = SetHelpers.Set;\nvar SetPrototype = SetHelpers.proto;\nvar forEach = uncurryThis(SetPrototype.forEach);\nvar keys = uncurryThis(SetPrototype.keys);\nvar next = keys(new Set()).next;\n\nmodule.exports = function (set, fn, interruptible) {\n  return interruptible ? iterateSimple({ iterator: keys(set), next: next }, fn) : forEach(set, fn);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-method-accept-set-like.js\nvar set_method_accept_set_like_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar getBuiltIn = (get_built_in_namespaceFn());\n\nvar createSetLike = function (size) {\n  return {\n    size: size,\n    has: function () {\n      return false;\n    },\n    keys: function () {\n      return {\n        next: function () {\n          return { done: true };\n        }\n      };\n    }\n  };\n};\n\nvar createSetLikeWithInfinitySize = function (size) {\n  return {\n    size: size,\n    has: function () {\n      return true;\n    },\n    keys: function () {\n      throw new Error('e');\n    }\n  };\n};\n\nmodule.exports = function (name, callback) {\n  var Set = getBuiltIn('Set');\n  try {\n    new Set()[name](createSetLike(0));\n    try {\n      // late spec change, early WebKit ~ Safari 17 implementation does not pass it\n      // https://github.com/tc39/proposal-set-methods/pull/88\n      // also covered engines with\n      // https://bugs.webkit.org/show_bug.cgi?id=272679\n      new Set()[name](createSetLike(-1));\n      return false;\n    } catch (error2) {\n      if (!callback) return true;\n      // early V8 implementation bug\n      // https://issues.chromium.org/issues/351332634\n      try {\n        new Set()[name](createSetLikeWithInfinitySize(-Infinity));\n        return false;\n      } catch (error) {\n        var set = new Set([1, 2]);\n        return callback(set[name](createSetLikeWithInfinitySize(Infinity)));\n      }\n    }\n  } catch (error) {\n    return false;\n  }\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/set-method-get-keys-before-cloning-detection.js\nvar set_method_get_keys_before_cloning_detection_namespaceFn = () => {\n\treturn __webpack_require__(835);\n};\n\n// MODULE: ./node_modules/core-js/internals/set-size.js\nvar set_size_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThisAccessor = (function_uncurry_this_accessor_namespaceFn());\nvar SetHelpers = (set_helpers_namespaceFn());\n\nmodule.exports = uncurryThisAccessor(SetHelpers.proto, 'size', 'get') || function (set) {\n  return set.size;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-symmetric-difference.js\nvar set_symmetric_difference_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar SetHelpers = (set_helpers_namespaceFn());\nvar clone = (set_clone_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\n\nvar add = SetHelpers.add;\nvar has = SetHelpers.has;\nvar remove = SetHelpers.remove;\n\n// `Set.prototype.symmetricDifference` method\n// https://tc39.es/ecma262/#sec-set.prototype.symmetricdifference\nmodule.exports = function symmetricDifference(other) {\n  var O = aSet(this);\n  var keysIter = getSetRecord(other).getIterator();\n  var result = clone(O);\n  iterateSimple(keysIter, function (e) {\n    if (has(O, e)) remove(result, e);\n    else add(result, e);\n  });\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/set-union.js\nvar set_union_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar aSet = (a_set_namespaceFn());\nvar add = (set_helpers_namespaceFn().add);\nvar clone = (set_clone_namespaceFn());\nvar getSetRecord = (get_set_record_namespaceFn());\nvar iterateSimple = (iterate_simple_namespaceFn());\n\n// `Set.prototype.union` method\n// https://tc39.es/ecma262/#sec-set.prototype.union\nmodule.exports = function union(other) {\n  var O = aSet(this);\n  var keysIter = getSetRecord(other).getIterator();\n  var result = clone(O);\n  iterateSimple(keysIter, function (it) {\n    add(result, it);\n  });\n  return result;\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/shared-key.js\nvar shared_key_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar shared = (shared_namespaceFn());\nvar uid = (uid_namespaceFn());\n\nvar keys = shared('keys');\n\nmodule.exports = function (key) {\n  return keys[key] || (keys[key] = uid(key));\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/shared-store.js\nvar shared_store_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar IS_PURE = (is_pure_namespaceFn());\nvar globalThis = (global_this_namespaceFn());\nvar defineGlobalProperty = (define_global_property_namespaceFn());\n\nvar SHARED = '__core-js_shared__';\nvar store = module.exports = globalThis[SHARED] || defineGlobalProperty(SHARED, {});\n\n(store.versions || (store.versions = [])).push({\n  version: '3.50.0',\n  mode: IS_PURE ? 'pure' : 'global',\n  copyright: '© 2013–2025 Denis Pushkarev (zloirock.ru), 2025–2026 CoreJS Company (core-js.io). All rights reserved.',\n  license: 'https://github.com/zloirock/core-js/blob/v3.50.0/LICENSE',\n  source: 'https://github.com/zloirock/core-js'\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/shared.js\nvar shared_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar store = (shared_store_namespaceFn());\n// eslint-disable-next-line es/no-object-create -- safe\nvar create = Object.create || Object;\n\nmodule.exports = function (key, value) {\n  return store[key] || (store[key] = value || create(null));\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/symbol-constructor-detection.js\nvar symbol_constructor_detection_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n/* eslint-disable es/no-symbol -- required for testing */\nvar V8_VERSION = (environment_v8_version_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar globalThis = (global_this_namespaceFn());\n\nvar $String = globalThis.String;\n\n// eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing\nmodule.exports = !!Object.getOwnPropertySymbols && !fails(function () {\n  var symbol = Symbol('symbol detection');\n  // Chrome 38 Symbol has incorrect toString conversion\n  // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances\n  // nb: Do not call `String` directly to avoid this being optimized out to `symbol+''` which will,\n  // of course, fail.\n  return !$String(symbol) || !(Object(symbol) instanceof Symbol) ||\n    // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances\n    !Symbol.sham && V8_VERSION && V8_VERSION < 41;\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/this-number-value.js\nvar this_number_value_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\n// `thisNumberValue` abstract operation\n// https://tc39.es/ecma262/#sec-thisnumbervalue\nmodule.exports = uncurryThis(1.1.valueOf);\n\n});\n\n// MODULE: ./node_modules/core-js/internals/to-absolute-index.js\nvar to_absolute_index_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar toIntegerOrInfinity = (to_integer_or_infinity_namespaceFn());\n\nvar max = Math.max;\nvar min = Math.min;\n\n// Helper for a popular repeating case of the spec:\n// Let integer be ? ToInteger(index).\n// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).\nmodule.exports = function (index, length) {\n  var integer = toIntegerOrInfinity(index);\n  return integer < 0 ? max(integer + length, 0) : min(integer, length);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/to-indexed-object.js\nvar to_indexed_object_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n// toObject with fallback for non-array-like ES3 strings\nvar IndexedObject = (indexed_object_namespaceFn());\nvar requireObjectCoercible = (require_object_coercible_namespaceFn());\n\nmodule.exports = function (it) {\n  return IndexedObject(requireObjectCoercible(it));\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/to-integer-or-infinity.js\nvar to_integer_or_infinity_namespaceFn = () => {\n\treturn __webpack_require__(291);\n};\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/to-length.js\nvar to_length_namespaceFn = () => {\n\treturn __webpack_require__(14);\n};\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/to-object.js\nvar to_object_namespaceFn = () => {\n\treturn __webpack_require__(981);\n};\n\n// MODULE: ./node_modules/core-js/internals/to-primitive.js\nvar to_primitive_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar call = (function_call_namespaceFn());\nvar isObject = (is_object_namespaceFn());\nvar isSymbol = (is_symbol_namespaceFn());\nvar getMethod = (get_method_namespaceFn());\nvar ordinaryToPrimitive = (ordinary_to_primitive_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\n\nvar $TypeError = TypeError;\nvar TO_PRIMITIVE = wellKnownSymbol('toPrimitive');\n\n// `ToPrimitive` abstract operation\n// https://tc39.es/ecma262/#sec-toprimitive\nmodule.exports = function (input, pref) {\n  if (!isObject(input) || isSymbol(input)) return input;\n  var exoticToPrim = getMethod(input, TO_PRIMITIVE);\n  var result;\n  if (exoticToPrim) {\n    if (pref === undefined) pref = 'default';\n    result = call(exoticToPrim, input, pref);\n    if (!isObject(result) || isSymbol(result)) return result;\n    throw new $TypeError(\"Can't convert object to primitive value\");\n  }\n  if (pref === undefined) pref = 'number';\n  return ordinaryToPrimitive(input, pref);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/to-property-key.js\nvar to_property_key_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar toPrimitive = (to_primitive_namespaceFn());\nvar isSymbol = (is_symbol_namespaceFn());\n\n// `ToPropertyKey` abstract operation\n// https://tc39.es/ecma262/#sec-topropertykey\nmodule.exports = function (argument) {\n  var key = toPrimitive(argument, 'string');\n  return isSymbol(key) ? key : key + '';\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/to-string-tag-support.js\nvar to_string_tag_support_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\n\nvar TO_STRING_TAG = wellKnownSymbol('toStringTag');\nvar test = {};\n// eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation\ntest[TO_STRING_TAG] = 'z';\n\nmodule.exports = String(test) === '[object z]';\n\n});\n\n// MODULE: ./node_modules/core-js/internals/to-string.js\nvar to_string_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar classof = (classof_namespaceFn());\n\nvar $String = String;\n\nmodule.exports = function (argument) {\n  if (classof(argument) === 'Symbol') throw new TypeError('Cannot convert a Symbol value to a string');\n  return $String(argument);\n};\n\n});\n\n// EXTERNAL MODULE: ./node_modules/core-js/internals/try-to-string.js\nvar try_to_string_namespaceFn = () => {\n\treturn __webpack_require__(823);\n};\n\n// MODULE: ./node_modules/core-js/internals/uid.js\nvar uid_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\nvar id = 0;\nvar postfix = Math.random();\nvar toString = uncurryThis(1.1.toString);\n\nmodule.exports = function (key) {\n  return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString(++id + postfix, 36);\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/use-symbol-as-uid.js\nvar use_symbol_as_uid_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n/* eslint-disable es/no-symbol -- required for testing */\nvar NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());\n\nmodule.exports = NATIVE_SYMBOL &&\n  !Symbol.sham &&\n  typeof Symbol.iterator == 'symbol';\n\n});\n\n// MODULE: ./node_modules/core-js/internals/v8-prototype-define-bug.js\nvar v8_prototype_define_bug_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar fails = (fails_namespaceFn());\n\n// V8 ~ Chrome 36-\n// https://bugs.chromium.org/p/v8/issues/detail?id=3334\nmodule.exports = DESCRIPTORS && fails(function () {\n  // eslint-disable-next-line es/no-object-defineproperty -- required for testing\n  return Object.defineProperty(function () { /* empty */ }, 'prototype', {\n    value: 42,\n    writable: false\n  }).prototype !== 42;\n});\n\n});\n\n// MODULE: ./node_modules/core-js/internals/weak-map-basic-detection.js\nvar weak_map_basic_detection_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\n\nvar WeakMap = globalThis.WeakMap;\n\nmodule.exports = isCallable(WeakMap) && /native code/.test(String(WeakMap));\n\n});\n\n// MODULE: ./node_modules/core-js/internals/weak-map-helpers.js\nvar weak_map_helpers_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar uncurryThis = (function_uncurry_this_namespaceFn());\n\n// eslint-disable-next-line es/no-weak-map -- safe\nvar WeakMapPrototype = WeakMap.prototype;\n\nmodule.exports = {\n  // eslint-disable-next-line es/no-weak-map -- safe\n  WeakMap: WeakMap,\n  set: uncurryThis(WeakMapPrototype.set),\n  get: uncurryThis(WeakMapPrototype.get),\n  has: uncurryThis(WeakMapPrototype.has),\n  remove: uncurryThis(WeakMapPrototype['delete'])\n};\n\n});\n\n// MODULE: ./node_modules/core-js/internals/well-known-symbol.js\nvar well_known_symbol_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar globalThis = (global_this_namespaceFn());\nvar shared = (shared_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar uid = (uid_namespaceFn());\nvar NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());\nvar USE_SYMBOL_AS_UID = (use_symbol_as_uid_namespaceFn());\n\nvar Symbol = globalThis.Symbol;\nvar WellKnownSymbolsStore = shared('wks');\nvar createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol['for'] || Symbol : Symbol && Symbol.withoutSetter || uid;\n\nmodule.exports = function (name) {\n  if (!hasOwn(WellKnownSymbolsStore, name)) {\n    WellKnownSymbolsStore[name] = NATIVE_SYMBOL && hasOwn(Symbol, name)\n      ? Symbol[name]\n      : createWellKnownSymbol('Symbol.' + name);\n  } return WellKnownSymbolsStore[name];\n};\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.array.includes.js\nvar es_array_includes_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar $includes = (array_includes_namespaceFn().includes);\nvar fails = (fails_namespaceFn());\nvar addToUnscopables = (add_to_unscopables_namespaceFn());\n\n// FF99+ bug\nvar BROKEN_ON_SPARSE = fails(function () {\n  // eslint-disable-next-line es/no-array-prototype-includes -- detection\n  return !Array(1).includes();\n});\n\n// Safari 26.4- bug\nvar BROKEN_ON_SPARSE_WITH_FROM_INDEX = fails(function () {\n  // eslint-disable-next-line no-sparse-arrays, es/no-array-prototype-includes -- detection\n  return [, 1].includes(undefined, 1);\n});\n\n// `Array.prototype.includes` method\n// https://tc39.es/ecma262/#sec-array.prototype.includes\n$({ target: 'Array', proto: true, forced: BROKEN_ON_SPARSE || BROKEN_ON_SPARSE_WITH_FROM_INDEX }, {\n  includes: function includes(el /* , fromIndex = 0 */) {\n    return $includes(this, el, arguments.length > 1 ? arguments[1] : undefined);\n  }\n});\n\n// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables\naddToUnscopables('includes');\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.array.push.js\nvar es_array_push_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar toObject = (to_object_namespaceFn());\nvar lengthOfArrayLike = (length_of_array_like_namespaceFn());\nvar setArrayLength = (array_set_length_namespaceFn());\nvar doesNotExceedSafeInteger = (does_not_exceed_safe_integer_namespaceFn());\nvar fails = (fails_namespaceFn());\n\nvar INCORRECT_TO_LENGTH = fails(function () {\n  return [].push.call({ length: 0x100000000 }, 1) !== 4294967297;\n});\n\n// V8 <= 121 and Safari <= 15.4; FF < 23 throws InternalError\n// https://bugs.chromium.org/p/v8/issues/detail?id=12681\nvar properErrorOnNonWritableLength = function () {\n  try {\n    // eslint-disable-next-line es/no-object-defineproperty -- safe\n    Object.defineProperty([], 'length', { writable: false }).push();\n  } catch (error) {\n    return error instanceof TypeError;\n  }\n};\n\nvar FORCED = INCORRECT_TO_LENGTH || !properErrorOnNonWritableLength();\n\n// `Array.prototype.push` method\n// https://tc39.es/ecma262/#sec-array.prototype.push\n$({ target: 'Array', proto: true, arity: 1, forced: FORCED }, {\n  // eslint-disable-next-line no-unused-vars -- required for `.length`\n  push: function push(item) {\n    var O = toObject(this);\n    var len = lengthOfArrayLike(O);\n    var argCount = arguments.length;\n    doesNotExceedSafeInteger(len + argCount);\n    for (var i = 0; i < argCount; i++) {\n      O[len] = arguments[i];\n      len++;\n    }\n    setArrayLength(O, len);\n    return len;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.constructor.js\nvar es_iterator_constructor_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar globalThis = (global_this_namespaceFn());\nvar anInstance = (an_instance_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar getPrototypeOf = (object_get_prototype_of_namespaceFn());\nvar defineBuiltInAccessor = (define_built_in_accessor_namespaceFn());\nvar createProperty = (create_property_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar hasOwn = (has_own_property_namespaceFn());\nvar wellKnownSymbol = (well_known_symbol_namespaceFn());\nvar IteratorPrototype = (iterators_core_namespaceFn().IteratorPrototype);\nvar DESCRIPTORS = (descriptors_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar CONSTRUCTOR = 'constructor';\nvar ITERATOR = 'Iterator';\nvar TO_STRING_TAG = wellKnownSymbol('toStringTag');\n\nvar $TypeError = TypeError;\nvar NativeIterator = globalThis[ITERATOR];\n\n// FF56- have non-standard global helper `Iterator`\nvar FORCED = IS_PURE\n  || !isCallable(NativeIterator)\n  || NativeIterator.prototype !== IteratorPrototype\n  // FF44- non-standard `Iterator` passes previous tests\n  || !fails(function () { NativeIterator({}); });\n\nvar IteratorConstructor = function Iterator() {\n  anInstance(this, IteratorPrototype);\n  if (getPrototypeOf(this) === IteratorPrototype) throw new $TypeError('Abstract class Iterator not directly constructable');\n};\n\nvar defineIteratorPrototypeAccessor = function (key, value) {\n  if (DESCRIPTORS) {\n    defineBuiltInAccessor(IteratorPrototype, key, {\n      configurable: true,\n      get: function () {\n        return value;\n      },\n      set: function (replacement) {\n        anObject(this);\n        if (this === IteratorPrototype) throw new $TypeError(\"You can't redefine this property\");\n        if (hasOwn(this, key)) this[key] = replacement;\n        else createProperty(this, key, replacement);\n      }\n    });\n  } else IteratorPrototype[key] = value;\n};\n\nif (!hasOwn(IteratorPrototype, TO_STRING_TAG)) defineIteratorPrototypeAccessor(TO_STRING_TAG, ITERATOR);\n\nif (FORCED || !hasOwn(IteratorPrototype, CONSTRUCTOR) || IteratorPrototype[CONSTRUCTOR] === Object) {\n  defineIteratorPrototypeAccessor(CONSTRUCTOR, IteratorConstructor);\n}\n\nIteratorConstructor.prototype = IteratorPrototype;\n\n// `Iterator` constructor\n// https://tc39.es/ecma262/#sec-iterator\n$({ global: true, constructor: true, forced: FORCED }, {\n  Iterator: IteratorConstructor\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.every.js\nvar es_iterator_every_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar iterate = (iterate_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\n\nvar everyWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError('every', TypeError);\n\n// `Iterator.prototype.every` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.every\n$({ target: 'Iterator', proto: true, real: true, forced: everyWithoutClosingOnEarlyError }, {\n  every: function every(predicate) {\n    anObject(this);\n    try {\n      aCallable(predicate);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    if (everyWithoutClosingOnEarlyError) return call(everyWithoutClosingOnEarlyError, this, predicate);\n\n    var record = getIteratorDirect(this);\n    var counter = 0;\n    return !iterate(record, function (value, stop) {\n      if (!predicate(value, counter++)) return stop();\n    }, { IS_RECORD: true, INTERRUPTED: true }).stopped;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.filter.js\nvar es_iterator_filter_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar createIteratorProxy = (iterator_create_proxy_namespaceFn());\nvar callWithSafeIterationClosing = (call_with_safe_iteration_closing_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperThrowsOnInvalidIterator = (iterator_helper_throws_on_invalid_iterator_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\n\nvar FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator('filter', function () { /* empty */ });\nvar filterWithoutClosingOnEarlyError = !IS_PURE && !FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR\n  && iteratorHelperWithoutClosingOnEarlyError('filter', TypeError);\n\nvar FORCED = IS_PURE || FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR || filterWithoutClosingOnEarlyError;\n\nvar IteratorProxy = createIteratorProxy(function () {\n  var iterator = this.iterator;\n  var predicate = this.predicate;\n  var next = this.next;\n  var result, done, value;\n  while (true) {\n    result = anObject(call(next, iterator));\n    done = this.done = !!result.done;\n    if (done) return;\n    value = result.value;\n    if (callWithSafeIterationClosing(iterator, predicate, [value, this.counter++], true)) return value;\n  }\n});\n\n// `Iterator.prototype.filter` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.filter\n$({ target: 'Iterator', proto: true, real: true, forced: FORCED }, {\n  filter: function filter(predicate) {\n    anObject(this);\n    try {\n      aCallable(predicate);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    if (filterWithoutClosingOnEarlyError) return call(filterWithoutClosingOnEarlyError, this, predicate);\n\n    return new IteratorProxy(getIteratorDirect(this), {\n      predicate: predicate\n    });\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.for-each.js\nvar es_iterator_for_each_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar iterate = (iterate_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\n\nvar forEachWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError('forEach', TypeError);\n\n// `Iterator.prototype.forEach` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.foreach\n$({ target: 'Iterator', proto: true, real: true, forced: forEachWithoutClosingOnEarlyError }, {\n  forEach: function forEach(fn) {\n    anObject(this);\n    try {\n      aCallable(fn);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    if (forEachWithoutClosingOnEarlyError) return call(forEachWithoutClosingOnEarlyError, this, fn);\n\n    var record = getIteratorDirect(this);\n    var counter = 0;\n    iterate(record, function (value) {\n      fn(value, counter++);\n    }, { IS_RECORD: true });\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.map.js\nvar es_iterator_map_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar createIteratorProxy = (iterator_create_proxy_namespaceFn());\nvar callWithSafeIterationClosing = (call_with_safe_iteration_closing_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperThrowsOnInvalidIterator = (iterator_helper_throws_on_invalid_iterator_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator('map', function () { /* empty */ });\nvar mapWithoutClosingOnEarlyError = !IS_PURE && !MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR\n  && iteratorHelperWithoutClosingOnEarlyError('map', TypeError);\n\nvar FORCED = IS_PURE || MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR || mapWithoutClosingOnEarlyError;\n\nvar IteratorProxy = createIteratorProxy(function () {\n  var iterator = this.iterator;\n  var result = anObject(call(this.next, iterator));\n  var done = this.done = !!result.done;\n  if (!done) return callWithSafeIterationClosing(iterator, this.mapper, [result.value, this.counter++], true);\n});\n\n// `Iterator.prototype.map` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.map\n$({ target: 'Iterator', proto: true, real: true, forced: FORCED }, {\n  map: function map(mapper) {\n    anObject(this);\n    try {\n      aCallable(mapper);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    if (mapWithoutClosingOnEarlyError) return call(mapWithoutClosingOnEarlyError, this, mapper);\n\n    return new IteratorProxy(getIteratorDirect(this), {\n      mapper: mapper\n    });\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.reduce.js\nvar es_iterator_reduce_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar iterate = (iterate_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\nvar apply = (function_apply_namespaceFn());\nvar fails = (fails_namespaceFn());\n\nvar $TypeError = TypeError;\n\n// https://bugs.webkit.org/show_bug.cgi?id=291651\nvar FAILS_ON_INITIAL_UNDEFINED = fails(function () {\n  // eslint-disable-next-line es/no-iterator-prototype-reduce, es/no-array-prototype-keys, array-callback-return -- required for testing\n  [].keys().reduce(function () { /* empty */ }, undefined);\n});\n\nvar reduceWithoutClosingOnEarlyError = !FAILS_ON_INITIAL_UNDEFINED && iteratorHelperWithoutClosingOnEarlyError('reduce', $TypeError);\n\n// `Iterator.prototype.reduce` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.reduce\n$({ target: 'Iterator', proto: true, real: true, forced: FAILS_ON_INITIAL_UNDEFINED || reduceWithoutClosingOnEarlyError }, {\n  reduce: function reduce(reducer /* , initialValue */) {\n    anObject(this);\n    try {\n      aCallable(reducer);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    var noInitial = arguments.length < 2;\n    var accumulator = noInitial ? undefined : arguments[1];\n    if (reduceWithoutClosingOnEarlyError) {\n      return apply(reduceWithoutClosingOnEarlyError, this, noInitial ? [reducer] : [reducer, accumulator]);\n    }\n    var record = getIteratorDirect(this);\n    var counter = 0;\n    iterate(record, function (value) {\n      if (noInitial) {\n        noInitial = false;\n        accumulator = value;\n      } else {\n        accumulator = reducer(accumulator, value, counter);\n      }\n      counter++;\n    }, { IS_RECORD: true });\n    if (noInitial) throw new $TypeError('Reduce of empty iterator with no initial value');\n    return accumulator;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.iterator.some.js\nvar es_iterator_some_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar iterate = (iterate_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar anObject = (an_object_namespaceFn());\nvar getIteratorDirect = (get_iterator_direct_namespaceFn());\nvar iteratorClose = (iterator_close_namespaceFn());\nvar iteratorHelperWithoutClosingOnEarlyError = (iterator_helper_without_closing_on_early_error_namespaceFn());\n\nvar someWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError('some', TypeError);\n\n// `Iterator.prototype.some` method\n// https://tc39.es/ecma262/#sec-iterator.prototype.some\n$({ target: 'Iterator', proto: true, real: true, forced: someWithoutClosingOnEarlyError }, {\n  some: function some(predicate) {\n    anObject(this);\n    try {\n      aCallable(predicate);\n    } catch (error) {\n      iteratorClose(this, 'throw', error);\n    }\n\n    if (someWithoutClosingOnEarlyError) return call(someWithoutClosingOnEarlyError, this, predicate);\n\n    var record = getIteratorDirect(this);\n    var counter = 0;\n    return iterate(record, function (value, stop) {\n      if (predicate(value, counter++)) return stop();\n    }, { IS_RECORD: true, INTERRUPTED: true }).stopped;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.json.stringify.js\nvar es_json_stringify_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar getBuiltIn = (get_built_in_namespaceFn());\nvar call = (function_call_namespaceFn());\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar isArray = (is_array_namespaceFn());\nvar isCallable = (is_callable_namespaceFn());\nvar isObject = (is_object_namespaceFn());\nvar create = (object_create_namespaceFn());\nvar isRawJSON = (is_raw_json_namespaceFn());\nvar isSymbol = (is_symbol_namespaceFn());\nvar classof = (classof_raw_namespaceFn());\nvar thisNumberValue = (this_number_value_namespaceFn());\nvar includes = (array_includes_namespaceFn().includes);\nvar hasOwn = (has_own_property_namespaceFn());\nvar toString = (to_string_namespaceFn());\nvar parseJSONString = (parse_json_string_namespaceFn());\nvar uid = (uid_namespaceFn());\nvar NATIVE_SYMBOL = (symbol_constructor_detection_namespaceFn());\nvar NATIVE_RAW_JSON = (native_raw_json_namespaceFn());\n\nvar $String = String;\nvar $TypeError = TypeError;\nvar $stringify = getBuiltIn('JSON', 'stringify');\nvar $BigInt = getBuiltIn('BigInt');\nvar stringValueOf = uncurryThis(''.valueOf);\nvar booleanValueOf = uncurryThis(true.valueOf);\nvar bigIntValueOf = $BigInt && uncurryThis($BigInt.prototype.valueOf);\nvar exec = uncurryThis(/./.exec);\nvar charAt = uncurryThis(''.charAt);\nvar charCodeAt = uncurryThis(''.charCodeAt);\nvar replace = uncurryThis(''.replace);\nvar slice = uncurryThis(''.slice);\nvar push = uncurryThis([].push);\nvar pop = uncurryThis([].pop);\nvar numberToString = uncurryThis(1.1.toString);\n\nvar surrogates = /[\\uD800-\\uDFFF]/g;\nvar leadingSurrogates = /^[\\uD800-\\uDBFF]$/;\nvar trailingSurrogates = /^[\\uDC00-\\uDFFF]$/;\nvar digits = /^\\d+$/;\n\n// a placeholder of a raw JSON value\nvar RAW_MARK = uid();\n// a prefix of keys of a reordered object, see `createOrderedObject`\nvar KEY_MARK = uid();\n// the last key of a reordered object, marks the end of its serialization\nvar END_MARK = uid();\nvar RAW_MARK_LENGTH = RAW_MARK.length;\nvar KEY_MARK_LENGTH = KEY_MARK.length;\n\nvar WRONG_SYMBOLS_CONVERSION = !NATIVE_SYMBOL || fails(function () {\n  var symbol = getBuiltIn('Symbol')('stringify detection');\n  // MS Edge converts symbol values to JSON as {}\n  return $stringify([symbol]) !== '[null]'\n    // WebKit converts symbol values to JSON as null\n    || $stringify({ a: symbol }) !== '{}'\n    // V8 throws on boxed symbols\n    || $stringify(Object(symbol)) !== '{}';\n});\n\n// https://github.com/tc39/proposal-well-formed-stringify\nvar ILL_FORMED_UNICODE = fails(function () {\n  return $stringify('\\uDF06\\uD834') !== '\"\\\\udf06\\\\ud834\"'\n    || $stringify('\\uDEAD') !== '\"\\\\udead\"';\n});\n\nvar isRawJSONValue = NATIVE_RAW_JSON ? getBuiltIn('JSON', 'isRawJSON') : isRawJSON;\n\nvar stringifyWithProperSymbolsConversion = WRONG_SYMBOLS_CONVERSION ? function (it, replacer, space) {\n  return $stringify(it, function (key, value) {\n    var replaced = call(replacer, this, key, value);\n    if (!isSymbol(replaced)) return replaced;\n  }, space);\n} : $stringify;\n\nvar fixIllFormedJSON = function (match, offset, string) {\n  var prev = charAt(string, offset - 1);\n  var next = charAt(string, offset + 1);\n  if (\n    (exec(leadingSurrogates, match) && !exec(trailingSurrogates, next)) ||\n    (exec(trailingSurrogates, match) && !exec(leadingSurrogates, prev))\n  ) {\n    return '\\\\u' + numberToString(charCodeAt(match, 0), 16);\n  } return match;\n};\n\n// `PropertyList` of `JSON.stringify`\n// https://tc39.es/ecma262/#sec-json.stringify\nvar getPropertyList = function (replacer) {\n  if (!isArray(replacer)) return;\n  var rawLength = replacer.length;\n  var propertyList = [];\n  // a null prototype object is used as a set of already added keys to keep the deduplication linear\n  var addedKeys = create(null);\n  for (var i = 0; i < rawLength; i++) {\n    var element = replacer[i];\n    var key;\n    if (typeof element == 'string') key = element;\n    else if (typeof element == 'number' || classof(element) === 'Number' || classof(element) === 'String') key = toString(element);\n    else continue;\n    if (!hasOwn(addedKeys, key)) {\n      addedKeys[key] = true;\n      push(propertyList, key);\n    }\n  }\n  return propertyList;\n};\n\n// values with such an internal slot are unwrapped by `SerializeJSONProperty` instead of being serialized as objects\nvar hasInternalSlot = function (valueOf, it) {\n  try {\n    valueOf(it);\n    return true;\n  } catch (error) {\n    return false;\n  }\n};\n\n// the slot check is expensive, so it's performed only for the kind reported by the value itself -\n// a value lying about its kind via `Symbol.toStringTag` is serialized as an ordinary object\nvar isBoxedPrimitive = function (it) {\n  var kind = classof(it);\n  return (kind === 'Number' && hasInternalSlot(thisNumberValue, it))\n    || (kind === 'String' && hasInternalSlot(stringValueOf, it))\n    || (kind === 'Boolean' && hasInternalSlot(booleanValueOf, it))\n    || (!!bigIntValueOf && kind === 'BigInt' && hasInternalSlot(bigIntValueOf, it));\n};\n\n// only objects serialized by `SerializeJSONObject` are affected by the property list\nvar isSerializedAsObject = function (it) {\n  if (!isObject(it) || isCallable(it) || isArray(it)) return false;\n  try {\n    return !isBoxedPrimitive(it);\n  // `classof` reads `Symbol.toStringTag`, so a proxy could throw - it has no internal slots anyway\n  } catch (error) {\n    return true;\n  }\n};\n\n// the engine unwraps it in the same order as it would read the original property,\n// so the property is read lazily and `toJSON` is called once and with the original key\nvar createElementHolder = function (holder, key) {\n  return {\n    toJSON: function () {\n      var element = holder[key];\n      if (isObject(element) || typeof element == 'bigint') {\n        var elementToJSON = element.toJSON;\n        if (isCallable(elementToJSON)) element = call(elementToJSON, element, key);\n      } return element;\n    }\n  };\n};\n\n// own keys of objects are sorted - integer-like keys are moved to the beginning,\n// so such keys should be marked and restored in the serialized string\nvar getKeyPrefix = function (propertyList) {\n  for (var i = 0, length = propertyList.length; i < length; i++) {\n    if (exec(digits, propertyList[i])) return KEY_MARK;\n  } return '';\n};\n\n// `SerializeJSONObject` iterates the property list, so the value is replaced with an object with keys in this order\nvar createOrderedObject = function (value, propertyList, keyPrefix) {\n  // keys are not marked if the property list has no integer-like keys, so `Object.prototype`\n  // with a setter, a non-writable property or `__proto__` should not intercept the assignment\n  var ordered = create(null);\n  for (var i = 0, length = propertyList.length; i < length; i++) {\n    var key = propertyList[i];\n    ordered[keyPrefix + key] = createElementHolder(value, key);\n  }\n  ordered[END_MARK] = null;\n  return ordered;\n};\n\n// `JSON.stringify` method\n// https://tc39.es/ecma262/#sec-json.stringify\n// https://github.com/tc39/proposal-json-parse-with-source\nif ($stringify) $({ target: 'JSON', stat: true, arity: 3, forced: WRONG_SYMBOLS_CONVERSION || ILL_FORMED_UNICODE || !NATIVE_RAW_JSON }, {\n  stringify: function stringify(text, replacer, space) {\n    var replacerFunction = isCallable(replacer) ? replacer : undefined;\n    var propertyList = replacerFunction ? undefined : getPropertyList(replacer);\n    var keyPrefix = propertyList && getKeyPrefix(propertyList);\n    var rawStrings = [];\n    var openObjects = [];\n    var parentOrdered = [];\n    var currentOrdered;\n    var marked = false;\n    var root = true;\n\n    var json = stringifyWithProperSymbolsConversion(text, function (key, value) {\n      // some old implementations (like WebKit) could pass numbers as keys\n      key = $String(key);\n\n      if (propertyList) {\n        if (key === END_MARK) {\n          pop(openObjects);\n          currentOrdered = pop(parentOrdered);\n          return;\n        }\n        if (root) root = false;\n        // the innermost reordered object already contains only keys of the property list and arrays are not\n        // affected by it, the rest of objects (like objects with a fake `Symbol.toStringTag`) are filtered here\n        else if (this !== currentOrdered && !isArray(this) && !includes(propertyList, key)) return;\n      } else if (replacerFunction) value = call(replacerFunction, this, key, value);\n\n      if (isRawJSONValue(value)) {\n        if (NATIVE_RAW_JSON) return value;\n        marked = true;\n        return RAW_MARK + (push(rawStrings, value.rawJSON) - 1);\n      }\n\n      if (propertyList && isSerializedAsObject(value)) {\n        // reordered objects are new each time, so cycles should be detected before the engine does it\n        if (includes(openObjects, value)) throw new $TypeError('Converting circular structure to JSON');\n        var ordered = createOrderedObject(value, propertyList, keyPrefix);\n        push(openObjects, value);\n        push(parentOrdered, currentOrdered);\n        currentOrdered = ordered;\n        if (keyPrefix) marked = true;\n        return ordered;\n      }\n\n      return value;\n    }, space);\n\n    if (typeof json != 'string') return json;\n\n    if (ILL_FORMED_UNICODE) json = replace(json, surrogates, fixIllFormedJSON);\n\n    if (!marked) return json;\n\n    var result = '';\n    var length = json.length;\n\n    for (var i = 0; i < length; i++) {\n      var chr = charAt(json, i);\n      if (chr === '\"') {\n        var end = parseJSONString(json, ++i).end - 1;\n        var string = slice(json, i, end);\n        if (slice(string, 0, RAW_MARK_LENGTH) === RAW_MARK) result += rawStrings[slice(string, RAW_MARK_LENGTH)];\n        else if (slice(string, 0, KEY_MARK_LENGTH) === KEY_MARK) result += '\"' + slice(string, KEY_MARK_LENGTH) + '\"';\n        else result += '\"' + string + '\"';\n        i = end;\n      } else result += chr;\n    }\n\n    return result;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.map.get-or-insert-computed.js\nvar es_map_get_or_insert_computed_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar MapHelpers = (map_helpers_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar get = MapHelpers.get;\nvar has = MapHelpers.has;\nvar set = MapHelpers.set;\n\n// `Map.prototype.getOrInsertComputed` method\n// https://tc39.es/ecma262/#sec-map.prototype.getorinsertcomputed\n$({ target: 'Map', proto: true, real: true, forced: IS_PURE }, {\n  getOrInsertComputed: function getOrInsertComputed(key, callbackfn) {\n    var hasKey = has(this, key);\n    aCallable(callbackfn);\n    if (hasKey) return get(this, key);\n    // CanonicalizeKeyedCollectionKey\n    if (key === 0 && 1 / key === -Infinity) key = 0;\n    var value = callbackfn(key);\n    set(this, key, value);\n    return value;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.map.get-or-insert.js\nvar es_map_get_or_insert_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar MapHelpers = (map_helpers_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar get = MapHelpers.get;\nvar has = MapHelpers.has;\nvar set = MapHelpers.set;\n\n// `Map.prototype.getOrInsert` method\n// https://tc39.es/ecma262/#sec-map.prototype.getorinsert\n$({ target: 'Map', proto: true, real: true, forced: IS_PURE }, {\n  getOrInsert: function getOrInsert(key, value) {\n    if (has(this, key)) return get(this, key);\n    set(this, key, value);\n    return value;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.math.sum-precise.js\nvar es_math_sum_precise_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\n// based on Shewchuk's algorithm for exactly floating point addition\n// adapted from https://github.com/tc39/proposal-math-sum/blob/3513d58323a1ae25560e8700aa5294500c6c9287/polyfill/polyfill.mjs\nvar $ = (export_namespaceFn());\nvar uncurryThis = (function_uncurry_this_namespaceFn());\nvar iterate = (iterate_namespaceFn());\n\nvar $RangeError = RangeError;\nvar $TypeError = TypeError;\nvar $Infinity = Infinity;\nvar $NaN = NaN;\nvar abs = Math.abs;\nvar pow = Math.pow;\nvar push = uncurryThis([].push);\n\nvar POW_2_1023 = pow(2, 1023);\nvar MAX_SAFE_INTEGER = pow(2, 53) - 1; // 2 ** 53 - 1 === 9007199254740991\nvar MAX_DOUBLE = Number.MAX_VALUE; // 2 ** 1024 - 2 ** (1023 - 52) === 1.79769313486231570815e+308\nvar MAX_ULP = pow(2, 971); // 2 ** (1023 - 52) === 1.99584030953471981166e+292\n\nvar NOT_A_NUMBER = {};\nvar MINUS_INFINITY = {};\nvar PLUS_INFINITY = {};\nvar MINUS_ZERO = {};\nvar FINITE = {};\n\n// prerequisite: abs(x) >= abs(y)\nvar twosum = function (x, y) {\n  var hi = x + y;\n  var lo = y - (hi - x);\n  return { hi: hi, lo: lo };\n};\n\n// `Math.sumPrecise` method\n// https://tc39.es/ecma262/#sec-math.sumprecise\n$({ target: 'Math', stat: true }, {\n  // eslint-disable-next-line max-statements -- ok\n  sumPrecise: function sumPrecise(items) {\n    var numbers = [];\n    var count = 0;\n    var state = MINUS_ZERO;\n\n    iterate(items, function (n) {\n      if (++count > MAX_SAFE_INTEGER) throw new $RangeError('Maximum allowed index exceeded');\n      if (typeof n != 'number') throw new $TypeError('Value is not a number');\n      if (state !== NOT_A_NUMBER) {\n        // eslint-disable-next-line no-self-compare -- NaN check\n        if (n !== n) state = NOT_A_NUMBER;\n        else if (n === $Infinity) state = state === MINUS_INFINITY ? NOT_A_NUMBER : PLUS_INFINITY;\n        else if (n === -$Infinity) state = state === PLUS_INFINITY ? NOT_A_NUMBER : MINUS_INFINITY;\n        else if ((n !== 0 || (1 / n) === $Infinity) && (state === MINUS_ZERO || state === FINITE)) {\n          state = FINITE;\n          push(numbers, n);\n        }\n      }\n    });\n\n    switch (state) {\n      case NOT_A_NUMBER: return $NaN;\n      case MINUS_INFINITY: return -$Infinity;\n      case PLUS_INFINITY: return $Infinity;\n      case MINUS_ZERO: return -0;\n    }\n\n    var partials = [];\n    var overflow = 0; // conceptually 2 ** 1024 times this value; the final partial is biased by this amount\n    var x, y, sum, hi, lo, tmp;\n\n    for (var i = 0; i < numbers.length; i++) {\n      x = numbers[i];\n      var actuallyUsedPartials = 0;\n      for (var j = 0; j < partials.length; j++) {\n        y = partials[j];\n        if (abs(x) < abs(y)) {\n          tmp = x;\n          x = y;\n          y = tmp;\n        }\n        sum = twosum(x, y);\n        hi = sum.hi;\n        lo = sum.lo;\n        if (abs(hi) === $Infinity) {\n          var sign = hi === $Infinity ? 1 : -1;\n          overflow += sign;\n\n          x = (x - (sign * POW_2_1023)) - (sign * POW_2_1023);\n          if (abs(x) < abs(y)) {\n            tmp = x;\n            x = y;\n            y = tmp;\n          }\n          sum = twosum(x, y);\n          hi = sum.hi;\n          lo = sum.lo;\n        }\n        if (lo !== 0) partials[actuallyUsedPartials++] = lo;\n        x = hi;\n      }\n      partials.length = actuallyUsedPartials;\n      if (x !== 0) push(partials, x);\n    }\n\n    // compute the exact sum of partials, stopping once we lose precision\n    var n = partials.length - 1;\n    hi = 0;\n    lo = 0;\n\n    if (overflow !== 0) {\n      var next = n >= 0 ? partials[n] : 0;\n      n--;\n      if (abs(overflow) > 1 || (overflow > 0 && next > 0) || (overflow < 0 && next < 0)) {\n        return overflow > 0 ? $Infinity : -$Infinity;\n      }\n      // here we actually have to do the arithmetic\n      // drop a factor of 2 so we can do it without overflow\n      // assert(abs(overflow) === 1)\n      sum = twosum(overflow * POW_2_1023, next / 2);\n      hi = sum.hi;\n      lo = sum.lo;\n      lo *= 2;\n      if (abs(2 * hi) === $Infinity) {\n        // rounding to the maximum value\n        if (hi > 0) {\n          return (hi === POW_2_1023 && lo === -(MAX_ULP / 2) && n >= 0 && partials[n] < 0) ? MAX_DOUBLE : $Infinity;\n        } return (hi === -POW_2_1023 && lo === (MAX_ULP / 2) && n >= 0 && partials[n] > 0) ? -MAX_DOUBLE : -$Infinity;\n      }\n\n      if (lo !== 0) {\n        partials[++n] = lo;\n        lo = 0;\n      }\n\n      hi *= 2;\n    }\n\n    while (n >= 0) {\n      sum = twosum(hi, partials[n--]);\n      hi = sum.hi;\n      lo = sum.lo;\n      if (lo !== 0) break;\n    }\n\n    if (n >= 0 && ((lo < 0 && partials[n] < 0) || (lo > 0 && partials[n] > 0))) {\n      y = lo * 2;\n      x = hi + y;\n      if (y === x - hi) hi = x;\n    }\n\n    return hi;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.difference.v2.js\nvar es_set_difference_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar difference = (set_difference_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar SET_LIKE_INCORRECT_BEHAVIOR = !setMethodAcceptSetLike('difference', function (result) {\n  return result.size === 0;\n});\n\nvar FORCED = SET_LIKE_INCORRECT_BEHAVIOR || fails(function () {\n  // https://bugs.webkit.org/show_bug.cgi?id=288595\n  var setLike = {\n    size: 1,\n    has: function () { return true; },\n    keys: function () {\n      var index = 0;\n      return {\n        next: function () {\n          var done = index++ > 1;\n          if (baseSet.has(1)) baseSet.clear();\n          return { done: done, value: 2 };\n        }\n      };\n    }\n  };\n  // eslint-disable-next-line es/no-set -- testing\n  var baseSet = new Set([1, 2, 3, 4]);\n  // eslint-disable-next-line es/no-set-prototype-difference -- testing\n  return baseSet.difference(setLike).size !== 3;\n});\n\n// `Set.prototype.difference` method\n// https://tc39.es/ecma262/#sec-set.prototype.difference\n$({ target: 'Set', proto: true, real: true, forced: FORCED }, {\n  difference: difference\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.intersection.v2.js\nvar es_set_intersection_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar fails = (fails_namespaceFn());\nvar intersection = (set_intersection_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar INCORRECT = !setMethodAcceptSetLike('intersection', function (result) {\n  return result.size === 2 && result.has(1) && result.has(2);\n}) || fails(function () {\n  // eslint-disable-next-line es/no-array-from, es/no-set, es/no-set-prototype-intersection -- testing\n  return String(Array.from(new Set([1, 2, 3]).intersection(new Set([3, 2])))) !== '3,2';\n});\n\n// `Set.prototype.intersection` method\n// https://tc39.es/ecma262/#sec-set.prototype.intersection\n$({ target: 'Set', proto: true, real: true, forced: INCORRECT }, {\n  intersection: intersection\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.is-disjoint-from.v2.js\nvar es_set_is_disjoint_from_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar isDisjointFrom = (set_is_disjoint_from_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar INCORRECT = !setMethodAcceptSetLike('isDisjointFrom', function (result) {\n  return !result;\n});\n\n// `Set.prototype.isDisjointFrom` method\n// https://tc39.es/ecma262/#sec-set.prototype.isdisjointfrom\n$({ target: 'Set', proto: true, real: true, forced: INCORRECT }, {\n  isDisjointFrom: isDisjointFrom\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.is-subset-of.v2.js\nvar es_set_is_subset_of_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar isSubsetOf = (set_is_subset_of_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar INCORRECT = !setMethodAcceptSetLike('isSubsetOf', function (result) {\n  return result;\n});\n\n// `Set.prototype.isSubsetOf` method\n// https://tc39.es/ecma262/#sec-set.prototype.issubsetof\n$({ target: 'Set', proto: true, real: true, forced: INCORRECT }, {\n  isSubsetOf: isSubsetOf\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.is-superset-of.v2.js\nvar es_set_is_superset_of_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar isSupersetOf = (set_is_superset_of_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar INCORRECT = !setMethodAcceptSetLike('isSupersetOf', function (result) {\n  return !result;\n});\n\n// `Set.prototype.isSupersetOf` method\n// https://tc39.es/ecma262/#sec-set.prototype.issupersetof\n$({ target: 'Set', proto: true, real: true, forced: INCORRECT }, {\n  isSupersetOf: isSupersetOf\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.symmetric-difference.v2.js\nvar es_set_symmetric_difference_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar symmetricDifference = (set_symmetric_difference_namespaceFn());\nvar setMethodGetKeysBeforeCloning = (set_method_get_keys_before_cloning_detection_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar FORCED = !setMethodAcceptSetLike('symmetricDifference') || !setMethodGetKeysBeforeCloning('symmetricDifference');\n\n// `Set.prototype.symmetricDifference` method\n// https://tc39.es/ecma262/#sec-set.prototype.symmetricdifference\n$({ target: 'Set', proto: true, real: true, forced: FORCED }, {\n  symmetricDifference: symmetricDifference\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.set.union.v2.js\nvar es_set_union_v2_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar union = (set_union_namespaceFn());\nvar setMethodGetKeysBeforeCloning = (set_method_get_keys_before_cloning_detection_namespaceFn());\nvar setMethodAcceptSetLike = (set_method_accept_set_like_namespaceFn());\n\nvar FORCED = !setMethodAcceptSetLike('union') || !setMethodGetKeysBeforeCloning('union');\n\n// `Set.prototype.union` method\n// https://tc39.es/ecma262/#sec-set.prototype.union\n$({ target: 'Set', proto: true, real: true, forced: FORCED }, {\n  union: union\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.weak-map.get-or-insert-computed.js\nvar es_weak_map_get_or_insert_computed_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar aCallable = (a_callable_namespaceFn());\nvar aWeakMap = (a_weak_map_namespaceFn());\nvar aWeakKey = (a_weak_key_namespaceFn());\nvar WeakMapHelpers = (weak_map_helpers_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar get = WeakMapHelpers.get;\nvar has = WeakMapHelpers.has;\nvar set = WeakMapHelpers.set;\n\nvar FORCED = IS_PURE || !function () {\n  try {\n    // eslint-disable-next-line es/no-weak-map, no-throw-literal -- testing\n    if (WeakMap.prototype.getOrInsertComputed) new WeakMap().getOrInsertComputed(1, function () { throw 1; });\n  } catch (error) {\n    // FF144 Nightly - Beta 3 bug\n    // https://bugzilla.mozilla.org/show_bug.cgi?id=1988369\n    return error instanceof TypeError;\n  }\n}();\n\n// `WeakMap.prototype.getOrInsertComputed` method\n// https://tc39.es/ecma262/#sec-weakmap.prototype.getorinsertcomputed\n$({ target: 'WeakMap', proto: true, real: true, forced: FORCED }, {\n  getOrInsertComputed: function getOrInsertComputed(key, callbackfn) {\n    if (!IS_PURE) aWeakMap(this);\n    aWeakKey(key);\n    aCallable(callbackfn);\n    if (has(this, key)) return get(this, key);\n    var value = callbackfn(key);\n    set(this, key, value);\n    return value;\n  }\n});\n\n});\n\n// MODULE: ./node_modules/core-js/modules/es.weak-map.get-or-insert.js\nvar es_weak_map_get_or_insert_namespaceFn = /*#__PURE__*/__webpack_require__.cw(function(module, exports) {\n\nvar $ = (export_namespaceFn());\nvar WeakMapHelpers = (weak_map_helpers_namespaceFn());\nvar IS_PURE = (is_pure_namespaceFn());\n\nvar get = WeakMapHelpers.get;\nvar has = WeakMapHelpers.has;\nvar set = WeakMapHelpers.set;\n\n// `WeakMap.prototype.getOrInsert` method\n// https://tc39.es/ecma262/#sec-weakmap.prototype.getorinsert\n$({ target: 'WeakMap', proto: true, real: true, forced: IS_PURE }, {\n  getOrInsert: function getOrInsert(key, value) {\n    if (has(this, key)) return get(this, key);\n    set(this, key, value);\n    return value;\n  }\n});\n\n});\n\n;// ./node_modules/core-js/modules/es.array.push.js\nes_array_push_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.iterator.constructor.js\nes_iterator_constructor_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.iterator.map.js\nes_iterator_map_namespaceFn();\n\n;// ./src/scripting_api/constants.js\nconst Border = Object.freeze({\n  s: \"solid\",\n  d: \"dashed\",\n  b: \"beveled\",\n  i: \"inset\",\n  u: \"underline\"\n});\nconst Cursor = Object.freeze({\n  visible: 0,\n  hidden: 1,\n  delay: 2\n});\nconst Display = Object.freeze({\n  visible: 0,\n  hidden: 1,\n  noPrint: 2,\n  noView: 3\n});\nconst Font = Object.freeze({\n  Times: \"Times-Roman\",\n  TimesB: \"Times-Bold\",\n  TimesI: \"Times-Italic\",\n  TimesBI: \"Times-BoldItalic\",\n  Helv: \"Helvetica\",\n  HelvB: \"Helvetica-Bold\",\n  HelvI: \"Helvetica-Oblique\",\n  HelvBI: \"Helvetica-BoldOblique\",\n  Cour: \"Courier\",\n  CourB: \"Courier-Bold\",\n  CourI: \"Courier-Oblique\",\n  CourBI: \"Courier-BoldOblique\",\n  Symbol: \"Symbol\",\n  ZapfD: \"ZapfDingbats\",\n  KaGo: \"HeiseiKakuGo-W5-UniJIS-UCS2-H\",\n  KaMi: \"HeiseiMin-W3-UniJIS-UCS2-H\"\n});\nconst Highlight = Object.freeze({\n  n: \"none\",\n  i: \"invert\",\n  p: \"push\",\n  o: \"outline\"\n});\nconst Position = Object.freeze({\n  textOnly: 0,\n  iconOnly: 1,\n  iconTextV: 2,\n  textIconV: 3,\n  iconTextH: 4,\n  textIconH: 5,\n  overlay: 6\n});\nconst ScaleHow = Object.freeze({\n  proportional: 0,\n  anamorphic: 1\n});\nconst ScaleWhen = Object.freeze({\n  always: 0,\n  never: 1,\n  tooBig: 2,\n  tooSmall: 3\n});\nconst Style = Object.freeze({\n  ch: \"check\",\n  cr: \"cross\",\n  di: \"diamond\",\n  ci: \"circle\",\n  st: \"star\",\n  sq: \"square\"\n});\nconst Trans = Object.freeze({\n  blindsH: \"BlindsHorizontal\",\n  blindsV: \"BlindsVertical\",\n  boxI: \"BoxIn\",\n  boxO: \"BoxOut\",\n  dissolve: \"Dissolve\",\n  glitterD: \"GlitterDown\",\n  glitterR: \"GlitterRight\",\n  glitterRD: \"GlitterRightDown\",\n  random: \"Random\",\n  replace: \"Replace\",\n  splitHI: \"SplitHorizontalIn\",\n  splitHO: \"SplitHorizontalOut\",\n  splitVI: \"SplitVerticalIn\",\n  splitVO: \"SplitVerticalOut\",\n  wipeD: \"WipeDown\",\n  wipeL: \"WipeLeft\",\n  wipeR: \"WipeRight\",\n  wipeU: \"WipeUp\"\n});\nconst ZoomType = Object.freeze({\n  none: \"NoVary\",\n  fitP: \"FitPage\",\n  fitW: \"FitWidth\",\n  fitH: \"FitHeight\",\n  fitV: \"FitVisibleWidth\",\n  pref: \"Preferred\",\n  refW: \"ReflowWidth\"\n});\nconst GlobalConstants = Object.freeze({\n  IDS_GREATER_THAN: \"Invalid value: must be greater than or equal to % s.\",\n  IDS_GT_AND_LT: \"Invalid value: must be greater than or equal to % s \" + \"and less than or equal to % s.\",\n  IDS_LESS_THAN: \"Invalid value: must be less than or equal to % s.\",\n  IDS_INVALID_MONTH: \"** Invalid **\",\n  IDS_INVALID_DATE: \"Invalid date / time: please ensure that the date / time exists. Field\",\n  IDS_INVALID_DATE2: \" should match format \",\n  IDS_INVALID_VALUE: \"The value entered does not match the format of the field\",\n  IDS_AM: \"am\",\n  IDS_PM: \"pm\",\n  IDS_MONTH_INFO: \"January[1] February[2] March[3] April[4] May[5] \" + \"June[6] July[7] August[8] September[9] October[10] \" + \"November[11] December[12] Sept[9] Jan[1] Feb[2] Mar[3] \" + \"Apr[4] Jun[6] Jul[7] Aug[8] Sep[9] Oct[10] Nov[11] Dec[12]\",\n  IDS_STARTUP_CONSOLE_MSG: \"** ^ _ ^ **\",\n  RE_NUMBER_ENTRY_DOT_SEP: [\"[+-]?\\\\d*\\\\.?\\\\d*\"],\n  RE_NUMBER_COMMIT_DOT_SEP: [\"[+-]?\\\\d+(\\\\.\\\\d+)?\", \"[+-]?\\\\.\\\\d+\", \"[+-]?\\\\d+\\\\.\"],\n  RE_NUMBER_ENTRY_COMMA_SEP: [\"[+-]?\\\\d*,?\\\\d*\"],\n  RE_NUMBER_COMMIT_COMMA_SEP: [\"[+-]?\\\\d+([.,]\\\\d+)?\", \"[+-]?[.,]\\\\d+\", \"[+-]?\\\\d+[.,]\"],\n  RE_ZIP_ENTRY: [\"\\\\d{0,5}\"],\n  RE_ZIP_COMMIT: [\"\\\\d{5}\"],\n  RE_ZIP4_ENTRY: [\"\\\\d{0,5}(\\\\.|[- ])?\\\\d{0,4}\"],\n  RE_ZIP4_COMMIT: [\"\\\\d{5}(\\\\.|[- ])?\\\\d{4}\"],\n  RE_PHONE_ENTRY: [\"\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,4}\", \"\\\\(\\\\d{0,3}\", \"\\\\(\\\\d{0,3}\\\\)(\\\\.|[- ])?\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,4}\", \"\\\\(\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,4}\", \"\\\\d{0,3}\\\\)(\\\\.|[- ])?\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,4}\", \"011(\\\\.|[- \\\\d])*\"],\n  RE_PHONE_COMMIT: [\"\\\\d{3}(\\\\.|[- ])?\\\\d{4}\", \"\\\\d{3}(\\\\.|[- ])?\\\\d{3}(\\\\.|[- ])?\\\\d{4}\", \"\\\\(\\\\d{3}\\\\)(\\\\.|[- ])?\\\\d{3}(\\\\.|[- ])?\\\\d{4}\", \"011(\\\\.|[- \\\\d])*\"],\n  RE_SSN_ENTRY: [\"\\\\d{0,3}(\\\\.|[- ])?\\\\d{0,2}(\\\\.|[- ])?\\\\d{0,4}\"],\n  RE_SSN_COMMIT: [\"\\\\d{3}(\\\\.|[- ])?\\\\d{2}(\\\\.|[- ])?\\\\d{4}\"]\n});\n\n;// ./node_modules/core-js/modules/es.iterator.every.js\nes_iterator_every_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.iterator.filter.js\nes_iterator_filter_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.iterator.for-each.js\nes_iterator_for_each_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.iterator.some.js\nes_iterator_some_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.difference.v2.js\nes_set_difference_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.intersection.v2.js\nes_set_intersection_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.is-disjoint-from.v2.js\nes_set_is_disjoint_from_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.is-subset-of.v2.js\nes_set_is_subset_of_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.is-superset-of.v2.js\nes_set_is_superset_of_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.symmetric-difference.v2.js\nes_set_symmetric_difference_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.set.union.v2.js\nes_set_union_v2_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.map.get-or-insert.js\nes_map_get_or_insert_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.map.get-or-insert-computed.js\nes_map_get_or_insert_computed_namespaceFn();\n\n;// ./src/scripting_api/common.js\n\n\nconst FieldType = {\n  none: 0,\n  number: 1,\n  percent: 2,\n  date: 3,\n  time: 4\n};\nfunction createMap(val) {\n  return val instanceof Map ? val : new Map(val ? Object.entries(val) : null);\n}\nfunction getFieldType(actions) {\n  let format = actions.get(\"Format\");\n  if (!format) {\n    return FieldType.none;\n  }\n  format = format[0].trim();\n  if (format.startsWith(\"AFNumber_\")) {\n    return FieldType.number;\n  }\n  if (format.startsWith(\"AFPercent_\")) {\n    return FieldType.percent;\n  }\n  if (format.startsWith(\"AFDate_\")) {\n    return FieldType.date;\n  }\n  return format.startsWith(\"AFTime_\") ? FieldType.time : FieldType.none;\n}\n\n;// ./src/scripting_api/app_utils.js\n\n\nconst VIEWER_TYPE = \"PDF.js\";\nconst VIEWER_VARIATION = \"Full\";\nconst VIEWER_VERSION = 21.00720099;\nconst FORMS_VERSION = 21.00720099;\nconst USERACTIVATION_CALLBACKID = 0;\nconst USERACTIVATION_MAXTIME_VALIDITY = 5000;\nfunction serializeError(error) {\n  const value = `${error.toString()}\\n${error.stack}`;\n  return {\n    command: \"error\",\n    value\n  };\n}\nconst makeArr = () => [];\nconst makeMap = () => new Map();\n\n;// ./src/shared/math_clamp.js\nfunction MathClamp(v, min, max) {\n  return Math.min(Math.max(v, min), max);\n}\n\n;// ./src/shared/scripting_utils.js\n\n\n\nfunction makeColorComp(n) {\n  return Math.floor(MathClamp(n, 0, 1) * 255).toString(16).padStart(2, \"0\");\n}\nfunction scaleAndClamp(x) {\n  return MathClamp(x, 0, 1) * 255;\n}\nclass ColorConverters {\n  static CMYK_G([c, y, m, k]) {\n    return [\"G\", 1 - Math.min(1, 0.3 * c + 0.59 * m + 0.11 * y + k)];\n  }\n  static G_CMYK([g]) {\n    return [\"CMYK\", 0, 0, 0, 1 - g];\n  }\n  static G_RGB([g]) {\n    return [\"RGB\", g, g, g];\n  }\n  static G_rgb([g]) {\n    g = scaleAndClamp(g);\n    return [g, g, g];\n  }\n  static G_HTML([g]) {\n    const G = makeColorComp(g);\n    return `#${G}${G}${G}`;\n  }\n  static RGB_G([r, g, b]) {\n    return [\"G\", 0.3 * r + 0.59 * g + 0.11 * b];\n  }\n  static RGB_rgb(color) {\n    return color.map(scaleAndClamp);\n  }\n  static RGB_HTML(color) {\n    return `#${color.map(makeColorComp).join(\"\")}`;\n  }\n  static T_HTML() {\n    return \"#00000000\";\n  }\n  static T_rgb() {\n    return [null];\n  }\n  static CMYK_RGB([c, y, m, k]) {\n    return [\"RGB\", 1 - Math.min(1, c + k), 1 - Math.min(1, m + k), 1 - Math.min(1, y + k)];\n  }\n  static CMYK_rgb([c, y, m, k]) {\n    return [scaleAndClamp(1 - Math.min(1, c + k)), scaleAndClamp(1 - Math.min(1, m + k)), scaleAndClamp(1 - Math.min(1, y + k))];\n  }\n  static CMYK_HTML(components) {\n    const rgb = this.CMYK_RGB(components).slice(1);\n    return this.RGB_HTML(rgb);\n  }\n  static RGB_CMYK([r, g, b]) {\n    const c = 1 - r;\n    const m = 1 - g;\n    const y = 1 - b;\n    const k = Math.min(c, m, y);\n    return [\"CMYK\", c, m, y, k];\n  }\n}\nconst DateFormats = [\"m/d\", \"m/d/yy\", \"mm/dd/yy\", \"mm/yy\", \"d-mmm\", \"d-mmm-yy\", \"dd-mmm-yy\", \"yy-mm-dd\", \"mmm-yy\", \"mmmm-yy\", \"mmm d, yyyy\", \"mmmm d, yyyy\", \"m/d/yy h:MM tt\", \"m/d/yy HH:MM\"];\nconst TimeFormats = [\"HH:MM\", \"h:MM tt\", \"HH:MM:ss\", \"h:MM:ss tt\"];\n\n;// ./src/scripting_api/pdf_object.js\nclass PDFObject {\n  constructor(data) {\n    this._expandos = Object.create(null);\n    this._send = data.send || null;\n    this._id = data.id || null;\n  }\n}\n\n;// ./src/scripting_api/color.js\n\n\n\n\nclass Color extends PDFObject {\n  transparent = [\"T\"];\n  black = [\"G\", 0];\n  white = [\"G\", 1];\n  red = [\"RGB\", 1, 0, 0];\n  green = [\"RGB\", 0, 1, 0];\n  blue = [\"RGB\", 0, 0, 1];\n  cyan = [\"CMYK\", 1, 0, 0, 0];\n  magenta = [\"CMYK\", 0, 1, 0, 0];\n  yellow = [\"CMYK\", 0, 0, 1, 0];\n  dkGray = [\"G\", 0.25];\n  gray = [\"G\", 0.5];\n  ltGray = [\"G\", 0.75];\n  constructor() {\n    super({});\n  }\n  static _isValidSpace(cColorSpace) {\n    return typeof cColorSpace === \"string\" && (cColorSpace === \"T\" || cColorSpace === \"G\" || cColorSpace === \"RGB\" || cColorSpace === \"CMYK\");\n  }\n  static _isValidColor(colorArray) {\n    if (!Array.isArray(colorArray) || colorArray.length === 0) {\n      return false;\n    }\n    const space = colorArray[0];\n    if (!Color._isValidSpace(space)) {\n      return false;\n    }\n    switch (space) {\n      case \"T\":\n        if (colorArray.length !== 1) {\n          return false;\n        }\n        break;\n      case \"G\":\n        if (colorArray.length !== 2) {\n          return false;\n        }\n        break;\n      case \"RGB\":\n        if (colorArray.length !== 4) {\n          return false;\n        }\n        break;\n      case \"CMYK\":\n        if (colorArray.length !== 5) {\n          return false;\n        }\n        break;\n      default:\n        return false;\n    }\n    return colorArray.slice(1).every(c => typeof c === \"number\" && c >= 0 && c <= 1);\n  }\n  static _getCorrectColor(colorArray) {\n    return Color._isValidColor(colorArray) ? colorArray : [\"G\", 0];\n  }\n  convert(colorArray, cColorSpace) {\n    if (!Color._isValidSpace(cColorSpace)) {\n      return this.black;\n    }\n    if (cColorSpace === \"T\") {\n      return [\"T\"];\n    }\n    colorArray = Color._getCorrectColor(colorArray);\n    if (colorArray[0] === cColorSpace) {\n      return colorArray;\n    }\n    if (colorArray[0] === \"T\") {\n      return this.convert(this.black, cColorSpace);\n    }\n    return ColorConverters[`${colorArray[0]}_${cColorSpace}`](colorArray.slice(1));\n  }\n  equal(colorArray1, colorArray2) {\n    colorArray1 = Color._getCorrectColor(colorArray1);\n    colorArray2 = Color._getCorrectColor(colorArray2);\n    if (colorArray1[0] === \"T\" || colorArray2[0] === \"T\") {\n      return colorArray1[0] === \"T\" && colorArray2[0] === \"T\";\n    }\n    if (colorArray1[0] !== colorArray2[0]) {\n      colorArray2 = this.convert(colorArray2, colorArray1[0]);\n    }\n    return colorArray1.slice(1).every((c, i) => c === colorArray2[i + 1]);\n  }\n}\n\n;// ./src/scripting_api/field.js\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nclass Field extends PDFObject {\n  constructor(data) {\n    super(data);\n    this.alignment = data.alignment || \"left\";\n    this.borderStyle = data.borderStyle || \"\";\n    this.buttonAlignX = data.buttonAlignX || 50;\n    this.buttonAlignY = data.buttonAlignY || 50;\n    this.buttonFitBounds = data.buttonFitBounds;\n    this.buttonPosition = data.buttonPosition;\n    this.buttonScaleHow = data.buttonScaleHow;\n    this.ButtonScaleWhen = data.buttonScaleWhen;\n    this.calcOrderIndex = data.calcOrderIndex;\n    this.comb = data.comb;\n    this.commitOnSelChange = data.commitOnSelChange;\n    this.currentValueIndices = data.currentValueIndices;\n    this.defaultStyle = data.defaultStyle;\n    this.defaultValue = data.defaultValue;\n    this.doNotScroll = data.doNotScroll;\n    this.doNotSpellCheck = data.doNotSpellCheck;\n    this.delay = data.delay;\n    this.display = data.display;\n    this.doc = data.doc.wrapped;\n    this.editable = data.editable;\n    this.exportValues = data.exportValues;\n    this.fileSelect = data.fileSelect;\n    this.hidden = data.hidden;\n    this.highlight = data.highlight;\n    this.lineWidth = data.lineWidth;\n    this.multiline = data.multiline;\n    this.multipleSelection = !!data.multipleSelection;\n    this.name = data.name;\n    this.password = data.password;\n    this.print = data.print;\n    this.radiosInUnison = data.radiosInUnison;\n    this.readonly = data.readonly;\n    this.rect = data.rect;\n    this.required = data.required;\n    this.richText = data.richText;\n    this.richValue = data.richValue;\n    this.style = data.style;\n    this.submitName = data.submitName;\n    this.textFont = data.textFont;\n    this.textSize = data.textSize;\n    this.type = data.type;\n    this.userName = data.userName;\n    this._actions = createMap(data.actions);\n    this._browseForFileToSubmit = data.browseForFileToSubmit || null;\n    this._buttonCaption = null;\n    this._buttonIcon = null;\n    this._charLimit = data.charLimit;\n    this._children = null;\n    this._currentValueIndices = data.currentValueIndices || 0;\n    this._document = data.doc;\n    this._fieldPath = data.fieldPath;\n    this._fillColor = data.fillColor || [\"T\"];\n    this._isChoice = Array.isArray(data.items);\n    this._items = data.items || [];\n    this._hasValue = Object.hasOwn(data, \"value\");\n    this._page = data.page || 0;\n    this._strokeColor = data.strokeColor || [\"G\", 0];\n    this._textColor = data.textColor || [\"G\", 0];\n    this._value = null;\n    this._kidIds = data.kidIds || null;\n    this._fieldType = getFieldType(this._actions);\n    this._siblings = data.siblings || null;\n    this._rotation = data.rotation || 0;\n    this._datetimeFormat = data.datetimeFormat || null;\n    this._hasDateOrTime = !!data.hasDatetimeHTML;\n    this._util = data.util;\n    this._globalEval = data.globalEval;\n    this._appObjects = data.appObjects;\n    this.value = data.value || \"\";\n  }\n  get currentValueIndices() {\n    return !this._isChoice ? 0 : this._currentValueIndices;\n  }\n  set currentValueIndices(indices) {\n    if (!this._isChoice) {\n      return;\n    }\n    if (!Array.isArray(indices)) {\n      indices = [indices];\n    }\n    if (!indices.every(i => Number.isInteger(i) && i >= 0 && i < this.numItems)) {\n      return;\n    }\n    indices.sort();\n    if (this.multipleSelection) {\n      this._currentValueIndices = indices;\n      this._value = [];\n      indices.forEach(i => {\n        this._value.push(this._items[i].displayValue);\n      });\n    } else if (indices.length > 0) {\n      indices = indices.splice(1, indices.length - 1);\n      this._currentValueIndices = indices[0];\n      this._value = this._items[this._currentValueIndices];\n    }\n    this._send({\n      id: this._id,\n      indices\n    });\n  }\n  get fillColor() {\n    return this._fillColor;\n  }\n  set fillColor(color) {\n    if (Color._isValidColor(color)) {\n      this._fillColor = color;\n    }\n  }\n  get bgColor() {\n    return this.fillColor;\n  }\n  set bgColor(color) {\n    this.fillColor = color;\n  }\n  get charLimit() {\n    return this._charLimit;\n  }\n  set charLimit(limit) {\n    if (typeof limit !== \"number\") {\n      throw new Error(\"Invalid argument value\");\n    }\n    this._charLimit = Math.max(0, Math.floor(limit));\n  }\n  get numItems() {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    return this._items.length;\n  }\n  set numItems(_) {\n    throw new Error(\"field.numItems is read-only\");\n  }\n  get strokeColor() {\n    return this._strokeColor;\n  }\n  set strokeColor(color) {\n    if (Color._isValidColor(color)) {\n      this._strokeColor = color;\n    }\n  }\n  get borderColor() {\n    return this.strokeColor;\n  }\n  set borderColor(color) {\n    this.strokeColor = color;\n  }\n  get page() {\n    return this._page;\n  }\n  set page(_) {\n    throw new Error(\"field.page is read-only\");\n  }\n  get rotation() {\n    return this._rotation;\n  }\n  set rotation(angle) {\n    angle = Math.floor(angle);\n    if (angle % 90 !== 0) {\n      throw new Error(\"Invalid rotation: must be a multiple of 90\");\n    }\n    angle %= 360;\n    if (angle < 0) {\n      angle += 360;\n    }\n    this._rotation = angle;\n  }\n  get textColor() {\n    return this._textColor;\n  }\n  set textColor(color) {\n    if (Color._isValidColor(color)) {\n      this._textColor = color;\n    }\n  }\n  get fgColor() {\n    return this.textColor;\n  }\n  set fgColor(color) {\n    this.textColor = color;\n  }\n  get value() {\n    return this._value;\n  }\n  set value(value) {\n    if (this._isChoice) {\n      this._setChoiceValue(value);\n      return;\n    }\n    if (this._hasDateOrTime && value) {\n      const date = this._util.scand(this._datetimeFormat, value);\n      if (date) {\n        this._originalValue = date.valueOf();\n        value = this._util.printd(this._datetimeFormat, date);\n        this._value = !isNaN(value) ? parseFloat(value) : value;\n        return;\n      }\n    }\n    if (value === \"\" || typeof value !== \"string\" || this._fieldType >= FieldType.date) {\n      this._originalValue = undefined;\n      this._value = value;\n      return;\n    }\n    this._originalValue = value;\n    const _value = value.trim().replace(\",\", \".\");\n    this._value = !isNaN(_value) ? parseFloat(_value) : value;\n  }\n  get _initialValue() {\n    return this._hasDateOrTime && this._originalValue || null;\n  }\n  _getValue() {\n    return this._originalValue ?? this.value;\n  }\n  _setChoiceValue(value) {\n    if (this.multipleSelection) {\n      if (!Array.isArray(value)) {\n        value = [value];\n      }\n      const values = new Set(value);\n      if (Array.isArray(this._currentValueIndices)) {\n        this._currentValueIndices.length = 0;\n        this._value.length = 0;\n      } else {\n        this._currentValueIndices = [];\n        this._value = [];\n      }\n      this._items.forEach((item, i) => {\n        if (values.has(item.exportValue)) {\n          this._currentValueIndices.push(i);\n          this._value.push(item.exportValue);\n        }\n      });\n    } else {\n      if (Array.isArray(value)) {\n        value = value[0];\n      }\n      const index = this._items.findIndex(({\n        exportValue\n      }) => value === exportValue);\n      if (index !== -1) {\n        this._currentValueIndices = index;\n        this._value = this._items[index].exportValue;\n      }\n    }\n  }\n  get valueAsString() {\n    return (this._value ?? \"\").toString();\n  }\n  set valueAsString(_) {}\n  browseForFileToSubmit() {\n    if (this._browseForFileToSubmit) {\n      this._browseForFileToSubmit();\n    }\n  }\n  buttonGetCaption(nFace = 0) {\n    return this._buttonCaption ? this._buttonCaption[nFace] : \"\";\n  }\n  buttonGetIcon(nFace = 0) {\n    return this._buttonIcon ? this._buttonIcon[nFace] : null;\n  }\n  buttonImportIcon(cPath = null, nPave = 0) {}\n  buttonSetCaption(cCaption, nFace = 0) {\n    this._buttonCaption ??= [\"\", \"\", \"\"];\n    this._buttonCaption[nFace] = cCaption;\n  }\n  buttonSetIcon(oIcon, nFace = 0) {\n    this._buttonIcon ??= [null, null, null];\n    this._buttonIcon[nFace] = oIcon;\n  }\n  checkThisBox(nWidget, bCheckIt = true) {}\n  clearItems() {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    this._items = [];\n    this._send({\n      id: this._id,\n      clear: null\n    });\n  }\n  deleteItemAt(nIdx = null) {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    if (!this.numItems) {\n      return;\n    }\n    if (nIdx === null) {\n      nIdx = Array.isArray(this._currentValueIndices) ? this._currentValueIndices[0] : this._currentValueIndices;\n      nIdx ||= 0;\n    }\n    if (nIdx < 0 || nIdx >= this.numItems) {\n      nIdx = this.numItems - 1;\n    }\n    this._items.splice(nIdx, 1);\n    if (Array.isArray(this._currentValueIndices)) {\n      let index = this._currentValueIndices.findIndex(i => i >= nIdx);\n      if (index !== -1) {\n        if (this._currentValueIndices[index] === nIdx) {\n          this._currentValueIndices.splice(index, 1);\n        }\n        for (const ii = this._currentValueIndices.length; index < ii; index++) {\n          --this._currentValueIndices[index];\n        }\n      }\n    } else if (this._currentValueIndices === nIdx) {\n      this._currentValueIndices = this.numItems > 0 ? 0 : -1;\n    } else if (this._currentValueIndices > nIdx) {\n      --this._currentValueIndices;\n    }\n    this._send({\n      id: this._id,\n      remove: nIdx\n    });\n  }\n  getItemAt(nIdx = -1, bExportValue = false) {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    if (nIdx < 0 || nIdx >= this.numItems) {\n      nIdx = this.numItems - 1;\n    }\n    const item = this._items[nIdx];\n    return bExportValue ? item.exportValue : item.displayValue;\n  }\n  getArray() {\n    if (this._kidIds) {\n      const array = [];\n      const fillArrayWithKids = kidIds => {\n        for (const id of kidIds) {\n          const obj = this._appObjects[id];\n          if (!obj) {\n            continue;\n          }\n          if (obj.obj._hasValue) {\n            array.push(obj.wrapped);\n          }\n          if (obj.obj._kidIds) {\n            fillArrayWithKids(obj.obj._kidIds);\n          }\n        }\n      };\n      fillArrayWithKids(this._kidIds);\n      return array;\n    }\n    return this._children ??= this._document.obj._getTerminalChildren(this._fieldPath);\n  }\n  getLock() {\n    return undefined;\n  }\n  isBoxChecked(nWidget) {\n    return false;\n  }\n  isDefaultChecked(nWidget) {\n    return false;\n  }\n  insertItemAt(cName, cExport = undefined, nIdx = 0) {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    if (!cName) {\n      return;\n    }\n    if (nIdx < 0 || nIdx > this.numItems) {\n      nIdx = this.numItems;\n    }\n    if (this._items.some(({\n      displayValue\n    }) => displayValue === cName)) {\n      return;\n    }\n    if (cExport === undefined) {\n      cExport = cName;\n    }\n    const data = {\n      displayValue: cName,\n      exportValue: cExport\n    };\n    this._items.splice(nIdx, 0, data);\n    if (Array.isArray(this._currentValueIndices)) {\n      let index = this._currentValueIndices.findIndex(i => i >= nIdx);\n      if (index !== -1) {\n        for (const ii = this._currentValueIndices.length; index < ii; index++) {\n          ++this._currentValueIndices[index];\n        }\n      }\n    } else if (this._currentValueIndices >= nIdx) {\n      ++this._currentValueIndices;\n    }\n    this._send({\n      id: this._id,\n      insert: {\n        index: nIdx,\n        ...data\n      }\n    });\n  }\n  setAction(cTrigger, cScript) {\n    if (typeof cTrigger !== \"string\" || typeof cScript !== \"string\") {\n      return;\n    }\n    this._actions.getOrInsertComputed(cTrigger, makeArr).push(cScript);\n  }\n  setFocus() {\n    this._send({\n      id: this._id,\n      focus: true\n    });\n  }\n  setItems(oArray) {\n    if (!this._isChoice) {\n      throw new Error(\"Not a choice widget\");\n    }\n    this._items.length = 0;\n    for (const element of oArray) {\n      let displayValue, exportValue;\n      if (Array.isArray(element)) {\n        displayValue = element[0]?.toString() || \"\";\n        exportValue = element[1]?.toString() || \"\";\n      } else {\n        displayValue = exportValue = element?.toString() || \"\";\n      }\n      this._items.push({\n        displayValue,\n        exportValue\n      });\n    }\n    this._currentValueIndices = 0;\n    this._send({\n      id: this._id,\n      items: this._items\n    });\n  }\n  setLock() {}\n  signatureGetModifications() {}\n  signatureGetSeedValue() {}\n  signatureInfo() {}\n  signatureSetSeedValue() {}\n  signatureSign() {}\n  signatureValidate() {}\n  _isButton() {\n    return false;\n  }\n  _reset() {\n    this.value = this.defaultValue;\n  }\n  _runActions(event) {\n    const eventName = event.name;\n    if (!this._actions.has(eventName)) {\n      return false;\n    }\n    const actions = this._actions.get(eventName);\n    for (const action of actions) {\n      try {\n        this._globalEval(action);\n      } catch (error) {\n        const serializedError = serializeError(error);\n        serializedError.value = `Error when executing \"${eventName}\" for field \"${this._id}\"\\n${serializedError.value}`;\n        this._send(serializedError);\n      }\n    }\n    return true;\n  }\n}\nclass RadioButtonField extends Field {\n  constructor(otherButtons, data) {\n    super(data);\n    this.exportValues = [this.exportValues];\n    this._radioIds = [this._id];\n    this._radioActions = [this._actions];\n    for (const radioData of otherButtons) {\n      this.exportValues.push(radioData.exportValues);\n      this._radioIds.push(radioData.id);\n      this._radioActions.push(createMap(radioData.actions));\n      if (this._value === radioData.exportValues) {\n        this._id = radioData.id;\n      }\n    }\n    this._hasBeenInitialized = true;\n    this._value = data.value || \"\";\n  }\n  get _siblings() {\n    return this._radioIds.filter(id => id !== this._id);\n  }\n  set _siblings(_) {}\n  get value() {\n    return this._value;\n  }\n  set value(value) {\n    if (!this._hasBeenInitialized) {\n      return;\n    }\n    if (value === null || value === undefined) {\n      this._value = \"\";\n    }\n    const i = this.exportValues.indexOf(value);\n    if (0 <= i && i < this._radioIds.length) {\n      this._id = this._radioIds[i];\n      this._value = value;\n    } else if (value === \"Off\" && this._radioIds.length === 2) {\n      const nextI = (1 + this._radioIds.indexOf(this._id)) % 2;\n      this._id = this._radioIds[nextI];\n      this._value = this.exportValues[nextI];\n    }\n  }\n  checkThisBox(nWidget, bCheckIt = true) {\n    if (nWidget < 0 || nWidget >= this._radioIds.length || !bCheckIt) {\n      return;\n    }\n    this._id = this._radioIds[nWidget];\n    this._value = this.exportValues[nWidget];\n    this._send({\n      id: this._id,\n      value: this._value\n    });\n  }\n  isBoxChecked(nWidget) {\n    return nWidget >= 0 && nWidget < this._radioIds.length && this._id === this._radioIds[nWidget];\n  }\n  isDefaultChecked(nWidget) {\n    return nWidget >= 0 && nWidget < this.exportValues.length && this.defaultValue === this.exportValues[nWidget];\n  }\n  _getExportValue(state) {\n    const i = this._radioIds.indexOf(this._id);\n    return this.exportValues[i];\n  }\n  _runActions(event) {\n    const i = this._radioIds.indexOf(this._id);\n    this._actions = this._radioActions[i];\n    return super._runActions(event);\n  }\n  _isButton() {\n    return true;\n  }\n}\nclass CheckboxField extends RadioButtonField {\n  get value() {\n    return this._value;\n  }\n  set value(value) {\n    if (!value || value === \"Off\") {\n      this._value = \"Off\";\n    } else {\n      super.value = value;\n    }\n  }\n  _getExportValue(state) {\n    return state ? super._getExportValue(state) : \"Off\";\n  }\n  isBoxChecked(nWidget) {\n    return this._value === \"Off\" ? false : super.isBoxChecked(nWidget);\n  }\n  isDefaultChecked(nWidget) {\n    return this.defaultValue === \"Off\" ? this._value === \"Off\" : super.isDefaultChecked(nWidget);\n  }\n  checkThisBox(nWidget, bCheckIt = true) {\n    if (nWidget < 0 || nWidget >= this._radioIds.length) {\n      return;\n    }\n    this._id = this._radioIds[nWidget];\n    this._value = bCheckIt ? this.exportValues[nWidget] : \"Off\";\n    this._send({\n      id: this._id,\n      value: this._value\n    });\n  }\n}\n\n;// ./node_modules/core-js/modules/es.iterator.reduce.js\nes_iterator_reduce_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.math.sum-precise.js\nes_math_sum_precise_namespaceFn();\n\n;// ./src/scripting_api/aform.js\n\n\n\n\n\n\n\n\n\nclass AForm {\n  constructor(document, app, util, color) {\n    this._document = document;\n    this._app = app;\n    this._util = util;\n    this._color = color;\n    this._emailRegex = new RegExp(\"^[\\\\w.!#$%&'*+/=?^`{|}~-]+\" + \"@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\" + \"(?:\\\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$\");\n  }\n  _mkTargetName(event) {\n    return event.target ? `[ ${event.target.name} ]` : \"\";\n  }\n  _parseDate(cFormat, cDate) {\n    let date = null;\n    try {\n      date = this._util._scand(cFormat, cDate, false);\n    } catch {}\n    if (date) {\n      return date;\n    }\n    date = Date.parse(cDate);\n    return isNaN(date) ? null : new Date(date);\n  }\n  AFMergeChange(event = globalThis.event) {\n    return event.willCommit ? event.value.toString() : this._app._eventDispatcher.mergeChange(event);\n  }\n  AFParseDateEx(cString, cOrder) {\n    return this._parseDate(cOrder, cString);\n  }\n  AFExtractNums(str) {\n    if (typeof str === \"number\") {\n      return [str];\n    }\n    if (!str || typeof str !== \"string\") {\n      return null;\n    }\n    const first = str.charAt(0);\n    if (first === \".\" || first === \",\") {\n      str = `0${str}`;\n    }\n    const numbers = str.match(/(\\d+)/g);\n    return numbers.length === 0 ? null : numbers;\n  }\n  AFMakeNumber(str) {\n    if (typeof str === \"number\") {\n      return str;\n    }\n    if (typeof str !== \"string\") {\n      return null;\n    }\n    str = str.trim().replace(\",\", \".\");\n    const number = parseFloat(str);\n    return isNaN(number) || !isFinite(number) ? null : number;\n  }\n  AFMakeArrayFromList(string) {\n    return typeof string === \"string\" ? string.split(/, ?/g) : string;\n  }\n  AFNumber_Format(nDec, sepStyle, negStyle, currStyle, strCurrency, bCurrencyPrepend) {\n    const event = globalThis.event;\n    let value = this.AFMakeNumber(event.value);\n    if (value === null) {\n      event.value = \"\";\n      return;\n    }\n    const sign = Math.sign(value);\n    const buf = [];\n    let hasParen = false;\n    if (sign === -1 && bCurrencyPrepend && negStyle === 0) {\n      buf.push(\"-\");\n    }\n    if ((negStyle === 2 || negStyle === 3) && sign === -1) {\n      buf.push(\"(\");\n      hasParen = true;\n    }\n    if (bCurrencyPrepend) {\n      buf.push(strCurrency);\n    }\n    sepStyle = MathClamp(Math.floor(sepStyle), 0, 4);\n    buf.push(\"%,\", sepStyle, \".\", nDec.toString(), \"f\");\n    if (!bCurrencyPrepend) {\n      buf.push(strCurrency);\n    }\n    if (hasParen) {\n      buf.push(\")\");\n    }\n    if (negStyle === 1 || negStyle === 3) {\n      event.target.textColor = sign === 1 ? this._color.black : this._color.red;\n    }\n    if ((negStyle !== 0 || bCurrencyPrepend) && sign === -1) {\n      value = -value;\n    }\n    const formatStr = buf.join(\"\");\n    event.value = this._util.printf(formatStr, value);\n  }\n  AFNumber_Keystroke(nDec, sepStyle, negStyle, currStyle, strCurrency, bCurrencyPrepend) {\n    const event = globalThis.event;\n    let value = this.AFMergeChange(event);\n    if (!value) {\n      return;\n    }\n    value = value.trim();\n    let pattern;\n    if (sepStyle > 1) {\n      pattern = event.willCommit ? /^[+-]?(\\d+(,\\d*)?|,\\d+)$/ : /^[+-]?\\d*(?:,\\d*)?$/;\n    } else {\n      pattern = event.willCommit ? /^[+-]?(\\d+(\\.\\d*)?|\\.\\d+)$/ : /^[+-]?\\d*(?:\\.\\d*)?$/;\n    }\n    if (!pattern.test(value)) {\n      if (event.willCommit) {\n        const err = `${GlobalConstants.IDS_INVALID_VALUE} ${this._mkTargetName(event)}`;\n        this._app.alert(err);\n      }\n      event.rc = false;\n    }\n    if (event.willCommit && sepStyle > 1) {\n      event.value = parseFloat(value.replace(\",\", \".\"));\n    }\n  }\n  AFPercent_Format(nDec, sepStyle, percentPrepend = false) {\n    if (typeof nDec !== \"number\" || typeof sepStyle !== \"number\") {\n      return;\n    }\n    if (nDec < 0) {\n      throw new Error(\"Invalid nDec value in AFPercent_Format\");\n    }\n    const event = globalThis.event;\n    if (nDec > 512) {\n      event.value = \"%\";\n      return;\n    }\n    nDec = Math.floor(nDec);\n    sepStyle = MathClamp(Math.floor(sepStyle), 0, 4);\n    let value = this.AFMakeNumber(event.value);\n    if (value === null) {\n      event.value = \"%\";\n      return;\n    }\n    const formatStr = `%,${sepStyle}.${nDec}f`;\n    value = this._util.printf(formatStr, value * 100);\n    event.value = percentPrepend ? `%${value}` : `${value}%`;\n  }\n  AFPercent_Keystroke(nDec, sepStyle) {\n    this.AFNumber_Keystroke(nDec, sepStyle, 0, 0, \"\", true);\n  }\n  AFDate_FormatEx(cFormat) {\n    const event = globalThis.event;\n    const value = event.value;\n    if (!value) {\n      return;\n    }\n    const date = this._parseDate(cFormat, value);\n    if (date !== null) {\n      event.value = this._util.printd(cFormat, date);\n    }\n  }\n  AFDate_Format(pdf) {\n    this.AFDate_FormatEx(DateFormats[pdf] ?? pdf);\n  }\n  AFDate_KeystrokeEx(cFormat) {\n    const event = globalThis.event;\n    if (!event.willCommit) {\n      return;\n    }\n    const value = this.AFMergeChange(event);\n    if (!value) {\n      return;\n    }\n    if (this._parseDate(cFormat, value) === null) {\n      const invalid = GlobalConstants.IDS_INVALID_DATE;\n      const invalid2 = GlobalConstants.IDS_INVALID_DATE2;\n      const err = `${invalid} ${this._mkTargetName(event)}${invalid2}${cFormat}`;\n      this._app.alert(err);\n      event.rc = false;\n    }\n  }\n  AFDate_Keystroke(pdf) {\n    if (pdf >= 0 && pdf < DateFormats.length) {\n      this.AFDate_KeystrokeEx(DateFormats[pdf]);\n    }\n  }\n  AFRange_Validate(bGreaterThan, nGreaterThan, bLessThan, nLessThan) {\n    const event = globalThis.event;\n    if (!event.value) {\n      return;\n    }\n    const value = this.AFMakeNumber(event.value);\n    if (value === null) {\n      return;\n    }\n    bGreaterThan = !!bGreaterThan;\n    bLessThan = !!bLessThan;\n    if (bGreaterThan) {\n      nGreaterThan = this.AFMakeNumber(nGreaterThan);\n      if (nGreaterThan === null) {\n        return;\n      }\n    }\n    if (bLessThan) {\n      nLessThan = this.AFMakeNumber(nLessThan);\n      if (nLessThan === null) {\n        return;\n      }\n    }\n    let err = \"\";\n    if (bGreaterThan && bLessThan) {\n      if (value < nGreaterThan || value > nLessThan) {\n        err = this._util.printf(GlobalConstants.IDS_GT_AND_LT, nGreaterThan, nLessThan);\n      }\n    } else if (bGreaterThan) {\n      if (value < nGreaterThan) {\n        err = this._util.printf(GlobalConstants.IDS_GREATER_THAN, nGreaterThan);\n      }\n    } else if (value > nLessThan) {\n      err = this._util.printf(GlobalConstants.IDS_LESS_THAN, nLessThan);\n    }\n    if (err) {\n      this._app.alert(err);\n      event.rc = false;\n    }\n  }\n  AFSimple(cFunction, nValue1, nValue2) {\n    const value1 = this.AFMakeNumber(nValue1);\n    if (value1 === null) {\n      throw new Error(\"Invalid nValue1 in AFSimple\");\n    }\n    const value2 = this.AFMakeNumber(nValue2);\n    if (value2 === null) {\n      throw new Error(\"Invalid nValue2 in AFSimple\");\n    }\n    switch (cFunction) {\n      case \"AVG\":\n        return (value1 + value2) / 2;\n      case \"SUM\":\n        return value1 + value2;\n      case \"PRD\":\n        return value1 * value2;\n      case \"MIN\":\n        return Math.min(value1, value2);\n      case \"MAX\":\n        return Math.max(value1, value2);\n    }\n    throw new Error(\"Invalid cFunction in AFSimple\");\n  }\n  AFSimple_Calculate(cFunction, cFields) {\n    const actions = {\n      AVG: args => Math.sumPrecise(args) / args.length,\n      SUM: args => Math.sumPrecise(args),\n      PRD: args => args.reduce((acc, value) => acc * value, 1),\n      MIN: args => Math.min(...args),\n      MAX: args => Math.max(...args)\n    };\n    if (!(cFunction in actions)) {\n      throw new TypeError(\"Invalid function in AFSimple_Calculate\");\n    }\n    const event = globalThis.event;\n    const values = [];\n    cFields = this.AFMakeArrayFromList(cFields);\n    for (const cField of cFields) {\n      const field = this._document.getField(cField);\n      if (!field) {\n        continue;\n      }\n      for (const child of field.getArray()) {\n        const number = this.AFMakeNumber(child.value);\n        values.push(number ?? 0);\n      }\n    }\n    if (values.length === 0) {\n      event.value = 0;\n      return;\n    }\n    const res = actions[cFunction](values);\n    event.value = Math.round(1e6 * res) / 1e6;\n  }\n  AFSpecial_Format(psf) {\n    const event = globalThis.event;\n    if (!event.value) {\n      return;\n    }\n    psf = this.AFMakeNumber(psf);\n    let formatStr;\n    switch (psf) {\n      case 0:\n        formatStr = \"99999\";\n        break;\n      case 1:\n        formatStr = \"99999-9999\";\n        break;\n      case 2:\n        formatStr = this._util.printx(\"9999999999\", event.value).length >= 10 ? \"(999) 999-9999\" : \"999-9999\";\n        break;\n      case 3:\n        formatStr = \"999-99-9999\";\n        break;\n      default:\n        throw new Error(\"Invalid psf in AFSpecial_Format\");\n    }\n    event.value = this._util.printx(formatStr, event.value);\n  }\n  AFSpecial_KeystrokeEx(cMask) {\n    const event = globalThis.event;\n    const simplifiedFormatStr = cMask.replaceAll(/[^9AOX]/g, \"\");\n    this.#AFSpecial_KeystrokeEx_helper(simplifiedFormatStr, null, false);\n    if (event.rc) {\n      return;\n    }\n    event.rc = true;\n    this.#AFSpecial_KeystrokeEx_helper(cMask, null, true);\n  }\n  #AFSpecial_KeystrokeEx_helper(cMask, value, warn) {\n    if (!cMask) {\n      return;\n    }\n    const event = globalThis.event;\n    value ||= this.AFMergeChange(event);\n    if (!value) {\n      return;\n    }\n    const checkers = new Map([[\"9\", char => char >= \"0\" && char <= \"9\"], [\"A\", char => \"a\" <= char && char <= \"z\" || \"A\" <= char && char <= \"Z\"], [\"O\", char => \"a\" <= char && char <= \"z\" || \"A\" <= char && char <= \"Z\" || \"0\" <= char && char <= \"9\"], [\"X\", char => true]]);\n    function _checkValidity(_value, _cMask) {\n      for (let i = 0, ii = _value.length; i < ii; i++) {\n        const mask = _cMask.charAt(i);\n        const char = _value.charAt(i);\n        const checker = checkers.get(mask);\n        if (checker) {\n          if (!checker(char)) {\n            return false;\n          }\n        } else if (mask !== char) {\n          return false;\n        }\n      }\n      return true;\n    }\n    const err = `${GlobalConstants.IDS_INVALID_VALUE} = \"${cMask}\"`;\n    if (value.length > cMask.length) {\n      if (warn) {\n        this._app.alert(err);\n      }\n      event.rc = false;\n      return;\n    }\n    if (event.willCommit) {\n      if (value.length < cMask.length) {\n        if (warn) {\n          this._app.alert(err);\n        }\n        event.rc = false;\n        return;\n      }\n      if (!_checkValidity(value, cMask)) {\n        if (warn) {\n          this._app.alert(err);\n        }\n        event.rc = false;\n        return;\n      }\n      event.value += cMask.substring(value.length);\n      return;\n    }\n    if (value.length < cMask.length) {\n      cMask = cMask.substring(0, value.length);\n    }\n    if (!_checkValidity(value, cMask)) {\n      if (warn) {\n        this._app.alert(err);\n      }\n      event.rc = false;\n    }\n  }\n  AFSpecial_Keystroke(psf) {\n    const event = globalThis.event;\n    psf = this.AFMakeNumber(psf);\n    let value = this.AFMergeChange(event);\n    let formatStr, secondFormatStr;\n    switch (psf) {\n      case 0:\n        formatStr = \"99999\";\n        break;\n      case 1:\n        formatStr = \"99999-9999\";\n        break;\n      case 2:\n        formatStr = \"999-9999\";\n        secondFormatStr = \"(999) 999-9999\";\n        break;\n      case 3:\n        formatStr = \"999-99-9999\";\n        break;\n      default:\n        throw new Error(\"Invalid psf in AFSpecial_Keystroke\");\n    }\n    const formats = secondFormatStr ? [formatStr, secondFormatStr] : [formatStr];\n    for (const format of formats) {\n      this.#AFSpecial_KeystrokeEx_helper(format, value, false);\n      if (event.rc) {\n        return;\n      }\n      event.rc = true;\n    }\n    const re = /[-()\\s]+/g;\n    value = value.replaceAll(re, \"\");\n    for (const format of formats) {\n      this.#AFSpecial_KeystrokeEx_helper(format.replaceAll(re, \"\"), value, false);\n      if (event.rc) {\n        return;\n      }\n      event.rc = true;\n    }\n    this.AFSpecial_KeystrokeEx((secondFormatStr && value.match(/\\d/g) || []).length > 7 ? secondFormatStr : formatStr);\n  }\n  AFTime_FormatEx(cFormat) {\n    this.AFDate_FormatEx(cFormat);\n  }\n  AFTime_Format(pdf) {\n    this.AFDate_FormatEx(TimeFormats[pdf] ?? pdf);\n  }\n  AFTime_KeystrokeEx(cFormat) {\n    this.AFDate_KeystrokeEx(cFormat);\n  }\n  AFTime_Keystroke(pdf) {\n    if (pdf >= 0 && pdf < TimeFormats.length) {\n      this.AFDate_KeystrokeEx(TimeFormats[pdf]);\n    }\n  }\n  eMailValidate(str) {\n    return this._emailRegex.test(str);\n  }\n  AFExactMatch(rePatterns, str) {\n    return rePatterns instanceof RegExp ? str.match(rePatterns)?.[0] === str || 0 : rePatterns.findIndex(re => str.match(re)?.[0] === str) + 1;\n  }\n}\n\n;// ./node_modules/core-js/modules/es.array.includes.js\nes_array_includes_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.weak-map.get-or-insert.js\nes_weak_map_get_or_insert_namespaceFn();\n\n;// ./node_modules/core-js/modules/es.weak-map.get-or-insert-computed.js\nes_weak_map_get_or_insert_computed_namespaceFn();\n\n;// ./src/scripting_api/event.js\n\n\nclass Event {\n  constructor(data) {\n    this.change = data.change || \"\";\n    this.changeEx = data.changeEx || null;\n    this.commitKey = data.commitKey || 0;\n    this.fieldFull = data.fieldFull || false;\n    this.keyDown = data.keyDown || false;\n    this.modifier = data.modifier || false;\n    this.name = data.name;\n    this.rc = true;\n    this.richChange = data.richChange || [];\n    this.richChangeEx = data.richChangeEx || [];\n    this.richValue = data.richValue || [];\n    this.selEnd = data.selEnd ?? -1;\n    this.selStart = data.selStart ?? -1;\n    this.shift = data.shift || false;\n    this.source = data.source || null;\n    this.target = data.target || null;\n    this.targetName = \"\";\n    this.type = \"Field\";\n    this.value = data.value || \"\";\n    this.willCommit = data.willCommit || false;\n  }\n}\nclass EventDispatcher {\n  constructor(document, calculationOrder, objects, externalCall) {\n    this._document = document;\n    this._calculationOrder = calculationOrder;\n    this._objects = objects;\n    this._externalCall = externalCall;\n    this._document.obj._eventDispatcher = this;\n    this._isCalculating = false;\n  }\n  mergeChange(event) {\n    let value = event.value;\n    if (Array.isArray(value)) {\n      return value;\n    }\n    if (typeof value !== \"string\") {\n      value = value.toString();\n    }\n    const prefix = event.selStart >= 0 ? value.substring(0, event.selStart) : \"\";\n    const postfix = event.selEnd >= 0 && event.selEnd <= value.length ? value.substring(event.selEnd) : \"\";\n    return `${prefix}${event.change}${postfix}`;\n  }\n  userActivation() {\n    this._document.obj._userActivation = true;\n    this._externalCall(\"setTimeout\", [(/* inlined export .USERACTIVATION_CALLBACKID */0), (/* inlined export .USERACTIVATION_MAXTIME_VALIDITY */5000)]);\n  }\n  dispatch(baseEvent) {\n    const id = baseEvent.id;\n    if (!(id in this._objects)) {\n      let event;\n      if (id === \"doc\" || id === \"page\") {\n        event = globalThis.event = new Event(baseEvent);\n        event.source = event.target = this._document.wrapped;\n        event.name = baseEvent.name;\n      }\n      if (id === \"doc\") {\n        const eventName = event.name;\n        if (eventName === \"Open\") {\n          this.userActivation();\n          this._document.obj._initActions();\n          this.formatAll();\n        }\n        if (![\"DidPrint\", \"DidSave\", \"WillPrint\", \"WillSave\"].includes(eventName)) {\n          this.userActivation();\n        }\n        this._document.obj._dispatchDocEvent(event.name);\n      } else if (id === \"page\") {\n        this.userActivation();\n        this._document.obj._dispatchPageEvent(event.name, baseEvent.actions, baseEvent.pageNumber);\n      } else if (id === \"app\" && baseEvent.name === \"ResetForm\") {\n        this.userActivation();\n        for (const fieldId of baseEvent.ids) {\n          const obj = this._objects[fieldId];\n          obj?.obj._reset();\n        }\n      }\n      return;\n    }\n    const name = baseEvent.name;\n    const source = this._objects[id];\n    const event = globalThis.event = new Event(baseEvent);\n    let savedChange;\n    this.userActivation();\n    if (source.obj._isButton()) {\n      source.obj._id = id;\n      event.value = source.obj._getExportValue(event.value);\n      if (name === \"Action\") {\n        source.obj._value = event.value;\n      }\n    }\n    switch (name) {\n      case \"Keystroke\":\n        savedChange = {\n          value: event.value,\n          changeEx: event.changeEx,\n          change: event.change,\n          selStart: event.selStart,\n          selEnd: event.selEnd\n        };\n        break;\n      case \"Blur\":\n      case \"Focus\":\n        Object.defineProperty(event, \"value\", {\n          configurable: false,\n          writable: false,\n          enumerable: true,\n          value: event.value\n        });\n        break;\n      case \"Validate\":\n        this.runValidation(source, event);\n        return;\n      case \"Action\":\n        this.runActions(source, source, event, name);\n        this.runCalculate(source, event);\n        return;\n    }\n    this.runActions(source, source, event, name);\n    if (name !== \"Keystroke\") {\n      return;\n    }\n    if (event.rc) {\n      if (event.willCommit) {\n        this.runValidation(source, event);\n      } else {\n        if (source.obj._isChoice) {\n          source.obj.value = savedChange.changeEx;\n          source.obj._send({\n            id: source.obj._id,\n            siblings: source.obj._siblings,\n            value: source.obj.value\n          });\n          return;\n        }\n        const value = source.obj.value = this.mergeChange(event);\n        let selStart, selEnd;\n        if (event.selStart !== savedChange.selStart || event.selEnd !== savedChange.selEnd) {\n          selStart = event.selStart;\n          selEnd = event.selEnd;\n        } else {\n          selEnd = selStart = savedChange.selStart + event.change.length;\n        }\n        source.obj._send({\n          id: source.obj._id,\n          siblings: source.obj._siblings,\n          value,\n          selRange: [selStart, selEnd]\n        });\n      }\n    } else if (!event.willCommit) {\n      source.obj._send({\n        id: source.obj._id,\n        siblings: source.obj._siblings,\n        value: savedChange.value,\n        selRange: [savedChange.selStart, savedChange.selEnd]\n      });\n    } else {\n      source.obj._send({\n        id: source.obj._id,\n        siblings: source.obj._siblings,\n        value: \"\",\n        formattedValue: null,\n        selRange: [0, 0]\n      });\n    }\n  }\n  formatAll() {\n    const event = globalThis.event = new Event({});\n    for (const source of Object.values(this._objects)) {\n      event.value = source.obj._getValue();\n      this.runActions(source, source, event, \"Format\");\n    }\n  }\n  runValidation(source, event) {\n    const didValidateRun = this.runActions(source, source, event, \"Validate\");\n    if (event.rc) {\n      source.obj.value = event.value;\n      this.runCalculate(source, event);\n      const savedValue = event.value = source.obj._getValue();\n      const formattedValue = this.runActions(source, source, event, \"Format\") ? event.value?.toString?.() : null;\n      source.obj._send({\n        id: source.obj._id,\n        siblings: source.obj._siblings,\n        value: savedValue,\n        formattedValue\n      });\n      event.value = savedValue;\n    } else if (didValidateRun) {\n      source.obj._send({\n        id: source.obj._id,\n        siblings: source.obj._siblings,\n        value: \"\",\n        formattedValue: null,\n        selRange: [0, 0],\n        focus: true\n      });\n    }\n  }\n  runActions(source, target, event, eventName) {\n    event.source = source.wrapped;\n    event.target = target.wrapped;\n    event.name = eventName;\n    event.targetName = target.obj.name;\n    event.rc = true;\n    return target.obj._runActions(event);\n  }\n  calculateNow() {\n    if (!this._calculationOrder || this._isCalculating || !this._document.obj.calculate) {\n      return;\n    }\n    this._isCalculating = true;\n    const first = this._calculationOrder[0];\n    const source = this._objects[first];\n    globalThis.event = new Event({});\n    this.runCalculate(source, globalThis.event);\n    this._isCalculating = false;\n  }\n  runCalculate(source, event) {\n    if (!this._calculationOrder || !this._document.obj.calculate) {\n      return;\n    }\n    for (const targetId of this._calculationOrder) {\n      if (!(targetId in this._objects)) {\n        continue;\n      }\n      if (!this._document.obj.calculate) {\n        break;\n      }\n      event.value = null;\n      const target = this._objects[targetId];\n      let savedValue = target.obj._getValue();\n      this.runActions(source, target, event, \"Calculate\");\n      if (!event.rc) {\n        continue;\n      }\n      if (event.value !== null) {\n        target.obj.value = event.value;\n      } else {\n        event.value = target.obj._getValue();\n      }\n      this.runActions(target, target, event, \"Validate\");\n      if (!event.rc) {\n        if (target.obj._getValue() !== savedValue) {\n          target.wrapped.value = savedValue;\n        }\n        continue;\n      }\n      if (event.value === null) {\n        event.value = target.obj._getValue();\n      }\n      savedValue = target.obj._getValue();\n      const formattedValue = this.runActions(target, target, event, \"Format\") ? event.value?.toString?.() : null;\n      target.obj._send({\n        id: target.obj._id,\n        siblings: target.obj._siblings,\n        value: savedValue,\n        formattedValue\n      });\n    }\n  }\n}\n\n;// ./src/scripting_api/fullscreen.js\n\n\nclass FullScreen extends PDFObject {\n  _backgroundColor = [];\n  _clickAdvances = true;\n  _cursor = Cursor.hidden;\n  _defaultTransition = \"\";\n  _escapeExits = true;\n  _isFullScreen = true;\n  _loop = false;\n  _timeDelay = 3600;\n  _usePageTiming = false;\n  _useTimer = false;\n  get backgroundColor() {\n    return this._backgroundColor;\n  }\n  set backgroundColor(_) {}\n  get clickAdvances() {\n    return this._clickAdvances;\n  }\n  set clickAdvances(_) {}\n  get cursor() {\n    return this._cursor;\n  }\n  set cursor(_) {}\n  get defaultTransition() {\n    return this._defaultTransition;\n  }\n  set defaultTransition(_) {}\n  get escapeExits() {\n    return this._escapeExits;\n  }\n  set escapeExits(_) {}\n  get isFullScreen() {\n    return this._isFullScreen;\n  }\n  set isFullScreen(_) {}\n  get loop() {\n    return this._loop;\n  }\n  set loop(_) {}\n  get timeDelay() {\n    return this._timeDelay;\n  }\n  set timeDelay(_) {}\n  get transitions() {\n    return [\"Replace\", \"WipeRight\", \"WipeLeft\", \"WipeDown\", \"WipeUp\", \"SplitHorizontalIn\", \"SplitHorizontalOut\", \"SplitVerticalIn\", \"SplitVerticalOut\", \"BlindsHorizontal\", \"BlindsVertical\", \"BoxIn\", \"BoxOut\", \"GlitterRight\", \"GlitterDown\", \"GlitterRightDown\", \"Dissolve\", \"Random\"];\n  }\n  set transitions(_) {\n    throw new Error(\"fullscreen.transitions is read-only\");\n  }\n  get usePageTiming() {\n    return this._usePageTiming;\n  }\n  set usePageTiming(_) {}\n  get useTimer() {\n    return this._useTimer;\n  }\n  set useTimer(_) {}\n}\n\n;// ./src/scripting_api/thermometer.js\n\nclass Thermometer extends PDFObject {\n  _cancelled = false;\n  _duration = 100;\n  _text = \"\";\n  _value = 0;\n  get cancelled() {\n    return this._cancelled;\n  }\n  set cancelled(_) {\n    throw new Error(\"thermometer.cancelled is read-only\");\n  }\n  get duration() {\n    return this._duration;\n  }\n  set duration(val) {\n    this._duration = val;\n  }\n  get text() {\n    return this._text;\n  }\n  set text(val) {\n    this._text = val;\n  }\n  get value() {\n    return this._value;\n  }\n  set value(val) {\n    this._value = val;\n  }\n  begin() {}\n  end() {}\n}\n\n;// ./src/scripting_api/app.js\n\n\n\n\n\n\n\n\n\n\n\nclass App extends PDFObject {\n  constructor(data) {\n    super(data);\n    this._constants = null;\n    this._focusRect = true;\n    this._fs = null;\n    this._language = App._getLanguage(data.language);\n    this._openInPlace = false;\n    this._platform = App._getPlatform(data.platform);\n    this._runtimeHighlight = false;\n    this._runtimeHighlightColor = [\"T\"];\n    this._thermometer = null;\n    this._toolbar = false;\n    this._document = data._document;\n    this._proxyHandler = data.proxyHandler;\n    this._objects = Object.create(null);\n    this._eventDispatcher = new EventDispatcher(this._document, data.calculationOrder, this._objects, data.externalCall);\n    this._timeoutIds = new WeakMap();\n    this._timeoutIdsRegistry = new FinalizationRegistry(this._cleanTimeout.bind(this));\n    this._timeoutCallbackIds = new Map();\n    this._timeoutCallbackId = (/* inlined export .USERACTIVATION_CALLBACKID */0) + 1;\n    this._globalEval = data.globalEval;\n    this._externalCall = data.externalCall;\n  }\n  _dispatchEvent(pdfEvent) {\n    this._eventDispatcher.dispatch(pdfEvent);\n  }\n  _registerTimeoutCallback(cExpr) {\n    const id = this._timeoutCallbackId++;\n    this._timeoutCallbackIds.set(id, cExpr);\n    return id;\n  }\n  _unregisterTimeoutCallback(id) {\n    this._timeoutCallbackIds.delete(id);\n  }\n  _evalCallback({\n    callbackId,\n    interval\n  }) {\n    const documentObj = this._document.obj;\n    if (callbackId === (/* inlined export .USERACTIVATION_CALLBACKID */0)) {\n      documentObj._userActivation = false;\n      return;\n    }\n    const expr = this._timeoutCallbackIds.get(callbackId);\n    if (!interval) {\n      this._unregisterTimeoutCallback(callbackId);\n    }\n    if (expr) {\n      const saveUserActivation = documentObj._userActivation;\n      documentObj._userActivation = false;\n      this._globalEval(expr);\n      documentObj._userActivation = saveUserActivation;\n    }\n  }\n  _registerTimeout(callbackId, interval) {\n    const timeout = Object.create(null);\n    const id = {\n      callbackId,\n      interval\n    };\n    this._timeoutIds.set(timeout, id);\n    this._timeoutIdsRegistry.register(timeout, id);\n    return timeout;\n  }\n  _unregisterTimeout(timeout) {\n    this._timeoutIdsRegistry.unregister(timeout);\n    const data = this._timeoutIds.get(timeout);\n    if (!data) {\n      return;\n    }\n    this._timeoutIds.delete(timeout);\n    this._cleanTimeout(data);\n  }\n  _cleanTimeout({\n    callbackId,\n    interval\n  }) {\n    this._unregisterTimeoutCallback(callbackId);\n    if (interval) {\n      this._externalCall(\"clearInterval\", [callbackId]);\n    } else {\n      this._externalCall(\"clearTimeout\", [callbackId]);\n    }\n  }\n  static _getPlatform(platform) {\n    if (typeof platform === \"string\") {\n      platform = platform.toLowerCase();\n      if (platform.includes(\"win\")) {\n        return \"WIN\";\n      } else if (platform.includes(\"mac\")) {\n        return \"MAC\";\n      }\n    }\n    return \"UNIX\";\n  }\n  static _getLanguage(language) {\n    const [main, sub] = language.toLowerCase().split(/[-_]/, 2);\n    switch (main) {\n      case \"zh\":\n        return sub === \"cn\" || sub === \"sg\" ? \"CHS\" : \"CHT\";\n      case \"da\":\n        return \"DAN\";\n      case \"de\":\n        return \"DEU\";\n      case \"es\":\n        return \"ESP\";\n      case \"fr\":\n        return \"FRA\";\n      case \"it\":\n        return \"ITA\";\n      case \"ko\":\n        return \"KOR\";\n      case \"ja\":\n        return \"JPN\";\n      case \"nl\":\n        return \"NLD\";\n      case \"no\":\n        return \"NOR\";\n      case \"pt\":\n        return sub === \"br\" ? \"PTB\" : \"ENU\";\n      case \"fi\":\n        return \"SUO\";\n      case \"SV\":\n        return \"SVE\";\n      default:\n        return \"ENU\";\n    }\n  }\n  get activeDocs() {\n    return [this._document.wrapped];\n  }\n  set activeDocs(_) {\n    throw new Error(\"app.activeDocs is read-only\");\n  }\n  get calculate() {\n    return this._document.obj.calculate;\n  }\n  set calculate(calculate) {\n    this._document.obj.calculate = calculate;\n  }\n  get constants() {\n    return this._constants ??= Object.freeze({\n      align: Object.freeze({\n        left: 0,\n        center: 1,\n        right: 2,\n        top: 3,\n        bottom: 4\n      })\n    });\n  }\n  set constants(_) {\n    throw new Error(\"app.constants is read-only\");\n  }\n  get focusRect() {\n    return this._focusRect;\n  }\n  set focusRect(val) {\n    this._focusRect = val;\n  }\n  get formsVersion() {\n    return FORMS_VERSION;\n  }\n  set formsVersion(_) {\n    throw new Error(\"app.formsVersion is read-only\");\n  }\n  get fromPDFConverters() {\n    return [];\n  }\n  set fromPDFConverters(_) {\n    throw new Error(\"app.fromPDFConverters is read-only\");\n  }\n  get fs() {\n    return this._fs ??= new Proxy(new FullScreen({\n      send: this._send\n    }), this._proxyHandler);\n  }\n  set fs(_) {\n    throw new Error(\"app.fs is read-only\");\n  }\n  get language() {\n    return this._language;\n  }\n  set language(_) {\n    throw new Error(\"app.language is read-only\");\n  }\n  get media() {\n    return undefined;\n  }\n  set media(_) {\n    throw new Error(\"app.media is read-only\");\n  }\n  get monitors() {\n    return [];\n  }\n  set monitors(_) {\n    throw new Error(\"app.monitors is read-only\");\n  }\n  get numPlugins() {\n    return 0;\n  }\n  set numPlugins(_) {\n    throw new Error(\"app.numPlugins is read-only\");\n  }\n  get openInPlace() {\n    return this._openInPlace;\n  }\n  set openInPlace(val) {\n    this._openInPlace = val;\n  }\n  get platform() {\n    return this._platform;\n  }\n  set platform(_) {\n    throw new Error(\"app.platform is read-only\");\n  }\n  get plugins() {\n    return [];\n  }\n  set plugins(_) {\n    throw new Error(\"app.plugins is read-only\");\n  }\n  get printColorProfiles() {\n    return [];\n  }\n  set printColorProfiles(_) {\n    throw new Error(\"app.printColorProfiles is read-only\");\n  }\n  get printerNames() {\n    return [];\n  }\n  set printerNames(_) {\n    throw new Error(\"app.printerNames is read-only\");\n  }\n  get runtimeHighlight() {\n    return this._runtimeHighlight;\n  }\n  set runtimeHighlight(val) {\n    this._runtimeHighlight = val;\n  }\n  get runtimeHighlightColor() {\n    return this._runtimeHighlightColor;\n  }\n  set runtimeHighlightColor(val) {\n    if (Color._isValidColor(val)) {\n      this._runtimeHighlightColor = val;\n    }\n  }\n  get thermometer() {\n    return this._thermometer ??= new Proxy(new Thermometer({\n      send: this._send\n    }), this._proxyHandler);\n  }\n  set thermometer(_) {\n    throw new Error(\"app.thermometer is read-only\");\n  }\n  get toolbar() {\n    return this._toolbar;\n  }\n  set toolbar(val) {\n    this._toolbar = val;\n  }\n  get toolbarHorizontal() {\n    return this.toolbar;\n  }\n  set toolbarHorizontal(value) {\n    this.toolbar = value;\n  }\n  get toolbarVertical() {\n    return this.toolbar;\n  }\n  set toolbarVertical(value) {\n    this.toolbar = value;\n  }\n  get viewerType() {\n    return (/* inlined export .VIEWER_TYPE */\"PDF.js\");\n  }\n  set viewerType(_) {\n    throw new Error(\"app.viewerType is read-only\");\n  }\n  get viewerVariation() {\n    return (/* inlined export .VIEWER_VARIATION */\"Full\");\n  }\n  set viewerVariation(_) {\n    throw new Error(\"app.viewerVariation is read-only\");\n  }\n  get viewerVersion() {\n    return VIEWER_VERSION;\n  }\n  set viewerVersion(_) {\n    throw new Error(\"app.viewerVersion is read-only\");\n  }\n  addMenuItem() {}\n  addSubMenu() {}\n  addToolButton() {}\n  alert(cMsg, nIcon = 0, nType = 0, cTitle = \"PDF.js\", oDoc = null, oCheckbox = null) {\n    if (!this._document.obj._userActivation) {\n      return 0;\n    }\n    this._document.obj._userActivation = false;\n    if (cMsg && typeof cMsg === \"object\") {\n      nType = cMsg.nType;\n      cMsg = cMsg.cMsg;\n    }\n    cMsg = (cMsg || \"\").toString();\n    if (!cMsg) {\n      return 0;\n    }\n    nType = typeof nType !== \"number\" || isNaN(nType) || nType < 0 || nType > 3 ? 0 : nType;\n    if (nType >= 2) {\n      return this._externalCall(\"confirm\", [cMsg]) ? 4 : 3;\n    }\n    this._externalCall(\"alert\", [cMsg]);\n    return 1;\n  }\n  beep() {}\n  beginPriv() {}\n  browseForDoc() {}\n  clearInterval(oInterval) {\n    this._unregisterTimeout(oInterval);\n  }\n  clearTimeOut(oTime) {\n    this._unregisterTimeout(oTime);\n  }\n  endPriv() {}\n  execDialog() {}\n  execMenuItem(item) {\n    if (!this._document.obj._userActivation) {\n      return;\n    }\n    this._document.obj._userActivation = false;\n    switch (item) {\n      case \"SaveAs\":\n        if (this._document.obj._disableSaving) {\n          return;\n        }\n        this._send({\n          command: item\n        });\n        break;\n      case \"FirstPage\":\n      case \"LastPage\":\n      case \"NextPage\":\n      case \"PrevPage\":\n      case \"ZoomViewIn\":\n      case \"ZoomViewOut\":\n        this._send({\n          command: item\n        });\n        break;\n      case \"FitPage\":\n        this._send({\n          command: \"zoom\",\n          value: \"page-fit\"\n        });\n        break;\n      case \"Print\":\n        if (this._document.obj._disablePrinting) {\n          return;\n        }\n        this._send({\n          command: \"print\"\n        });\n        break;\n    }\n  }\n  getNthPlugInName() {}\n  getPath() {}\n  goBack() {}\n  goForward() {}\n  hideMenuItem() {}\n  hideToolbarButton() {}\n  launchURL() {}\n  listMenuItems() {}\n  listToolbarButtons() {}\n  loadPolicyFile() {}\n  mailGetAddrs() {}\n  mailMsg() {}\n  newDoc() {}\n  newCollection() {}\n  newFDF() {}\n  openDoc() {}\n  openFDF() {}\n  popUpMenu() {}\n  popUpMenuEx() {}\n  removeToolButton() {}\n  response(cQuestion, cTitle = \"\", cDefault = \"\", bPassword = \"\", cLabel = \"\") {\n    if (!this._document.obj._userActivation) {\n      return null;\n    }\n    this._document.obj._userActivation = false;\n    if (cQuestion && typeof cQuestion === \"object\") {\n      cDefault = cQuestion.cDefault;\n      cQuestion = cQuestion.cQuestion;\n    }\n    cQuestion = (cQuestion || \"\").toString();\n    cDefault = (cDefault || \"\").toString();\n    return this._externalCall(\"prompt\", [cQuestion, cDefault || \"\"]);\n  }\n  setInterval(cExpr, nMilliseconds = 0) {\n    if (cExpr && typeof cExpr === \"object\") {\n      nMilliseconds = cExpr.nMilliseconds || 0;\n      cExpr = cExpr.cExpr;\n    }\n    if (typeof cExpr !== \"string\") {\n      throw new TypeError(\"First argument of app.setInterval must be a string\");\n    }\n    if (typeof nMilliseconds !== \"number\") {\n      throw new TypeError(\"Second argument of app.setInterval must be a number\");\n    }\n    const callbackId = this._registerTimeoutCallback(cExpr);\n    this._externalCall(\"setInterval\", [callbackId, nMilliseconds]);\n    return this._registerTimeout(callbackId, true);\n  }\n  setTimeOut(cExpr, nMilliseconds = 0) {\n    if (cExpr && typeof cExpr === \"object\") {\n      nMilliseconds = cExpr.nMilliseconds || 0;\n      cExpr = cExpr.cExpr;\n    }\n    if (typeof cExpr !== \"string\") {\n      throw new TypeError(\"First argument of app.setTimeOut must be a string\");\n    }\n    if (typeof nMilliseconds !== \"number\") {\n      throw new TypeError(\"Second argument of app.setTimeOut must be a number\");\n    }\n    const callbackId = this._registerTimeoutCallback(cExpr);\n    this._externalCall(\"setTimeout\", [callbackId, nMilliseconds]);\n    return this._registerTimeout(callbackId, false);\n  }\n  trustedFunction() {}\n  trustPropagatorFunction() {}\n}\n\n;// ./node_modules/core-js/modules/es.json.stringify.js\nes_json_stringify_namespaceFn();\n\n;// ./src/scripting_api/console.js\n\n\nclass Console extends PDFObject {\n  clear() {\n    this._send({\n      id: \"clear\"\n    });\n  }\n  hide() {}\n  println(msg) {\n    if (typeof msg !== \"string\") {\n      try {\n        msg = JSON.stringify(msg);\n      } catch {\n        msg = msg.toString?.() || \"[Unserializable object]\";\n      }\n    }\n    this._send({\n      command: \"println\",\n      value: \"PDF.js Console:: \" + msg\n    });\n  }\n  show() {}\n}\n\n;// ./src/scripting_api/print_params.js\nclass PrintParams {\n  binaryOk = true;\n  bitmapDPI = 150;\n  booklet = {\n    binding: 0,\n    duplexMode: 0,\n    subsetFrom: 0,\n    subsetTo: -1\n  };\n  colorOverride = 0;\n  colorProfile = \"\";\n  constants = Object.freeze({\n    bookletBindings: Object.freeze({\n      Left: 0,\n      Right: 1,\n      LeftTall: 2,\n      RightTall: 3\n    }),\n    bookletDuplexMode: Object.freeze({\n      BothSides: 0,\n      FrontSideOnly: 1,\n      BasicSideOnly: 2\n    }),\n    colorOverrides: Object.freeze({\n      auto: 0,\n      gray: 1,\n      mono: 2\n    }),\n    fontPolicies: Object.freeze({\n      everyPage: 0,\n      jobStart: 1,\n      pageRange: 2\n    }),\n    handling: Object.freeze({\n      none: 0,\n      fit: 1,\n      shrink: 2,\n      tileAll: 3,\n      tileLarge: 4,\n      nUp: 5,\n      booklet: 6\n    }),\n    interactionLevel: Object.freeze({\n      automatic: 0,\n      full: 1,\n      silent: 2\n    }),\n    nUpPageOrders: Object.freeze({\n      Horizontal: 0,\n      HorizontalReversed: 1,\n      Vertical: 2\n    }),\n    printContents: Object.freeze({\n      doc: 0,\n      docAndComments: 1,\n      formFieldsOnly: 2\n    }),\n    flagValues: Object.freeze({\n      applyOverPrint: 1,\n      applySoftProofSettings: 1 << 1,\n      applyWorkingColorSpaces: 1 << 2,\n      emitHalftones: 1 << 3,\n      emitPostScriptXObjects: 1 << 4,\n      emitFormsAsPSForms: 1 << 5,\n      maxJP2KRes: 1 << 6,\n      setPageSize: 1 << 7,\n      suppressBG: 1 << 8,\n      suppressCenter: 1 << 9,\n      suppressCJKFontSubst: 1 << 10,\n      suppressCropClip: 1 << 11,\n      suppressRotate: 1 << 12,\n      suppressTransfer: 1 << 13,\n      suppressUCR: 1 << 14,\n      useTrapAnnots: 1 << 15,\n      usePrintersMarks: 1 << 16\n    }),\n    rasterFlagValues: Object.freeze({\n      textToOutline: 1,\n      strokesToOutline: 1 << 1,\n      allowComplexClip: 1 << 2,\n      preserveOverprint: 1 << 3\n    }),\n    subsets: Object.freeze({\n      all: 0,\n      even: 1,\n      odd: 2\n    }),\n    tileMarks: Object.freeze({\n      none: 0,\n      west: 1,\n      east: 2\n    }),\n    usages: Object.freeze({\n      auto: 0,\n      use: 1,\n      noUse: 2\n    })\n  });\n  downloadFarEastFonts = false;\n  fileName = \"\";\n  firstPage = 0;\n  flags = 0;\n  fontPolicy = 0;\n  gradientDPI = 150;\n  interactive = 1;\n  npUpAutoRotate = false;\n  npUpNumPagesH = 2;\n  npUpNumPagesV = 2;\n  npUpPageBorder = false;\n  npUpPageOrder = 0;\n  pageHandling = 0;\n  pageSubset = 0;\n  printAsImage = false;\n  printContent = 0;\n  printerName = \"\";\n  psLevel = 0;\n  rasterFlags = 0;\n  reversePages = false;\n  tileLabel = false;\n  tileMark = 0;\n  tileOverlap = 0;\n  tileScale = 1.0;\n  transparencyLevel = 75;\n  usePrinterCRD = 0;\n  useT1Conversion = 0;\n  constructor(data) {\n    this.lastPage = data.lastPage;\n  }\n}\n\n;// ./src/scripting_api/doc.js\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nconst DOC_EXTERNAL = false;\nclass InfoProxyHandler {\n  static get(obj, prop) {\n    return obj[prop.toLowerCase()];\n  }\n  static set(obj, prop, value) {\n    throw new Error(`doc.info.${prop} is read-only`);\n  }\n}\nclass Doc extends PDFObject {\n  #pageActions = null;\n  #otherPageActions = null;\n  constructor(data) {\n    super(data);\n    this._expandos = globalThis;\n    this._baseURL = data.baseURL || \"\";\n    this._calculate = true;\n    this._delay = false;\n    this._dirty = false;\n    this._disclosed = false;\n    this._media = undefined;\n    this._metadata = data.metadata || \"\";\n    this._noautocomplete = undefined;\n    this._nocache = undefined;\n    this._spellDictionaryOrder = [];\n    this._spellLanguageOrder = [];\n    this._printParams = null;\n    this._fields = new Map();\n    this._fieldNames = [];\n    this._event = null;\n    this._author = data.Author || \"\";\n    this._creator = data.Creator || \"\";\n    this._creationDate = this._getDate(data.CreationDate) || null;\n    this._docID = data.docID || [\"\", \"\"];\n    this._documentFileName = data.filename || \"\";\n    this._filesize = data.filesize || 0;\n    this._keywords = data.Keywords || \"\";\n    this._layout = data.layout || \"\";\n    this._modDate = this._getDate(data.ModDate) || null;\n    this._numFields = 0;\n    this._numPages = data.numPages || 1;\n    this._pageNum = data.pageNum || 0;\n    this._producer = data.Producer || \"\";\n    this._securityHandler = data.EncryptFilterName || null;\n    this._subject = data.Subject || \"\";\n    this._title = data.Title || \"\";\n    this._URL = data.URL || \"\";\n    this._info = new Proxy({\n      title: this._title,\n      author: this._author,\n      authors: data.authors || [this._author],\n      subject: this._subject,\n      keywords: this._keywords,\n      creator: this._creator,\n      producer: this._producer,\n      creationdate: this._creationDate,\n      moddate: this._modDate,\n      trapped: data.Trapped || \"Unknown\"\n    }, InfoProxyHandler);\n    this._zoomType = ZoomType.none;\n    this._zoom = data.zoom || 100;\n    this._actions = createMap(data.actions);\n    this._globalEval = data.globalEval;\n    this._userActivation = false;\n    this._disablePrinting = false;\n    this._disableSaving = false;\n  }\n  _initActions() {\n    for (const {\n      obj\n    } of this._fields.values()) {\n      const initialValue = obj._initialValue;\n      if (initialValue) {\n        this._send({\n          id: obj._id,\n          siblings: obj._siblings,\n          value: initialValue,\n          formattedValue: obj.value.toString()\n        });\n      }\n    }\n    const dontRun = new Set([\"WillClose\", \"WillSave\", \"DidSave\", \"WillPrint\", \"DidPrint\", \"OpenAction\"]);\n    this._disableSaving = true;\n    for (const actionName of this._actions.keys()) {\n      if (!dontRun.has(actionName)) {\n        this._runActions(actionName);\n      }\n    }\n    this._runActions(\"OpenAction\");\n    this._disableSaving = false;\n  }\n  _dispatchDocEvent(name) {\n    switch (name) {\n      case \"Open\":\n        this._disableSaving = true;\n        this._runActions(\"OpenAction\");\n        this._disableSaving = false;\n        break;\n      case \"WillPrint\":\n        this._disablePrinting = true;\n        try {\n          this._runActions(name);\n        } catch (error) {\n          this._send(serializeError(error));\n        }\n        this._send({\n          command: \"WillPrintFinished\"\n        });\n        this._disablePrinting = false;\n        break;\n      case \"WillSave\":\n        this._disableSaving = true;\n        this._runActions(name);\n        this._disableSaving = false;\n        break;\n      default:\n        this._runActions(name);\n    }\n  }\n  _dispatchPageEvent(name, actions, pageNumber) {\n    if (name === \"PageOpen\") {\n      this.#pageActions ??= new Map();\n      this.#pageActions.getOrInsertComputed(pageNumber, () => createMap(actions));\n      this._pageNum = pageNumber - 1;\n    }\n    for (const acts of [this.#pageActions, this.#otherPageActions]) {\n      actions = acts?.get(pageNumber)?.get(name);\n      if (actions) {\n        for (const action of actions) {\n          this._globalEval(action);\n        }\n      }\n    }\n  }\n  _runActions(name) {\n    const actions = this._actions.get(name);\n    if (!actions) {\n      return;\n    }\n    for (const action of actions) {\n      try {\n        this._globalEval(action);\n      } catch (error) {\n        const serializedError = serializeError(error);\n        serializedError.value = `Error when executing \"${name}\" for document\\n${serializedError.value}`;\n        this._send(serializedError);\n      }\n    }\n  }\n  _addField(name, field) {\n    this._fields.set(name, field);\n    this._fieldNames.push(name);\n    this._numFields++;\n    const po = field.obj._actions.get(\"PageOpen\");\n    const pc = field.obj._actions.get(\"PageClose\");\n    if (po || pc) {\n      this.#otherPageActions ??= new Map();\n      const actions = this.#otherPageActions.getOrInsertComputed(field.obj._page + 1, makeMap);\n      if (po) {\n        actions.getOrInsertComputed(\"PageOpen\", makeArr).push(...po);\n      }\n      if (pc) {\n        actions.getOrInsertComputed(\"PageClose\", makeArr).push(...pc);\n      }\n    }\n  }\n  _getDate(date) {\n    if (!date || date.length < 15 || !date.startsWith(\"D:\")) {\n      return date;\n    }\n    date = date.substring(2);\n    const year = date.substring(0, 4);\n    const month = date.substring(4, 6);\n    const day = date.substring(6, 8);\n    const hour = date.substring(8, 10);\n    const minute = date.substring(10, 12);\n    const o = date.charAt(12);\n    let second, offsetPos;\n    if (o === \"Z\" || o === \"+\" || o === \"-\") {\n      second = \"00\";\n      offsetPos = 12;\n    } else {\n      second = date.substring(12, 14);\n      offsetPos = 14;\n    }\n    const offset = date.substring(offsetPos).replaceAll(\"'\", \"\");\n    return new Date(`${year}-${month}-${day}T${hour}:${minute}:${second}${offset}`);\n  }\n  get author() {\n    return this._author;\n  }\n  set author(_) {\n    throw new Error(\"doc.author is read-only\");\n  }\n  get baseURL() {\n    return this._baseURL;\n  }\n  set baseURL(baseURL) {\n    this._baseURL = baseURL;\n  }\n  get bookmarkRoot() {\n    return undefined;\n  }\n  set bookmarkRoot(_) {\n    throw new Error(\"doc.bookmarkRoot is read-only\");\n  }\n  get calculate() {\n    return this._calculate;\n  }\n  set calculate(calculate) {\n    this._calculate = calculate;\n  }\n  get creator() {\n    return this._creator;\n  }\n  set creator(_) {\n    throw new Error(\"doc.creator is read-only\");\n  }\n  get dataObjects() {\n    return [];\n  }\n  set dataObjects(_) {\n    throw new Error(\"doc.dataObjects is read-only\");\n  }\n  get delay() {\n    return this._delay;\n  }\n  set delay(delay) {\n    this._delay = delay;\n  }\n  get dirty() {\n    return this._dirty;\n  }\n  set dirty(dirty) {\n    this._dirty = dirty;\n  }\n  get disclosed() {\n    return this._disclosed;\n  }\n  set disclosed(disclosed) {\n    this._disclosed = disclosed;\n  }\n  get docID() {\n    return this._docID;\n  }\n  set docID(_) {\n    throw new Error(\"doc.docID is read-only\");\n  }\n  get documentFileName() {\n    return this._documentFileName;\n  }\n  set documentFileName(_) {\n    throw new Error(\"doc.documentFileName is read-only\");\n  }\n  get dynamicXFAForm() {\n    return false;\n  }\n  set dynamicXFAForm(_) {\n    throw new Error(\"doc.dynamicXFAForm is read-only\");\n  }\n  get external() {\n    return DOC_EXTERNAL;\n  }\n  set external(_) {\n    throw new Error(\"doc.external is read-only\");\n  }\n  get filesize() {\n    return this._filesize;\n  }\n  set filesize(_) {\n    throw new Error(\"doc.filesize is read-only\");\n  }\n  get hidden() {\n    return false;\n  }\n  set hidden(_) {\n    throw new Error(\"doc.hidden is read-only\");\n  }\n  get hostContainer() {\n    return undefined;\n  }\n  set hostContainer(_) {\n    throw new Error(\"doc.hostContainer is read-only\");\n  }\n  get icons() {\n    return undefined;\n  }\n  set icons(_) {\n    throw new Error(\"doc.icons is read-only\");\n  }\n  get info() {\n    return this._info;\n  }\n  set info(_) {\n    throw new Error(\"doc.info is read-only\");\n  }\n  get innerAppWindowRect() {\n    return [0, 0, 0, 0];\n  }\n  set innerAppWindowRect(_) {\n    throw new Error(\"doc.innerAppWindowRect is read-only\");\n  }\n  get innerDocWindowRect() {\n    return [0, 0, 0, 0];\n  }\n  set innerDocWindowRect(_) {\n    throw new Error(\"doc.innerDocWindowRect is read-only\");\n  }\n  get isModal() {\n    return false;\n  }\n  set isModal(_) {\n    throw new Error(\"doc.isModal is read-only\");\n  }\n  get keywords() {\n    return this._keywords;\n  }\n  set keywords(_) {\n    throw new Error(\"doc.keywords is read-only\");\n  }\n  get layout() {\n    return this._layout;\n  }\n  set layout(value) {\n    if (!this._userActivation) {\n      return;\n    }\n    this._userActivation = false;\n    if (typeof value !== \"string\") {\n      return;\n    }\n    if (value !== \"SinglePage\" && value !== \"OneColumn\" && value !== \"TwoColumnLeft\" && value !== \"TwoPageLeft\" && value !== \"TwoColumnRight\" && value !== \"TwoPageRight\") {\n      value = \"SinglePage\";\n    }\n    this._send({\n      command: \"layout\",\n      value\n    });\n    this._layout = value;\n  }\n  get media() {\n    return this._media;\n  }\n  set media(media) {\n    this._media = media;\n  }\n  get metadata() {\n    return this._metadata;\n  }\n  set metadata(metadata) {\n    this._metadata = metadata;\n  }\n  get modDate() {\n    return this._modDate;\n  }\n  set modDate(_) {\n    throw new Error(\"doc.modDate is read-only\");\n  }\n  get mouseX() {\n    return 0;\n  }\n  set mouseX(_) {\n    throw new Error(\"doc.mouseX is read-only\");\n  }\n  get mouseY() {\n    return 0;\n  }\n  set mouseY(_) {\n    throw new Error(\"doc.mouseY is read-only\");\n  }\n  get noautocomplete() {\n    return this._noautocomplete;\n  }\n  set noautocomplete(noautocomplete) {\n    this._noautocomplete = noautocomplete;\n  }\n  get nocache() {\n    return this._nocache;\n  }\n  set nocache(nocache) {\n    this._nocache = nocache;\n  }\n  get numFields() {\n    return this._numFields;\n  }\n  set numFields(_) {\n    throw new Error(\"doc.numFields is read-only\");\n  }\n  get numPages() {\n    return this._numPages;\n  }\n  set numPages(_) {\n    throw new Error(\"doc.numPages is read-only\");\n  }\n  get numTemplates() {\n    return 0;\n  }\n  set numTemplates(_) {\n    throw new Error(\"doc.numTemplates is read-only\");\n  }\n  get outerAppWindowRect() {\n    return [0, 0, 0, 0];\n  }\n  set outerAppWindowRect(_) {\n    throw new Error(\"doc.outerAppWindowRect is read-only\");\n  }\n  get outerDocWindowRect() {\n    return [0, 0, 0, 0];\n  }\n  set outerDocWindowRect(_) {\n    throw new Error(\"doc.outerDocWindowRect is read-only\");\n  }\n  get pageNum() {\n    return this._pageNum;\n  }\n  set pageNum(value) {\n    if (!this._userActivation) {\n      return;\n    }\n    this._userActivation = false;\n    if (typeof value !== \"number\" || value < 0 || value >= this._numPages) {\n      return;\n    }\n    this._send({\n      command: \"page-num\",\n      value\n    });\n    this._pageNum = value;\n  }\n  get pageWindowRect() {\n    return [0, 0, 0, 0];\n  }\n  set pageWindowRect(_) {\n    throw new Error(\"doc.pageWindowRect is read-only\");\n  }\n  get path() {\n    return \"\";\n  }\n  set path(_) {\n    throw new Error(\"doc.path is read-only\");\n  }\n  get permStatusReady() {\n    return true;\n  }\n  set permStatusReady(_) {\n    throw new Error(\"doc.permStatusReady is read-only\");\n  }\n  get producer() {\n    return this._producer;\n  }\n  set producer(_) {\n    throw new Error(\"doc.producer is read-only\");\n  }\n  get requiresFullSave() {\n    return false;\n  }\n  set requiresFullSave(_) {\n    throw new Error(\"doc.requiresFullSave is read-only\");\n  }\n  get securityHandler() {\n    return this._securityHandler;\n  }\n  set securityHandler(_) {\n    throw new Error(\"doc.securityHandler is read-only\");\n  }\n  get selectedAnnots() {\n    return [];\n  }\n  set selectedAnnots(_) {\n    throw new Error(\"doc.selectedAnnots is read-only\");\n  }\n  get sounds() {\n    return [];\n  }\n  set sounds(_) {\n    throw new Error(\"doc.sounds is read-only\");\n  }\n  get spellDictionaryOrder() {\n    return this._spellDictionaryOrder;\n  }\n  set spellDictionaryOrder(spellDictionaryOrder) {\n    this._spellDictionaryOrder = spellDictionaryOrder;\n  }\n  get spellLanguageOrder() {\n    return this._spellLanguageOrder;\n  }\n  set spellLanguageOrder(spellLanguageOrder) {\n    this._spellLanguageOrder = spellLanguageOrder;\n  }\n  get subject() {\n    return this._subject;\n  }\n  set subject(_) {\n    throw new Error(\"doc.subject is read-only\");\n  }\n  get templates() {\n    return [];\n  }\n  set templates(_) {\n    throw new Error(\"doc.templates is read-only\");\n  }\n  get title() {\n    return this._title;\n  }\n  set title(_) {\n    throw new Error(\"doc.title is read-only\");\n  }\n  get URL() {\n    return this._URL;\n  }\n  set URL(_) {\n    throw new Error(\"doc.URL is read-only\");\n  }\n  get viewState() {\n    return undefined;\n  }\n  set viewState(_) {\n    throw new Error(\"doc.viewState is read-only\");\n  }\n  get xfa() {\n    return this._xfa;\n  }\n  set xfa(_) {\n    throw new Error(\"doc.xfa is read-only\");\n  }\n  get XFAForeground() {\n    return false;\n  }\n  set XFAForeground(_) {\n    throw new Error(\"doc.XFAForeground is read-only\");\n  }\n  get zoomType() {\n    return this._zoomType;\n  }\n  set zoomType(type) {\n    if (!this._userActivation) {\n      return;\n    }\n    this._userActivation = false;\n    if (typeof type !== \"string\") {\n      return;\n    }\n    switch (type) {\n      case ZoomType.none:\n        this._send({\n          command: \"zoom\",\n          value: 1\n        });\n        break;\n      case ZoomType.fitP:\n        this._send({\n          command: \"zoom\",\n          value: \"page-fit\"\n        });\n        break;\n      case ZoomType.fitW:\n        this._send({\n          command: \"zoom\",\n          value: \"page-width\"\n        });\n        break;\n      case ZoomType.fitH:\n        this._send({\n          command: \"zoom\",\n          value: \"page-height\"\n        });\n        break;\n      case ZoomType.fitV:\n        this._send({\n          command: \"zoom\",\n          value: \"auto\"\n        });\n        break;\n      case ZoomType.pref:\n      case ZoomType.refW:\n        break;\n      default:\n        return;\n    }\n    this._zoomType = type;\n  }\n  get zoom() {\n    return this._zoom;\n  }\n  set zoom(value) {\n    if (!this._userActivation) {\n      return;\n    }\n    this._userActivation = false;\n    if (typeof value !== \"number\" || value < 8.33 || value > 6400) {\n      return;\n    }\n    this._send({\n      command: \"zoom\",\n      value: value / 100\n    });\n  }\n  addAnnot() {}\n  addField() {}\n  addIcon() {}\n  addLink() {}\n  addRecipientListCryptFilter() {}\n  addRequirement() {}\n  addScript() {}\n  addThumbnails() {}\n  addWatermarkFromFile() {}\n  addWatermarkFromText() {}\n  addWeblinks() {}\n  bringToFront() {}\n  calculateNow() {\n    this._eventDispatcher.calculateNow();\n  }\n  closeDoc() {}\n  colorConvertPage() {}\n  createDataObject() {}\n  createTemplate() {}\n  deletePages() {}\n  deleteSound() {}\n  embedDocAsDataObject() {}\n  embedOutputIntent() {}\n  encryptForRecipients() {}\n  encryptUsingPolicy() {}\n  exportAsFDF() {}\n  exportAsFDFStr() {}\n  exportAsText() {}\n  exportAsXFDF() {}\n  exportAsXFDFStr() {}\n  exportDataObject() {}\n  exportXFAData() {}\n  extractPages() {}\n  flattenPages() {}\n  getAnnot() {}\n  getAnnots() {}\n  getAnnot3D() {}\n  getAnnots3D() {}\n  getColorConvertAction() {}\n  getDataObject() {}\n  getDataObjectContents() {}\n  _getField(cName) {\n    if (cName && typeof cName === \"object\") {\n      cName = cName.cName;\n    }\n    if (typeof cName !== \"string\") {\n      throw new TypeError(\"Invalid field name: must be a string\");\n    }\n    const searchedField = this._fields.get(cName);\n    if (searchedField) {\n      return searchedField;\n    }\n    const parts = cName.split(\"#\");\n    let childIndex = NaN;\n    if (parts.length === 2) {\n      childIndex = Math.floor(parseFloat(parts[1]));\n      cName = parts[0];\n    }\n    for (const [name, field] of this._fields) {\n      if (name.endsWith(cName)) {\n        if (!isNaN(childIndex)) {\n          const children = this._getChildren(name);\n          if (childIndex < 0 || childIndex >= children.length) {\n            childIndex = 0;\n          }\n          if (childIndex < children.length) {\n            this._fields.set(cName, children[childIndex]);\n            return children[childIndex];\n          }\n        }\n        this._fields.set(cName, field);\n        return field;\n      }\n    }\n    return null;\n  }\n  getField(cName) {\n    const field = this._getField(cName);\n    return !field ? null : field.wrapped;\n  }\n  _getChildren(fieldName) {\n    const len = fieldName.length;\n    const children = [];\n    const pattern = /^\\.[^.]+$/;\n    for (const [name, field] of this._fields) {\n      if (name.startsWith(fieldName)) {\n        const finalPart = name.slice(len);\n        if (pattern.test(finalPart)) {\n          children.push(field);\n        }\n      }\n    }\n    return children;\n  }\n  _getTerminalChildren(fieldName) {\n    const children = [];\n    const len = fieldName.length;\n    for (const [name, field] of this._fields) {\n      if (name.startsWith(fieldName)) {\n        const finalPart = name.slice(len);\n        if (field.obj._hasValue && (finalPart === \"\" || finalPart.startsWith(\".\"))) {\n          children.push(field.wrapped);\n        }\n      }\n    }\n    return children;\n  }\n  getIcon() {}\n  getLegalWarnings() {}\n  getLinks() {}\n  getNthFieldName(nIndex) {\n    if (nIndex && typeof nIndex === \"object\") {\n      nIndex = nIndex.nIndex;\n    }\n    if (typeof nIndex !== \"number\") {\n      throw new TypeError(\"Invalid field index: must be a number\");\n    }\n    return nIndex >= 0 && nIndex < this.numFields ? this._fieldNames[Math.trunc(nIndex)] : null;\n  }\n  getNthTemplate() {\n    return null;\n  }\n  getOCGs() {}\n  getOCGOrder() {}\n  getPageBox() {}\n  getPageLabel() {}\n  getPageNthWord() {}\n  getPageNthWordQuads() {}\n  getPageNumWords() {}\n  getPageRotation() {}\n  getPageTransition() {}\n  getPrintParams() {\n    return this._printParams ||= new PrintParams({\n      lastPage: this._numPages - 1\n    });\n  }\n  getSound() {}\n  getTemplate() {}\n  getURL() {}\n  gotoNamedDest() {}\n  importAnFDF() {}\n  importAnXFDF() {}\n  importDataObject() {}\n  importIcon() {}\n  importSound() {}\n  importTextData() {}\n  importXFAData() {}\n  insertPages() {}\n  mailDoc() {}\n  mailForm() {}\n  movePage() {}\n  newPage() {}\n  openDataObject() {}\n  print(bUI = true, nStart = 0, nEnd = -1, bSilent = false, bShrinkToFit = false, bPrintAsImage = false, bReverse = false, bAnnotations = true, printParams = null) {\n    if (this._disablePrinting || !this._userActivation) {\n      return;\n    }\n    this._userActivation = false;\n    if (bUI && typeof bUI === \"object\") {\n      nStart = bUI.nStart;\n      nEnd = bUI.nEnd;\n      bSilent = bUI.bSilent;\n      bShrinkToFit = bUI.bShrinkToFit;\n      bPrintAsImage = bUI.bPrintAsImage;\n      bReverse = bUI.bReverse;\n      bAnnotations = bUI.bAnnotations;\n      printParams = bUI.printParams;\n      bUI = bUI.bUI;\n    }\n    if (printParams) {\n      nStart = printParams.firstPage;\n      nEnd = printParams.lastPage;\n    }\n    nStart = typeof nStart === \"number\" ? Math.max(0, Math.trunc(nStart)) : 0;\n    nEnd = typeof nEnd === \"number\" ? Math.max(0, Math.trunc(nEnd)) : -1;\n    this._send({\n      command: \"print\",\n      start: nStart,\n      end: nEnd\n    });\n  }\n  removeDataObject() {}\n  removeField() {}\n  removeIcon() {}\n  removeLinks() {}\n  removeRequirement() {}\n  removeScript() {}\n  removeTemplate() {}\n  removeThumbnails() {}\n  removeWeblinks() {}\n  replacePages() {}\n  resetForm(aFields = null) {\n    if (aFields && typeof aFields === \"object\" && !Array.isArray(aFields)) {\n      aFields = aFields.aFields;\n    }\n    if (aFields && !Array.isArray(aFields)) {\n      aFields = [aFields];\n    }\n    let mustCalculate = false;\n    let fieldsToReset;\n    if (aFields) {\n      fieldsToReset = new Set();\n      for (const fieldName of aFields) {\n        if (!fieldName) {\n          continue;\n        }\n        if (typeof fieldName !== \"string\") {\n          fieldsToReset = null;\n          break;\n        }\n        const field = this._getField(fieldName);\n        if (!field) {\n          continue;\n        }\n        fieldsToReset.add(field);\n        mustCalculate = true;\n      }\n    }\n    if (fieldsToReset) {\n      for (const {\n        obj\n      } of fieldsToReset) {\n        for (const id of obj._kidIds || []) {\n          const kid = obj._appObjects[id];\n          if (kid) {\n            fieldsToReset.add(kid);\n          }\n        }\n      }\n    } else {\n      fieldsToReset = this._fields.values();\n      mustCalculate = this._fields.size !== 0;\n    }\n    for (const field of fieldsToReset) {\n      field.obj.value = field.obj.defaultValue;\n      this._send({\n        id: field.obj._id,\n        siblings: field.obj._siblings,\n        value: field.obj.defaultValue,\n        formattedValue: null,\n        selRange: [0, 0]\n      });\n    }\n    if (mustCalculate) {\n      this.calculateNow();\n    }\n  }\n  saveAs() {}\n  scroll() {}\n  selectPageNthWord() {}\n  setAction() {}\n  setDataObjectContents() {}\n  setOCGOrder() {}\n  setPageAction() {}\n  setPageBoxes() {}\n  setPageLabels() {}\n  setPageRotations() {}\n  setPageTabOrder() {}\n  setPageTransitions() {}\n  spawnPageFromTemplate() {}\n  submitForm() {}\n  syncAnnotScan() {}\n}\n\n;// ./src/scripting_api/proxy.js\n\n\n\n\n\n\n\n\n\n\nclass ProxyHandler {\n  nosend = new Set([\"delay\"]);\n  get(obj, prop) {\n    if (prop in obj._expandos) {\n      const val = obj._expandos[prop];\n      return typeof val === \"function\" ? val.bind(obj) : val;\n    }\n    if (typeof prop === \"string\" && !prop.startsWith(\"_\") && prop in obj) {\n      const val = obj[prop];\n      return typeof val === \"function\" ? val.bind(obj) : val;\n    }\n    return undefined;\n  }\n  set(obj, prop, value) {\n    obj._kidIds?.forEach(id => {\n      obj._appObjects[id].wrapped[prop] = value;\n    });\n    if (typeof prop === \"string\" && !prop.startsWith(\"_\") && prop in obj) {\n      const old = obj[prop];\n      obj[prop] = value;\n      if (!this.nosend.has(prop) && obj._send && obj._id !== null && typeof old !== \"function\") {\n        const data = {\n          id: obj._id\n        };\n        data[prop] = prop === \"value\" ? obj._getValue() : obj[prop];\n        if (!obj._siblings) {\n          obj._send(data);\n        } else {\n          data.siblings = obj._siblings;\n          obj._send(data);\n        }\n      }\n    } else {\n      obj._expandos[prop] = value;\n    }\n    return true;\n  }\n  has(obj, prop) {\n    return prop in obj._expandos || typeof prop === \"string\" && !prop.startsWith(\"_\") && prop in obj;\n  }\n  getPrototypeOf(obj) {\n    return null;\n  }\n  setPrototypeOf(obj, proto) {\n    return false;\n  }\n  isExtensible(obj) {\n    return true;\n  }\n  preventExtensions(obj) {\n    return false;\n  }\n  getOwnPropertyDescriptor(obj, prop) {\n    if (prop in obj._expandos) {\n      return {\n        configurable: true,\n        enumerable: true,\n        value: obj._expandos[prop]\n      };\n    }\n    return typeof prop === \"string\" && !prop.startsWith(\"_\") && prop in obj ? {\n      configurable: true,\n      enumerable: true,\n      value: obj[prop]\n    } : undefined;\n  }\n  defineProperty(obj, key, descriptor) {\n    Object.defineProperty(obj._expandos, key, descriptor);\n    return true;\n  }\n  deleteProperty(obj, prop) {\n    if (prop in obj._expandos) {\n      delete obj._expandos[prop];\n    }\n  }\n  ownKeys(obj) {\n    const fromExpandos = Reflect.ownKeys(obj._expandos);\n    const fromObj = Reflect.ownKeys(obj).filter(k => !k.startsWith(\"_\"));\n    return fromExpandos.concat(fromObj);\n  }\n}\n\n;// ./src/scripting_api/util.js\n\n\n\n\n\n\n\n\nclass Util extends PDFObject {\n  #createDateActionsBound = this.#createDateActions.bind(this);\n  #createScandDataBound = this.#createScandData.bind(this);\n  #dateActionsCache = null;\n  #scandCache = null;\n  #months = [\"January\", \"February\", \"March\", \"April\", \"May\", \"June\", \"July\", \"August\", \"September\", \"October\", \"November\", \"December\"];\n  #days = [\"Sunday\", \"Monday\", \"Tuesday\", \"Wednesday\", \"Thursday\", \"Friday\", \"Saturday\"];\n  MILLISECONDS_IN_DAY = 86400000;\n  MILLISECONDS_IN_WEEK = 604800000;\n  constructor(data) {\n    super(data);\n    this._externalCall = data.externalCall;\n  }\n  printf(...args) {\n    if (args.length === 0) {\n      throw new Error(\"Invalid number of params in printf\");\n    }\n    if (typeof args[0] !== \"string\") {\n      throw new TypeError(\"First argument of printf must be a string\");\n    }\n    const pattern = /%(,[0-4])?([+ 0#]+)?(\\d+)?(\\.\\d+)?(.)/g;\n    const PLUS = 1;\n    const SPACE = 2;\n    const ZERO = 4;\n    const HASH = 8;\n    let i = 0;\n    return args[0].replaceAll(pattern, function (_, nDecSep, cFlags, nWidth, nPrecision, cConvChar) {\n      if (cConvChar !== \"d\" && cConvChar !== \"f\" && cConvChar !== \"s\" && cConvChar !== \"x\") {\n        const buf = [\"%\"];\n        for (const str of [nDecSep, cFlags, nWidth, nPrecision, cConvChar]) {\n          if (str) {\n            buf.push(str);\n          }\n        }\n        return buf.join(\"\");\n      }\n      i++;\n      if (i === args.length) {\n        throw new Error(\"Not enough arguments in printf\");\n      }\n      const arg = args[i];\n      if (cConvChar === \"s\") {\n        return arg.toString();\n      }\n      let flags = 0;\n      if (cFlags) {\n        for (const flag of cFlags) {\n          switch (flag) {\n            case \"+\":\n              flags |= PLUS;\n              break;\n            case \" \":\n              flags |= SPACE;\n              break;\n            case \"0\":\n              flags |= ZERO;\n              break;\n            case \"#\":\n              flags |= HASH;\n              break;\n          }\n        }\n      }\n      cFlags = flags;\n      nWidth &&= parseInt(nWidth);\n      let intPart = Math.trunc(arg);\n      if (cConvChar === \"x\") {\n        let hex = Math.abs(intPart).toString(16).toUpperCase();\n        if (nWidth !== undefined) {\n          hex = hex.padStart(nWidth, cFlags & ZERO ? \"0\" : \" \");\n        }\n        if (cFlags & HASH) {\n          hex = `0x${hex}`;\n        }\n        return hex;\n      }\n      nPrecision &&= parseInt(nPrecision.substring(1));\n      nDecSep = nDecSep ? nDecSep.substring(1) : \"0\";\n      const separators = {\n        0: [\",\", \".\"],\n        1: [\"\", \".\"],\n        2: [\".\", \",\"],\n        3: [\"\", \",\"],\n        4: [\"'\", \".\"]\n      };\n      const [thousandSep, decimalSep] = separators[nDecSep];\n      let decPart = \"\";\n      if (cConvChar === \"f\") {\n        decPart = nPrecision !== undefined ? Math.abs(arg - intPart).toFixed(nPrecision) : Math.abs(arg - intPart).toString();\n        if (decPart.length > 2) {\n          if (/^1\\.0+$/.test(decPart)) {\n            intPart += Math.sign(arg);\n            decPart = `${decimalSep}${decPart.split(\".\")[1]}`;\n          } else {\n            decPart = `${decimalSep}${decPart.substring(2)}`;\n          }\n        } else {\n          if (decPart === \"1\") {\n            intPart += Math.sign(arg);\n          }\n          decPart = cFlags & HASH ? \".\" : \"\";\n        }\n      }\n      let sign = \"\";\n      if (intPart < 0) {\n        sign = \"-\";\n        intPart = -intPart;\n      } else if (cFlags & PLUS) {\n        sign = \"+\";\n      } else if (cFlags & SPACE) {\n        sign = \" \";\n      }\n      if (thousandSep && intPart >= 1000) {\n        const buf = [];\n        while (true) {\n          buf.push((intPart % 1000).toString().padStart(3, \"0\"));\n          intPart = Math.trunc(intPart / 1000);\n          if (intPart < 1000) {\n            buf.push(intPart.toString());\n            break;\n          }\n        }\n        intPart = buf.reverse().join(thousandSep);\n      } else {\n        intPart = intPart.toString();\n      }\n      let n = `${intPart}${decPart}`;\n      if (nWidth !== undefined) {\n        n = n.padStart(nWidth - sign.length, cFlags & ZERO ? \"0\" : \" \");\n      }\n      return `${sign}${n}`;\n    });\n  }\n  iconStreamFromIcon() {}\n  printd(cFormat, oDate) {\n    switch (cFormat) {\n      case 0:\n        return this.printd(\"D:yyyymmddHHMMss\", oDate);\n      case 1:\n        return this.printd(\"yyyy.mm.dd HH:MM:ss\", oDate);\n      case 2:\n        return this.printd(\"m/d/yy h:MM:ss tt\", oDate);\n    }\n    const handlers = {\n      mmmm: data => this.#months[data.month],\n      mmm: data => this.#months[data.month].substring(0, 3),\n      mm: data => (data.month + 1).toString().padStart(2, \"0\"),\n      m: data => (data.month + 1).toString(),\n      dddd: data => this.#days[data.dayOfWeek],\n      ddd: data => this.#days[data.dayOfWeek].substring(0, 3),\n      dd: data => data.day.toString().padStart(2, \"0\"),\n      d: data => data.day.toString(),\n      yyyy: data => data.year.toString().padStart(4, \"0\"),\n      yy: data => (data.year % 100).toString().padStart(2, \"0\"),\n      HH: data => data.hours.toString().padStart(2, \"0\"),\n      H: data => data.hours.toString(),\n      hh: data => (1 + (data.hours + 11) % 12).toString().padStart(2, \"0\"),\n      h: data => (1 + (data.hours + 11) % 12).toString(),\n      MM: data => data.minutes.toString().padStart(2, \"0\"),\n      M: data => data.minutes.toString(),\n      ss: data => data.seconds.toString().padStart(2, \"0\"),\n      s: data => data.seconds.toString(),\n      tt: data => data.hours < 12 ? \"am\" : \"pm\",\n      t: data => data.hours < 12 ? \"a\" : \"p\"\n    };\n    const data = {\n      year: oDate.getFullYear(),\n      month: oDate.getMonth(),\n      day: oDate.getDate(),\n      dayOfWeek: oDate.getDay(),\n      hours: oDate.getHours(),\n      minutes: oDate.getMinutes(),\n      seconds: oDate.getSeconds()\n    };\n    const patterns = /(mmmm|mmm|mm|m|dddd|ddd|dd|d|yyyy|yy|HH|H|hh|h|MM|M|ss|s|tt|t|\\\\.)/g;\n    return cFormat.replaceAll(patterns, (_, pattern) => pattern in handlers ? handlers[pattern](data) : pattern.charAt(1));\n  }\n  printx(cFormat, cSource) {\n    cSource = (cSource ?? \"\").toString();\n    const handlers = [x => x, x => x.toUpperCase(), x => x.toLowerCase()];\n    const buf = [];\n    let i = 0;\n    const ii = cSource.length;\n    let currCase = handlers[0];\n    let escaped = false;\n    for (const command of cFormat) {\n      if (escaped) {\n        buf.push(command);\n        escaped = false;\n        continue;\n      }\n      if (i >= ii) {\n        break;\n      }\n      switch (command) {\n        case \"?\":\n          buf.push(currCase(cSource.charAt(i++)));\n          break;\n        case \"X\":\n          while (i < ii) {\n            const char = cSource.charAt(i++);\n            if (\"a\" <= char && char <= \"z\" || \"A\" <= char && char <= \"Z\" || \"0\" <= char && char <= \"9\") {\n              buf.push(currCase(char));\n              break;\n            }\n          }\n          break;\n        case \"A\":\n          while (i < ii) {\n            const char = cSource.charAt(i++);\n            if (\"a\" <= char && char <= \"z\" || \"A\" <= char && char <= \"Z\") {\n              buf.push(currCase(char));\n              break;\n            }\n          }\n          break;\n        case \"9\":\n          while (i < ii) {\n            const char = cSource.charAt(i++);\n            if (\"0\" <= char && char <= \"9\") {\n              buf.push(char);\n              break;\n            }\n          }\n          break;\n        case \"*\":\n          while (i < ii) {\n            buf.push(currCase(cSource.charAt(i++)));\n          }\n          break;\n        case \"\\\\\":\n          escaped = true;\n          break;\n        case \">\":\n          currCase = handlers[1];\n          break;\n        case \"<\":\n          currCase = handlers[2];\n          break;\n        case \"=\":\n          currCase = handlers[0];\n          break;\n        default:\n          buf.push(command);\n      }\n    }\n    return buf.join(\"\");\n  }\n  #createDateActions(cFormat) {\n    const actions = [];\n    cFormat.replaceAll(/(d+)|(m+)|(y+)|(H+)|(M+)|(s+)/g, function (_, d, m, y, H, M, s) {\n      if (d) {\n        actions.push((n, data) => {\n          if (n >= 1 && n <= 31) {\n            data.day = n;\n            return true;\n          }\n          return false;\n        });\n      } else if (m) {\n        actions.push((n, data) => {\n          if (n >= 1 && n <= 12) {\n            data.month = n - 1;\n            return true;\n          }\n          return false;\n        });\n      } else if (y) {\n        actions.push((n, data) => {\n          if (n < 50) {\n            n += 2000;\n          } else if (n < 100) {\n            n += 1900;\n          }\n          data.year = n;\n          return true;\n        });\n      } else if (H) {\n        actions.push((n, data) => {\n          if (n >= 0 && n <= 23) {\n            data.hours = n;\n            return true;\n          }\n          return false;\n        });\n      } else if (M) {\n        actions.push((n, data) => {\n          if (n >= 0 && n <= 59) {\n            data.minutes = n;\n            return true;\n          }\n          return false;\n        });\n      } else if (s) {\n        actions.push((n, data) => {\n          if (n >= 0 && n <= 59) {\n            data.seconds = n;\n            return true;\n          }\n          return false;\n        });\n      }\n      return \"\";\n    });\n    return actions;\n  }\n  #tryToGuessDate(cFormat, cDate) {\n    const actions = (this.#dateActionsCache ??= new Map()).getOrInsertComputed(cFormat, this.#createDateActionsBound);\n    const number = /\\d+/g;\n    let i = 0;\n    let array;\n    const data = {\n      year: new Date().getFullYear(),\n      month: 0,\n      day: 1,\n      hours: 12,\n      minutes: 0,\n      seconds: 0\n    };\n    while ((array = number.exec(cDate)) !== null) {\n      if (i < actions.length) {\n        if (!actions[i++](parseInt(array[0]), data)) {\n          return null;\n        }\n      } else {\n        break;\n      }\n    }\n    if (i === 0) {\n      return null;\n    }\n    return new Date(data.year, data.month, data.day, data.hours, data.minutes, data.seconds);\n  }\n  scand(cFormat, cDate) {\n    return this._scand(cFormat, cDate);\n  }\n  #createScandData(cFormat) {\n    const months = this.#months,\n      days = this.#days;\n    const handlers = {\n      mmmm: {\n        pattern: `(${months.join(\"|\")})`,\n        action: (value, data) => {\n          data.month = months.indexOf(value);\n        }\n      },\n      mmm: {\n        pattern: `(${months.map(month => month.substring(0, 3)).join(\"|\")})`,\n        action: (value, data) => {\n          data.month = months.findIndex(month => month.substring(0, 3) === value);\n        }\n      },\n      mm: {\n        pattern: `(\\\\d{2})`,\n        action: (value, data) => {\n          data.month = parseInt(value) - 1;\n        }\n      },\n      m: {\n        pattern: `(\\\\d{1,2})`,\n        action: (value, data) => {\n          data.month = parseInt(value) - 1;\n        }\n      },\n      dddd: {\n        pattern: `(${days.join(\"|\")})`,\n        action: (value, data) => {\n          data.day = days.indexOf(value);\n        }\n      },\n      ddd: {\n        pattern: `(${days.map(day => day.substring(0, 3)).join(\"|\")})`,\n        action: (value, data) => {\n          data.day = days.findIndex(day => day.substring(0, 3) === value);\n        }\n      },\n      dd: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.day = parseInt(value);\n        }\n      },\n      d: {\n        pattern: \"(\\\\d{1,2})\",\n        action: (value, data) => {\n          data.day = parseInt(value);\n        }\n      },\n      yyyy: {\n        pattern: \"(\\\\d{4})\",\n        action: (value, data) => {\n          data.year = parseInt(value);\n        }\n      },\n      yy: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.year = 2000 + parseInt(value);\n        }\n      },\n      HH: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.hours = parseInt(value);\n        }\n      },\n      H: {\n        pattern: \"(\\\\d{1,2})\",\n        action: (value, data) => {\n          data.hours = parseInt(value);\n        }\n      },\n      hh: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.hours = parseInt(value);\n        }\n      },\n      h: {\n        pattern: \"(\\\\d{1,2})\",\n        action: (value, data) => {\n          data.hours = parseInt(value);\n        }\n      },\n      MM: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.minutes = parseInt(value);\n        }\n      },\n      M: {\n        pattern: \"(\\\\d{1,2})\",\n        action: (value, data) => {\n          data.minutes = parseInt(value);\n        }\n      },\n      ss: {\n        pattern: \"(\\\\d{2})\",\n        action: (value, data) => {\n          data.seconds = parseInt(value);\n        }\n      },\n      s: {\n        pattern: \"(\\\\d{1,2})\",\n        action: (value, data) => {\n          data.seconds = parseInt(value);\n        }\n      },\n      tt: {\n        pattern: \"([aApP][mM])\",\n        action: (value, data) => {\n          const char = value.charAt(0);\n          data.am = char === \"a\" || char === \"A\";\n        }\n      },\n      t: {\n        pattern: \"([aApP])\",\n        action: (value, data) => {\n          data.am = value === \"a\" || value === \"A\";\n        }\n      }\n    };\n    const escapedFormat = cFormat.replaceAll(/[.*+\\-?^${}()|[\\]\\\\]/g, \"\\\\$&\");\n    const patterns = /(mmmm|mmm|mm|m|dddd|ddd|dd|d|yyyy|yy|HH|H|hh|h|MM|M|ss|s|tt|t)/g;\n    const actions = [];\n    const re = escapedFormat.replaceAll(patterns, function (_, patternElement) {\n      const {\n        pattern,\n        action\n      } = handlers[patternElement];\n      actions.push(action);\n      return pattern.includes(\",\") ? `(?=${pattern})\\\\${actions.length}` : pattern;\n    });\n    return [new RegExp(`^${re}$`, \"g\"), actions];\n  }\n  _scand(cFormat, cDate, strict = false) {\n    if (typeof cDate !== \"string\") {\n      return new Date(cDate);\n    }\n    if (cDate === \"\") {\n      return new Date();\n    }\n    switch (cFormat) {\n      case 0:\n        return this.scand(\"D:yyyymmddHHMMss\", cDate);\n      case 1:\n        return this.scand(\"yyyy.mm.dd HH:MM:ss\", cDate);\n      case 2:\n        return this.scand(\"m/d/yy h:MM:ss tt\", cDate);\n    }\n    const [regex, actions] = (this.#scandCache ??= new Map()).getOrInsertComputed(cFormat, this.#createScandDataBound);\n    const matches = regex.exec(cDate);\n    if (!matches || matches.length !== actions.length + 1) {\n      return strict ? null : this.#tryToGuessDate(cFormat, cDate);\n    }\n    const data = {\n      year: 2000,\n      month: 0,\n      day: 1,\n      hours: 0,\n      minutes: 0,\n      seconds: 0,\n      am: null\n    };\n    actions.forEach((action, i) => action(matches[i + 1], data));\n    if (data.am !== null) {\n      data.hours = data.hours % 12 + (data.am ? 0 : 12);\n    }\n    return new Date(data.year, data.month, data.day, data.hours, data.minutes, data.seconds);\n  }\n  spansToXML() {}\n  stringFromStream() {}\n  xmlToSpans() {}\n}\n\n;// ./src/scripting_api/initialization.js\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nfunction initSandbox(params) {\n  delete globalThis.pdfjsScripting;\n  const externalCall = globalThis.callExternalFunction;\n  delete globalThis.callExternalFunction;\n  const globalEval = code => globalThis.eval(code);\n  const send = data => externalCall(\"send\", [data]);\n  const proxyHandler = new ProxyHandler();\n  const {\n    data\n  } = params;\n  const doc = new Doc({\n    send,\n    globalEval,\n    ...data.docInfo\n  });\n  const _document = {\n    obj: doc,\n    wrapped: new Proxy(doc, proxyHandler)\n  };\n  const app = new App({\n    send,\n    globalEval,\n    externalCall,\n    _document,\n    calculationOrder: data.calculationOrder,\n    proxyHandler,\n    ...data.appInfo\n  });\n  const util = new Util({\n    externalCall\n  });\n  const appObjects = app._objects;\n  for (const [name, objs] of createMap(data.objects)) {\n    const annotations = [];\n    let container = null;\n    for (const obj of objs) {\n      if (obj.type !== \"\") {\n        annotations.push(obj);\n      } else {\n        container = obj;\n      }\n    }\n    let obj = container;\n    if (annotations.length > 0) {\n      obj = annotations[0];\n      obj.send = send;\n    }\n    obj.globalEval = globalEval;\n    obj.doc = _document;\n    obj.fieldPath = name;\n    obj.appObjects = appObjects;\n    obj.util = util;\n    const otherFields = annotations.slice(1);\n    let field;\n    switch (obj.type) {\n      case \"radiobutton\":\n        {\n          field = new RadioButtonField(otherFields, obj);\n          break;\n        }\n      case \"checkbox\":\n        {\n          field = new CheckboxField(otherFields, obj);\n          break;\n        }\n      default:\n        if (otherFields.length > 0) {\n          obj.siblings = otherFields.map(x => x.id);\n        }\n        field = new Field(obj);\n    }\n    const wrapped = new Proxy(field, proxyHandler);\n    const _object = {\n      obj: field,\n      wrapped\n    };\n    doc._addField(name, _object);\n    for (const object of objs) {\n      appObjects[object.id] = _object;\n    }\n    if (container) {\n      appObjects[container.id] = _object;\n    }\n  }\n  const color = new Color();\n  globalThis.event = null;\n  globalThis.global = Object.create(null);\n  globalThis.app = new Proxy(app, proxyHandler);\n  globalThis.color = new Proxy(color, proxyHandler);\n  globalThis.console = new Proxy(new Console({\n    send\n  }), proxyHandler);\n  globalThis.util = new Proxy(util, proxyHandler);\n  globalThis.border = Border;\n  globalThis.cursor = Cursor;\n  globalThis.display = Display;\n  globalThis.font = Font;\n  globalThis.highlight = Highlight;\n  globalThis.position = Position;\n  globalThis.scaleHow = ScaleHow;\n  globalThis.scaleWhen = ScaleWhen;\n  globalThis.style = Style;\n  globalThis.trans = Trans;\n  globalThis.zoomtype = ZoomType;\n  globalThis.ADBE = {\n    Reader_Value_Asked: true,\n    Viewer_Value_Asked: true\n  };\n  const aform = new AForm(doc, app, util, color);\n  for (const name of Object.getOwnPropertyNames(AForm.prototype)) {\n    if (name !== \"constructor\" && !name.startsWith(\"_\")) {\n      globalThis[name] = aform[name].bind(aform);\n    }\n  }\n  for (const [name, value] of Object.entries(GlobalConstants)) {\n    Object.defineProperty(globalThis, name, {\n      value,\n      writable: false\n    });\n  }\n  Object.defineProperties(globalThis, {\n    ColorConvert: {\n      value: color.convert.bind(color),\n      writable: true\n    },\n    ColorEqual: {\n      value: color.equal.bind(color),\n      writable: true\n    }\n  });\n  const properties = Object.create(null);\n  for (const name of Object.getOwnPropertyNames(Doc.prototype)) {\n    if (name === \"constructor\" || name.startsWith(\"_\")) {\n      continue;\n    }\n    const descriptor = Object.getOwnPropertyDescriptor(Doc.prototype, name);\n    if (descriptor.get) {\n      properties[name] = {\n        get: descriptor.get.bind(doc),\n        set: descriptor.set.bind(doc)\n      };\n    } else {\n      properties[name] = {\n        value: Doc.prototype[name].bind(doc)\n      };\n    }\n  }\n  Object.defineProperties(globalThis, properties);\n  const functions = {\n    dispatchEvent: app._dispatchEvent.bind(app),\n    timeoutCb: app._evalCallback.bind(app)\n  };\n  return (name, args) => {\n    try {\n      functions[name](args);\n    } catch (error) {\n      send(serializeError(error));\n    }\n  };\n}\n\n;// ./src/pdf.scripting.js\n\nglobalThis.pdfjsScripting = {\n  initSandbox: initSandbox\n};\n"];
    code.push("delete dump;");
    let success = false;
    let buf = 0;
    try {
      const sandboxData = this.support.exportValueToSandbox(data);
      code.push(`pdfjsScripting.initSandbox({ data: ${sandboxData} })`);
      buf = this._module.stringToNewUTF8(code.join("\n"));
      success = !!this._module.ccall("init", "number", ["number", "number"], [buf, this._alertOnError]);
    } catch (error) {
      console.error(error);
    } finally {
      if (buf) {
        this._module.ccall("free", "number", ["number"], [buf]);
      }
    }
    if (success) {
      this.support.commFun = this._module.cwrap("commFun", null, ["string", "string"]);
    } else {
      this.nukeSandbox();
      throw new Error("Cannot start sandbox");
    }
  }
  dispatchEvent(event) {
    this.support?.callSandboxFunction("dispatchEvent", event);
  }
  dumpMemoryUse() {
    this._module?.ccall("dumpMemoryUse", null, []);
  }
  nukeSandbox() {
    if (this._module !== null) {
      this.support.destroy();
      this.support = null;
      this._module.ccall("nukeSandbox", null, []);
      this._module = null;
    }
  }
  evalForTesting(code, key) {
    throw new Error("Not implemented: evalForTesting");
  }
}
async function QuickJSSandbox(wasmUrl = "../web/wasm/") {
  const {
    default: ModuleLoader
  } = await import(
  /*webpackIgnore: true*/
  /*@vite-ignore*/
  `${wasmUrl}quickjs-eval.js`);
  const module = await ModuleLoader();
  return new Sandbox(window, module);
}
globalThis.pdfjsSandbox = {
  QuickJSSandbox
};

export { QuickJSSandbox };

//# sourceMappingURL=pdf.sandbox.mjs.map