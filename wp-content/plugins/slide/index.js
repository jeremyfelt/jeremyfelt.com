// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles
parcelRequire = (function (modules, cache, entry, globalName) {
  // Save the require from previous bundle to this closure if any
  var previousRequire = typeof parcelRequire === 'function' && parcelRequire;
  var nodeRequire = typeof require === 'function' && require;

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire = typeof parcelRequire === 'function' && parcelRequire;
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error('Cannot find module \'' + name + '\'');
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = cache[name] = new newRequire.Module(name);

      modules[name][0].call(module.exports, localRequire, module, module.exports, this);
    }

    return cache[name].exports;

    function localRequire(x){
      return newRequire(localRequire.resolve(x));
    }

    function resolve(x){
      return modules[name][1][x] || x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [function (require, module) {
      module.exports = exports;
    }, {}];
  };

  var error;
  for (var i = 0; i < entry.length; i++) {
    try {
      newRequire(entry[i]);
    } catch (e) {
      // Save first error but execute all entries
      if (!error) {
        error = e;
      }
    }
  }

  if (entry.length) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(entry[entry.length - 1]);

    // CommonJS
    if (typeof exports === "object" && typeof module !== "undefined") {
      module.exports = mainExports;

    // RequireJS
    } else if (typeof define === "function" && define.amd) {
     define(function () {
       return mainExports;
     });

    // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }

  // Override the current require with this new one
  parcelRequire = newRequire;

  if (error) {
    // throw error from earlier, _after updating parcelRequire_
    throw error;
  }

  return newRequire;
})({"eotu":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

var _window$wp = window.wp,
    e = _window$wp.element.createElement,
    BaseControl = _window$wp.components.BaseControl,
    withInstanceId = _window$wp.compose.withInstanceId;
var googleFonts = {
  'Abril Fatface': {
    weight: ['400']
  },
  Anton: {
    weight: ['400']
  },
  Arvo: {
    weight: ['400', '700']
  },
  Asap: {
    weight: ['400', '500', '600', '700']
  },
  'Barlow Condensed': {
    weight: ['100', '200', '300', '400', '500', '600', '700', '800', '900']
  },
  Barlow: {
    weight: ['100', '200', '300', '400', '500', '600', '700', '800', '900']
  },
  'Cormorant Garamond': {
    weight: ['300', '400', '500', '600', '700']
  },
  Faustina: {
    weight: ['400', '500', '600', '700']
  },
  'Fira Sans': {
    weight: ['100', '200', '300', '400', '500', '600', '700', '800', '900']
  },
  'IBM Plex Sans': {
    weight: ['100', '200', '300', '400', '500', '600', '700']
  },
  Inconsolata: {
    weight: ['400', '700']
  },
  Heebo: {
    weight: ['100', '300', '400', '500', '700', '800', '900']
  },
  Karla: {
    weight: ['400', '700']
  },
  Lato: {
    weight: ['100', '200', '300', '400', '500', '600', '700', '800', '900']
  },
  Lora: {
    weight: ['400', '700']
  },
  Merriweather: {
    weight: ['300', '400', '500', '600', '700', '800', '900']
  },
  Montserrat: {
    weight: ['100', '200', '300', '400', '500', '600', '700', '800', '900']
  },
  'Noto Sans': {
    weight: ['400', '700']
  },
  'Noto Serif': {
    weight: ['400', '700']
  },
  'Open Sans': {
    weight: ['300', '400', '500', '600', '700', '800']
  },
  Oswald: {
    weight: ['200', '300', '400', '500', '600', '700']
  },
  'Playfair Display': {
    weight: ['400', '700', '900']
  },
  'PT Serif': {
    weight: ['400', '700']
  },
  Roboto: {
    weight: ['100', '300', '400', '500', '700', '900']
  },
  Rubik: {
    weight: ['300', '400', '500', '700', '900']
  },
  Tajawal: {
    weight: ['200', '300', '400', '500', '700', '800', '900']
  },
  Ubuntu: {
    weight: ['300', '400', '500', '700']
  },
  Yrsa: {
    weight: ['300', '400', '500', '600', '700']
  },
  'Source Serif Pro': {
    weight: ['200', '300', '400', '600', '700', '900']
  },
  'Source Sans Pro': {
    weight: ['200', '300', '400', '600', '700', '900']
  },
  Martel: {
    weight: ['200', '300', '400', '600', '700', '800', '900']
  }
};

var _default = withInstanceId(function (_ref) {
  var label = _ref.label,
      value = _ref.value,
      help = _ref.help,
      instanceId = _ref.instanceId,
      onChange = _ref.onChange,
      className = _ref.className,
      props = _objectWithoutProperties(_ref, ["label", "value", "help", "instanceId", "onChange", "className"]);

  var id = "inspector-coblocks-font-family-".concat(instanceId);
  var systemFonts = [{
    value: 'Arial',
    label: 'Arial'
  }, {
    value: '',
    label: 'Helvetica'
  }, {
    value: 'Times New Roman',
    label: 'Times New Roman'
  }, {
    value: 'Georgia',
    label: 'Georgia'
  }];
  var fonts = [];

  function sortThings(a, b) {
    return a > b ? 1 : b > a ? -1 : 0;
  } // Add Google Fonts


  Object.keys(googleFonts).sort(sortThings).map(function (k) {
    fonts.push({
      value: k,
      label: k
    });
  });
  var customFonts = [];

  if (document.fonts && document.fonts.forEach) {
    document.fonts.forEach(function (font) {
      if (googleFonts[font.family]) {
        return;
      }

      if (font.family === 'dashicons') {
        return;
      }

      if (customFonts.find(function (_ref2) {
        var value = _ref2.value;
        return value === font.family;
      })) {
        return;
      }

      customFonts.push({
        value: font.family,
        label: font.family
      });
    });
  }

  var onChangeValue = function onChangeValue(_ref3) {
    var value = _ref3.target.value;
    var googleFontsAttr = ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
    var isSystemFont = systemFonts.filter(function (font) {
      return font.label === value;
    }).length > 0;
    var url = '';

    if (!isSystemFont) {
      url = 'https://fonts.googleapis.com/css?family=' + value.replace(/ /g, '+') + googleFontsAttr;
    }

    onChange(value, url);
  };

  return e(BaseControl, {
    label: label,
    id: id,
    help: help,
    className: className
  }, e('select', _objectSpread({
    className: 'components-select-control__input components-select-control__input--coblocks-fontfamily',
    onChange: onChangeValue,
    'aria-describedby': help ? "".concat(id, "__help") : undefined
  }, props), customFonts.length > 0 && e('optgroup', {
    label: 'Custom Loaded Fonts'
  }, customFonts.map(function (option, index) {
    return e('option', {
      key: option.value,
      value: option.value,
      selected: value === option.value
    }, option.label);
  })), e('optgroup', {
    label: 'System Fonts'
  }, systemFonts.map(function (option, index) {
    return e('option', {
      key: option.value,
      value: option.value,
      selected: value === option.value
    }, option.label);
  })), e('optgroup', {
    label: 'Google Fonts'
  }, fonts.map(function (option, index) {
    return e('option', {
      key: option.value,
      value: option.value,
      selected: value === option.value
    }, option.label);
  }))));
});

exports.default = _default;
},{}],"aPnW":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = FontWeightPicker;

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

var _window$wp = window.wp,
    __ = _window$wp.i18n.__,
    e = _window$wp.element.createElement,
    SelectControl = _window$wp.components.SelectControl;

function FontWeightPicker(_ref) {
  var fontFamily = _ref.fontFamily,
      props = _objectWithoutProperties(_ref, ["fontFamily"]);

  var options = [{
    value: '100',
    label: __('Thin', 'slide')
  }, {
    value: '200',
    label: __('Extra Light', 'slide')
  }, {
    value: '300',
    label: __('Light', 'slide')
  }, {
    value: '400',
    label: __('Normal', 'slide')
  }, {
    value: '500',
    label: __('Medium', 'slide')
  }, {
    value: '600',
    label: __('Semi Bold', 'slide')
  }, {
    value: '700',
    label: __('Bold', 'slide')
  }, {
    value: '800',
    label: __('Extra Bold', 'slide')
  }, {
    value: '900',
    label: __('Black', 'slide')
  }];
  var weights = new Set();

  if (document.fonts && document.fonts.forEach) {
    document.fonts.forEach(function (font) {
      if (font.family !== fontFamily) {
        return;
      }

      weights.add(font.weight);
    });
  }

  if (weights.size) {
    options.forEach(function (option) {
      if (weights.has(option.value)) {
        return;
      }

      option.disabled = true;
    });
  }

  return e(SelectControl, _objectSpread({}, props, {
    options: options
  }));
}
},{}],"HZbO":[function(require,module,exports) {
"use strict";

var _fontPicker = _interopRequireDefault(require("./font-picker"));

var _fontWeightPicker = _interopRequireDefault(require("./font-weight-picker"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var _window$wp = window.wp,
    addFilter = _window$wp.hooks.addFilter,
    _window$wp$element = _window$wp.element,
    e = _window$wp$element.createElement,
    f = _window$wp$element.Fragment,
    InspectorControls = _window$wp.blockEditor.InspectorControls,
    PanelBody = _window$wp.components.PanelBody,
    __ = _window$wp.i18n.__;
var allowedBlocks = new Set(['core/paragraph']);
addFilter('blocks.registerBlockType', 'slide/register-block-attributes', function (settings) {
  if (!allowedBlocks.has(settings.name)) {
    return settings;
  }

  return _objectSpread({}, settings, {
    attributes: _objectSpread({}, settings.attributes, {
      fontFamily: {
        type: 'string'
      },
      fontWeight: {
        type: 'string'
      }
    })
  });
});
addFilter('editor.BlockEdit', 'slide/control-block-attributes', function (BlockEdit) {
  return function (props) {
    var attributes = props.attributes,
        setAttributes = props.setAttributes,
        isSelected = props.isSelected,
        name = props.name;
    return e(f, null, e(BlockEdit, props), isSelected && allowedBlocks.has(name) && e(InspectorControls, null, e(PanelBody, {
      title: __('Font', 'slide'),
      icon: 'format-text',
      initialOpen: false
    }, e(_fontPicker.default, {
      label: __('Font Family', 'slide'),
      value: attributes.fontFamily,
      onChange: function onChange(fontFamily) {
        return setAttributes({
          fontFamily: fontFamily
        });
      }
    }), e(_fontWeightPicker.default, {
      label: __('Font Weight', 'slide'),
      value: attributes.fontWeight || '400',
      onChange: function onChange(fontWeight) {
        return setAttributes({
          fontWeight: fontWeight
        });
      },
      fontFamily: attributes.fontFamily
    }))));
  };
});
addFilter('editor.BlockListBlock', 'slide/edit-block-attributes', function (BlockListBlock) {
  return function (props) {
    if (allowedBlocks.has(props.block.name)) {
      var _props = props,
          _props$wrapperProps = _props.wrapperProps,
          wrapperProps = _props$wrapperProps === void 0 ? {} : _props$wrapperProps,
          attributes = _props.attributes;
      var _wrapperProps$style = wrapperProps.style,
          style = _wrapperProps$style === void 0 ? {} : _wrapperProps$style;
      var fontFamily = attributes.fontFamily,
          fontWeight = attributes.fontWeight;

      if (fontFamily) {
        props = _objectSpread({}, props, {
          wrapperProps: _objectSpread({}, wrapperProps, {
            style: _objectSpread({}, style, {
              fontFamily: fontFamily,
              fontWeight: fontWeight
            })
          })
        });
      }
    }

    return e(BlockListBlock, props);
  };
});
addFilter('blocks.getSaveContent.extraProps', 'slide/save-block-attributes', function (extraProps, blockType, attributes) {
  if (!allowedBlocks.has(blockType.name)) {
    return extraProps;
  }

  var fontFamily = attributes.fontFamily,
      fontWeight = attributes.fontWeight;
  var _extraProps$style = extraProps.style,
      style = _extraProps$style === void 0 ? {} : _extraProps$style;
  return _objectSpread({}, extraProps, {
    style: _objectSpread({}, style, {
      fontFamily: fontFamily,
      fontWeight: fontWeight
    })
  });
});
},{"./font-picker":"eotu","./font-weight-picker":"aPnW"}],"Xy8f":[function(require,module,exports) {
window.addEventListener('DOMContentLoaded', resize);

function resize() {
  var element = document.querySelector('.block-editor-writing-flow');

  if (!element) {
    window.requestAnimationFrame(resize);
    return;
  }

  var width = element.clientWidth;
  var parentWidth = element.parentNode.clientWidth;
  var margin = parentWidth / 26;
  var innerParentWidth = element.parentNode.clientWidth - margin * 2;
  var scale = Math.min(1, innerParentWidth / width);
  var marginLeft = scale === 1 ? (innerParentWidth - width) / 2 + margin : margin;
  var transform = "translate(".concat(marginLeft, "px, ").concat(margin, "px) scale(").concat(scale, ")");

  if (element.style.transform !== transform) {
    element.style.transformOrigin = '0 0';
    element.style.transform = transform;
  }

  window.requestAnimationFrame(resize);
}
},{}],"XUD5":[function(require,module,exports) {
var _window$wp = window.wp,
    createBlock = _window$wp.blocks.createBlock,
    _window$wp$data = _window$wp.data,
    subscribe = _window$wp$data.subscribe,
    select = _window$wp$data.select,
    dispatch = _window$wp$data.dispatch;
subscribe(function () {
  var blocks = select('core/block-editor').getBlocks();
  var block = blocks.find(function (_ref) {
    var name = _ref.name;
    return name !== 'slide/slide';
  });

  if (!block) {
    return;
  }

  var slide = createBlock('slide/slide', {}, [block.name === 'core/paragraph' ? createBlock('core/heading') : createBlock(block.name, block.attributes)]);
  dispatch('core/block-editor').replaceBlock(block.clientId, slide);
});
},{}],"S1sO":[function(require,module,exports) {
var _window$wp = window.wp,
    __ = _window$wp.i18n.__,
    e = _window$wp.element.createElement,
    _window$wp$richText = _window$wp.richText,
    registerFormatType = _window$wp$richText.registerFormatType,
    toggleFormat = _window$wp$richText.toggleFormat,
    RichTextToolbarButton = _window$wp.blockEditor.RichTextToolbarButton;
registerFormatType('slide/fragment', {
  title: __('Slide Fragment', 'slide'),
  tagName: 'span',
  className: 'fragment',
  edit: function edit(_ref) {
    var value = _ref.value,
        onChange = _ref.onChange;
    return e(RichTextToolbarButton, {
      icon: 'editor-textcolor',
      title: __('Slide Fragment', 'slide'),
      onClick: function onClick() {
        onChange(toggleFormat(value, {
          type: 'slide/fragment'
        }));
      }
    });
  }
});
},{}],"wMKM":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

var _window$wp = window.wp,
    _window$wp$element = _window$wp.element,
    e = _window$wp$element.createElement,
    useRef = _window$wp$element.useRef,
    useEffect = _window$wp$element.useEffect,
    memo = _window$wp$element.memo,
    _window$wp$codeEditor = _window$wp.codeEditor,
    initialize = _window$wp$codeEditor.initialize,
    defaultSettings = _window$wp$codeEditor.defaultSettings;

var _default = memo(function (_ref) {
  var onChange = _ref.onChange,
      mode = _ref.mode,
      props = _objectWithoutProperties(_ref, ["onChange", "mode"]);

  var ref = useRef();
  useEffect(function () {
    var editor = initialize(ref.current, _objectSpread({}, defaultSettings, {
      codemirror: _objectSpread({}, defaultSettings.codemirror, {
        tabSize: 2,
        mode: mode,
        lineNumbers: false
      })
    }));
    editor.codemirror.on('change', function () {
      onChange(editor.codemirror.getValue());
    });
    return function () {
      editor.codemirror.toTextArea();
    };
  });
  return e('textarea', _objectSpread({
    ref: ref
  }, props)); // Never rerender.
}, function () {
  return true;
});

exports.default = _default;
},{}],"W9ez":[function(require,module,exports) {
"use strict";

var _codeEditor = _interopRequireDefault(require("./code-editor"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var _window$wp = window.wp,
    __ = _window$wp.i18n.__,
    registerBlockType = _window$wp.blocks.registerBlockType,
    _window$wp$element = _window$wp.element,
    e = _window$wp$element.createElement,
    Fragment = _window$wp$element.Fragment,
    useSelect = _window$wp.data.useSelect,
    _window$wp$components = _window$wp.components,
    TextareaControl = _window$wp$components.TextareaControl,
    PanelBody = _window$wp$components.PanelBody,
    RangeControl = _window$wp$components.RangeControl,
    ToggleControl = _window$wp$components.ToggleControl,
    Button = _window$wp$components.Button,
    FocalPointPicker = _window$wp$components.FocalPointPicker,
    Notice = _window$wp$components.Notice,
    TextControl = _window$wp$components.TextControl,
    RadioControl = _window$wp$components.RadioControl,
    _window$wp$blockEdito = _window$wp.blockEditor,
    MediaUpload = _window$wp$blockEdito.MediaUpload,
    InnerBlocks = _window$wp$blockEdito.InnerBlocks,
    InspectorControls = _window$wp$blockEdito.InspectorControls,
    ColorPalette = _window$wp$blockEdito.ColorPalette;
var ALLOWED_MEDIA_TYPES = ['image'];
var backgroundUrlKey = 'presentation-background-url';
registerBlockType('slide/slide', {
  title: __('Slide', 'slide'),
  description: __('With this blocks you can form your slide deck! You can override document level setting for each slide block.'),
  icon: 'slides',
  category: 'common',
  keywords: [__('Presentation', 'slide')],
  attributes: {
    notes: {
      type: 'string'
    },
    color: {
      type: 'string'
    },
    backgroundColor: {
      type: 'string'
    },
    backgroundId: {
      type: 'string'
    },
    backgroundUrl: {
      type: 'string'
    },
    focalPoint: {
      type: 'object'
    },
    backgroundOpacity: {
      type: 'string'
    },
    backgroundSize: {
      type: 'string'
    },
    hidden: {
      type: 'boolean'
    },
    backgroundIframeUrl: {
      type: 'string'
    },
    backgroundSvg: {
      type: 'string'
    }
  },
  edit: function edit(_ref) {
    var attributes = _ref.attributes,
        setAttributes = _ref.setAttributes,
        className = _ref.className;
    var meta = useSelect(function (select) {
      return select('core/editor').getEditedPostAttribute('meta');
    });
    return e(Fragment, null, e(InspectorControls, null, e(PanelBody, {
      title: __('Speaker Notes', 'slide'),
      icon: 'edit',
      initialOpen: false
    }, e(TextareaControl, {
      label: __('Anything you want to remember.', 'slide'),
      value: attributes.notes,
      onChange: function onChange(notes) {
        return setAttributes({
          notes: notes
        });
      },
      rows: 10
    })), e(PanelBody, {
      title: __('Font', 'slide'),
      icon: 'text',
      initialOpen: false
    }, e(ColorPalette, {
      label: __('Color', 'slide'),
      value: attributes.color,
      onChange: function onChange(color) {
        return setAttributes({
          color: color
        });
      }
    }), !!attributes.color && e(Button, {
      isDefault: true,
      onClick: function onClick() {
        setAttributes({
          color: undefined
        });
      }
    }, __('Remove'))), e(PanelBody, {
      title: __('Background Color', 'slide'),
      icon: 'art',
      initialOpen: false
    }, e(ColorPalette, {
      label: __('Background Color', 'slide'),
      value: attributes.backgroundColor,
      onChange: function onChange(backgroundColor) {
        return setAttributes({
          backgroundColor: backgroundColor
        });
      }
    }), (attributes.backgroundUrl || meta[backgroundUrlKey]) && e(RangeControl, {
      label: __('Opacity', 'slide'),
      value: attributes.backgroundOpacity ? 100 - parseInt(attributes.backgroundOpacity, 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 0,
      onChange: function onChange(value) {
        if (value === undefined) {
          setAttributes({
            backgroundOpacity: undefined
          });
        } else {
          setAttributes({
            backgroundOpacity: 100 - value + ''
          });
        }
      }
    }), !!attributes.backgroundColor && e(Button, {
      isDefault: true,
      onClick: function onClick() {
        setAttributes({
          backgroundColor: undefined
        });
      }
    }, __('Remove'))), e(PanelBody, {
      title: __('Background Image', 'slide'),
      icon: 'format-image',
      initialOpen: false
    }, e(MediaUpload, {
      onSelect: function onSelect(media) {
        if (!media || !media.url) {
          setAttributes({
            backgroundUrl: undefined,
            backgroundId: undefined,
            backgroundSize: undefined,
            focalPoint: undefined
          });
          return;
        }

        setAttributes({
          backgroundUrl: media.url,
          backgroundId: media.id
        });
      },
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: attributes.backgroundId,
      render: function render(_ref2) {
        var open = _ref2.open;
        return e(Button, {
          isDefault: true,
          onClick: open
        }, attributes.backgroundUrl ? __('Change') : __('Add Background Image'));
      }
    }), ' ', !!attributes.backgroundUrl && e(Button, {
      isDefault: true,
      onClick: function onClick() {
        setAttributes({
          backgroundUrl: undefined,
          backgroundId: undefined,
          backgroundSize: undefined,
          focalPoint: undefined
        });
      }
    }, __('Remove')), e('br'), e('br'), !!attributes.backgroundUrl && e(FocalPointPicker, {
      label: __('Focal Point Picker'),
      url: attributes.backgroundUrl,
      value: attributes.focalPoint,
      onChange: function onChange(focalPoint) {
        return setAttributes({
          focalPoint: focalPoint
        });
      }
    }), !!attributes.backgroundUrl && e(RangeControl, {
      label: __('Opacity', 'slide'),
      value: attributes.backgroundOpacity ? parseInt(attributes.backgroundOpacity, 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 100,
      onChange: function onChange(value) {
        return setAttributes({
          backgroundOpacity: value + ''
        });
      }
    }), !!attributes.backgroundUrl && e(RadioControl, {
      label: __('Size', 'slide'),
      selected: attributes.backgroundSize,
      options: [{
        label: __('Cover'),
        value: 'cover'
      }, {
        label: __('Contain'),
        value: 'contain'
      }],
      onChange: function onChange(backgroundSize) {
        return setAttributes({
          backgroundSize: backgroundSize
        });
      }
    })), e(PanelBody, {
      title: __('Background Iframe', 'slide'),
      icon: 'format-video',
      initialOpen: false
    }, e(TextControl, {
      label: __('Iframe URL'),
      value: attributes.backgroundIframeUrl,
      onChange: function onChange(backgroundIframeUrl) {
        return setAttributes({
          backgroundIframeUrl: backgroundIframeUrl
        });
      }
    }), e('br'), e('br'), !!attributes.backgroundIframeUrl && e(RangeControl, {
      label: __('Opacity', 'slide'),
      value: attributes.backgroundOpacity ? parseInt(attributes.backgroundOpacity, 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 100,
      onChange: function onChange(value) {
        return setAttributes({
          backgroundOpacity: value + ''
        });
      }
    })), e(PanelBody, {
      title: __('Background SVG', 'slide'),
      icon: 'format-video',
      initialOpen: false
    }, e(_codeEditor.default, {
      mode: 'htmlmixed',
      value: attributes.backgroundSvg,
      onChange: function onChange(backgroundSvg) {
        return setAttributes({
          backgroundSvg: backgroundSvg
        });
      }
    }), e('br'), e('br'), !!attributes.backgroundSvg && e(RangeControl, {
      label: __('Opacity', 'slide'),
      value: attributes.backgroundOpacity ? parseInt(attributes.backgroundOpacity, 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 100,
      onChange: function onChange(value) {
        return setAttributes({
          backgroundOpacity: value + ''
        });
      }
    })), e(PanelBody, {
      title: __('Visibility', 'slide'),
      icon: 'visibility',
      initialOpen: false
    }, e(ToggleControl, {
      label: __('Hide Slide', 'slide'),
      checked: attributes.hidden,
      onChange: function onChange(hidden) {
        return setAttributes({
          hidden: hidden
        });
      }
    }))), attributes.hidden && e(Notice, {
      status: 'warning',
      isDismissible: false
    }, 'This slide is hidden'), e('div', {
      className: 'wp-block-slide-slide__body',
      style: {
        color: attributes.color || undefined,
        backgroundColor: attributes.backgroundColor || undefined,
        // If a background color is set, disable the global gradient.
        backgroundImage: attributes.backgroundColor ? 'none' : undefined
      }
    }, e('div', {
      className: 'wp-block-slide-slide__background',
      style: {
        backgroundImage: attributes.backgroundUrl ? "url(\"".concat(attributes.backgroundUrl, "\")") : undefined,
        backgroundPosition: attributes.focalPoint ? "".concat(attributes.focalPoint.x * 100, "% ").concat(attributes.focalPoint.y * 100, "%") : undefined,
        backgroundSize: attributes.backgroundSize ? attributes.backgroundSize : undefined,
        opacity: attributes.backgroundOpacity ? attributes.backgroundOpacity / 100 : undefined
      }
    }, !!attributes.backgroundIframeUrl && e('iframe', {
      src: attributes.backgroundIframeUrl
    }), !!attributes.backgroundSvg && e('div', {
      dangerouslySetInnerHTML: {
        __html: attributes.backgroundSvg
      }
    })), e('section', {
      className: className
    }, e(InnerBlocks))), e(TextareaControl, {
      label: __('Speaker notes', 'slide'),
      value: attributes.notes,
      onChange: function onChange(notes) {
        return setAttributes({
          notes: notes
        });
      },
      rows: 5
    }));
  },
  save: function save(_ref3) {
    var attributes = _ref3.attributes;
    return e(attributes.hidden ? 'div' : 'section', {
      style: {
        color: attributes.color || undefined,
        display: attributes.hidden ? 'none' : undefined
      },
      'data-background-color': attributes.backgroundColor || undefined,
      'data-background-image': attributes.backgroundUrl ? attributes.backgroundUrl : undefined,
      'data-background-position': attributes.focalPoint ? "".concat(attributes.focalPoint.x * 100, "% ").concat(attributes.focalPoint.y * 100, "%") : undefined,
      'data-background-opacity': attributes.backgroundOpacity ? attributes.backgroundOpacity / 100 : undefined,
      'data-background-iframe': attributes.backgroundIframeUrl ? attributes.backgroundIframeUrl : undefined,
      'data-background-size': attributes.backgroundSize ? attributes.backgroundSize : undefined,
      'data-background-svg': attributes.backgroundSvg ? attributes.backgroundSvg : undefined
    }, e(InnerBlocks.Content));
  }
});
},{"./code-editor":"wMKM"}],"GOVZ":[function(require,module,exports) {
"use strict";

var _fontPicker = _interopRequireDefault(require("./font-picker"));

var _fontWeightPicker = _interopRequireDefault(require("./font-weight-picker"));

var _codeEditor = _interopRequireDefault(require("./code-editor"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var _window$wp = window.wp,
    __ = _window$wp.i18n.__,
    _window$wp$element = _window$wp.element,
    e = _window$wp$element.createElement,
    useEffect = _window$wp$element.useEffect,
    registerPlugin = _window$wp.plugins.registerPlugin,
    PluginDocumentSettingPanel = _window$wp.editPost.PluginDocumentSettingPanel,
    _window$wp$data = _window$wp.data,
    useSelect = _window$wp$data.useSelect,
    useDispatch = _window$wp$data.useDispatch,
    _window$wp$components = _window$wp.components,
    TextareaControl = _window$wp$components.TextareaControl,
    RangeControl = _window$wp$components.RangeControl,
    SelectControl = _window$wp$components.SelectControl,
    ToggleControl = _window$wp$components.ToggleControl,
    Button = _window$wp$components.Button,
    FocalPointPicker = _window$wp$components.FocalPointPicker,
    ExternalLink = _window$wp$components.ExternalLink,
    TextControl = _window$wp$components.TextControl,
    RadioControl = _window$wp$components.RadioControl,
    CheckboxControl = _window$wp$components.CheckboxControl,
    _window$wp$blockEdito = _window$wp.blockEditor,
    MediaUpload = _window$wp$blockEdito.MediaUpload,
    __experimentalGradientPickerControl = _window$wp$blockEdito.__experimentalGradientPickerControl,
    ColorPalette = _window$wp$blockEdito.ColorPalette,
    addQueryArgs = _window$wp.url.addQueryArgs;
var colorKey = 'presentation-color';
var bgColorKey = 'presentation-background-color';
var backgroundGradientKey = 'presentation-background-gradient';
var backgroundUrlKey = 'presentation-background-url';
var backgroundIdKey = 'presentation-background-id';
var backgroundPositionKey = 'presentation-background-position';
var backgroundOpacityKey = 'presentation-background-opacity';
var cssKey = 'presentation-css';
var fontSizeKey = 'presentation-font-size';
var fontFamilyKey = 'presentation-font-family';
var fontFamilyUrlKey = 'presentation-font-family-url';
var fontFamilyHeadingKey = 'presentation-font-family-heading';
var fontFamilyHeadingUrlKey = 'presentation-font-family-heading-url';
var fontWeightHeadingKey = 'presentation-font-weight-heading';
var transitionKey = 'presentation-transition';
var backgroundTransitionKey = 'presentation-background-transition';
var transitionSpeedKey = 'presentation-transition-speed';
var controlsKey = 'presentation-controls';
var progressKey = 'presentation-progress';
var widthKey = 'presentation-width';
var horizontalPaddingKey = 'presentation-horizontal-padding';
var verticalPaddingKey = 'presentation-vertical-padding';
var colorPaletteKey = 'presentation-color-palette';
var containKey = 'presentation-contain';
var ALLOWED_MEDIA_TYPES = ['image'];
registerPlugin('slide', {
  render: function render() {
    var meta = useSelect(function (select) {
      return select('core/editor').getEditedPostAttribute('meta');
    });
    var link = useSelect(function (select) {
      return select('core/editor').getCurrentPost('meta').link;
    });

    var _useDispatch = useDispatch('core/editor'),
        editPost = _useDispatch.editPost;

    var updateMeta = function updateMeta(value, key) {
      return editPost({
        meta: _objectSpread({}, meta, _defineProperty({}, key, value))
      });
    };

    var bodyRules = {
      'background-color': meta[bgColorKey] || '#fff',
      'background-image': meta[backgroundGradientKey] || 'none',
      color: meta[colorKey] || '#000',
      'font-size': (meta[fontSizeKey] || '42') + 'px',
      'font-family': meta[fontFamilyKey] || 'Helvetica, sans-serif'
    };
    var rules = {
      width: meta[widthKey] ? meta[widthKey] + 'px !important' : undefined,
      'padding-top': meta[verticalPaddingKey] ? meta[verticalPaddingKey] : '0.2em',
      'padding-bottom': meta[verticalPaddingKey] ? meta[verticalPaddingKey] : '0.2em',
      'padding-left': meta[horizontalPaddingKey] ? meta[horizontalPaddingKey] : '0.2em',
      'padding-right': meta[horizontalPaddingKey] ? meta[horizontalPaddingKey] : '0.2em'
    };
    var width = parseInt(meta[widthKey] || '960', 10) + 30;

    if (meta[containKey] === 'true') {
      rules.width = 'auto !important';
      rules.height = 'auto !important';
      bodyRules.width = meta[widthKey] ? meta[widthKey] + 'px !important' : '960px !important';
      bodyRules.height = '720px !important';
    } else {
      width += 100;
    }

    useEffect(function () {
      if (meta[containKey] === 'true') {
        document.documentElement.classList.add('presentation-contain');
      } else {
        document.documentElement.classList.remove('presentation-contain');
      }
    });
    var backgroundRules = {
      'background-image': meta[backgroundUrlKey] ? "url(\"".concat(meta[backgroundUrlKey], "\")") : 'none',
      'background-size': 'cover',
      'background-position': meta[backgroundPositionKey] ? meta[backgroundPositionKey] : '50% 50%',
      opacity: meta[backgroundOpacityKey] ? meta[backgroundOpacityKey] / 100 : 1
    };
    return [].concat(_toConsumableArray(Object.keys(bodyRules).map(function (key) {
      return e('style', null, ".wp-block-slide-slide__body {".concat(key, ":").concat(bodyRules[key], "}"));
    })), _toConsumableArray(Object.keys(rules).map(function (key) {
      return e('style', null, ".wp-block-slide-slide {".concat(key, ":").concat(rules[key], "}"));
    })), _toConsumableArray(Object.keys(backgroundRules).map(function (key) {
      return e('style', null, ".wp-block-slide-slide__background {".concat(key, ":").concat(backgroundRules[key], "}"));
    })), [e('style', null, meta[cssKey]), !!meta[fontFamilyUrlKey] && e('style', null, "@import url(\"".concat(meta[fontFamilyUrlKey], "\")")), !!meta[fontFamilyHeadingKey] && e('style', null, (meta[fontFamilyHeadingUrlKey] ? "@import url(\"".concat(meta[fontFamilyHeadingUrlKey], "\");") : '') + ".wp-block-slide-slide h1, .wp-block-slide-slide h2, .wp-block-slide-slide h3, .wp-block-slide-slide h4, .wp-block-slide-slide h5, .wp-block-slide-slide h6 { font-family: ".concat(meta[fontFamilyHeadingKey], " }")), !!meta[fontWeightHeadingKey] && e('style', null, ".wp-block-slide-slide h1, .wp-block-slide-slide h2, .wp-block-slide-slide h3, .wp-block-slide-slide h4, .wp-block-slide-slide h5, .wp-block-slide-slide h6 { font-weight: ".concat(meta[fontWeightHeadingKey], " }")), e('style', null, ".editor-styles-wrapper .editor-writing-flow { width: ".concat(width, "px !important; }")), e(PluginDocumentSettingPanel, {
      name: 'slide-dimensions',
      title: __('Setup', 'slide'),
      icon: 'editor-expand'
    }, e(RadioControl, {
      selected: meta[widthKey] === '1280' ? '16:9' : '',
      options: [{
        label: __('Standard 4:3'),
        value: ''
      }, {
        label: __('Widescreen 16:9'),
        value: '16:9'
      }],
      onChange: function onChange(value) {
        editPost({
          meta: _defineProperty({}, widthKey, value === '16:9' ? '1280' : '')
        });
      }
    }), e(CheckboxControl, {
      label: __('Contain view to dimensions', 'slide'),
      help: __('This can be useful if positions from background and full width blocks must be preserved.', 'slide'),
      checked: meta[containKey] === 'true',
      onChange: function onChange(value) {
        editPost({
          meta: _defineProperty({}, containKey, value + '')
        });
      }
    }), e(TextControl, {
      label: __('Horizontal Padding'),
      placeholder: '0.2em',
      value: meta[horizontalPaddingKey],
      onChange: function onChange(value) {
        return updateMeta(value, horizontalPaddingKey);
      }
    }), e(TextControl, {
      label: __('Vertical Padding'),
      placeholder: '0.2em',
      value: meta[verticalPaddingKey],
      onChange: function onChange(value) {
        return updateMeta(value, verticalPaddingKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-font',
      title: __('Base Font', 'slide'),
      icon: 'text'
    }, e(RangeControl, {
      label: __('Font Size', 'slide'),
      value: meta[fontSizeKey] ? parseInt(meta[fontSizeKey], 10) : undefined,
      min: 10,
      max: 100,
      initialPosition: 42,
      onChange: function onChange(value) {
        return updateMeta(value + '', fontSizeKey);
      }
    }), e(_fontPicker.default, {
      label: __('Font Family', 'slide'),
      value: meta[fontFamilyKey],
      onChange: function onChange(value, fontUrl) {
        var _meta3;

        editPost({
          meta: (_meta3 = {}, _defineProperty(_meta3, fontFamilyKey, value), _defineProperty(_meta3, fontFamilyUrlKey, fontUrl), _meta3)
        });
      }
    }), e(ColorPalette, {
      label: __('Color', 'slide'),
      value: meta[colorKey],
      onChange: function onChange(value) {
        return updateMeta(value, colorKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-heading-font',
      title: __('Heading Font', 'slide'),
      icon: 'text'
    }, e(_fontPicker.default, {
      label: __('Font Family', 'slide'),
      value: meta[fontFamilyHeadingKey],
      onChange: function onChange(value, fontUrl) {
        var _meta4;

        editPost({
          meta: (_meta4 = {}, _defineProperty(_meta4, fontFamilyHeadingKey, value), _defineProperty(_meta4, fontFamilyHeadingUrlKey, fontUrl), _meta4)
        });
      }
    }), e(_fontWeightPicker.default, {
      label: __('Font Weight', 'slide'),
      value: meta[fontWeightHeadingKey] || '400',
      onChange: function onChange(value) {
        return updateMeta(value, fontWeightHeadingKey);
      },
      fontFamily: meta[fontFamilyHeadingKey]
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-background',
      title: __('Background', 'slide'),
      icon: 'art'
    }, e(ColorPalette, {
      label: __('Background Color', 'slide'),
      value: meta[bgColorKey],
      onChange: function onChange(value) {
        var _objectSpread3;

        editPost({
          meta: _objectSpread({}, meta, (_objectSpread3 = {}, _defineProperty(_objectSpread3, bgColorKey, value), _defineProperty(_objectSpread3, backgroundGradientKey, ''), _objectSpread3))
        });
      }
    }), __('Experimental:'), __experimentalGradientPickerControl && e(__experimentalGradientPickerControl, {
      onChange: function onChange(value) {
        return updateMeta(value, backgroundGradientKey);
      },
      value: meta[backgroundGradientKey]
    }), !!meta[backgroundUrlKey] && e(RangeControl, {
      label: __('Opacity', 'slide'),
      help: __('May be overridden by the block!'),
      value: meta[backgroundOpacityKey] ? 100 - parseInt(meta[backgroundOpacityKey], 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 0,
      onChange: function onChange(value) {
        editPost({
          meta: _objectSpread({}, meta, _defineProperty({}, backgroundOpacityKey, 100 - value + ''))
        });
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-background-image',
      title: __('Background Image', 'slide'),
      icon: 'format-image'
    }, e(MediaUpload, {
      onSelect: function onSelect(media) {
        var _objectSpread6;

        if (!media || !media.url) {
          var _objectSpread5;

          editPost({
            meta: _objectSpread({}, meta, (_objectSpread5 = {}, _defineProperty(_objectSpread5, backgroundUrlKey, undefined), _defineProperty(_objectSpread5, backgroundIdKey, undefined), _defineProperty(_objectSpread5, backgroundPositionKey, undefined), _defineProperty(_objectSpread5, backgroundOpacityKey, undefined), _objectSpread5))
          });
          return;
        }

        editPost({
          meta: _objectSpread({}, meta, (_objectSpread6 = {}, _defineProperty(_objectSpread6, backgroundUrlKey, media.url), _defineProperty(_objectSpread6, backgroundIdKey, media.id + ''), _objectSpread6))
        });
      },
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: meta[backgroundIdKey] ? parseInt(meta[backgroundIdKey], 10) : undefined,
      render: function render(_ref) {
        var open = _ref.open;
        return e(Button, {
          isDefault: true,
          onClick: open
        }, meta[backgroundUrlKey] ? __('Change') : __('Add Background Image'));
      }
    }), ' ', !!meta[backgroundUrlKey] && e(Button, {
      isDefault: true,
      onClick: function onClick() {
        var _objectSpread7;

        editPost({
          meta: _objectSpread({}, meta, (_objectSpread7 = {}, _defineProperty(_objectSpread7, backgroundUrlKey, ''), _defineProperty(_objectSpread7, backgroundIdKey, ''), _defineProperty(_objectSpread7, backgroundPositionKey, ''), _defineProperty(_objectSpread7, backgroundOpacityKey, ''), _objectSpread7))
        });
      }
    }, __('Remove')), e('br'), e('br'), !!meta[backgroundUrlKey] && e(FocalPointPicker, {
      label: __('Focal Point Picker'),
      url: meta[backgroundUrlKey],
      value: function () {
        if (!meta[backgroundPositionKey]) {
          return;
        }

        var _meta$backgroundPosit = meta[backgroundPositionKey].split(' '),
            _meta$backgroundPosit2 = _slicedToArray(_meta$backgroundPosit, 2),
            x = _meta$backgroundPosit2[0],
            y = _meta$backgroundPosit2[1];

        x = parseFloat(x) / 100;
        y = parseFloat(y) / 100;
        return {
          x: x,
          y: y
        };
      }(),
      onChange: function onChange(focalPoint) {
        editPost({
          meta: _objectSpread({}, meta, _defineProperty({}, backgroundPositionKey, "".concat(focalPoint.x * 100, "% ").concat(focalPoint.y * 100, "%")))
        });
      }
    }), !!meta[backgroundUrlKey] && e(RangeControl, {
      label: __('Opacity', 'slide'),
      help: __('May be overridden by the block!'),
      value: meta[backgroundOpacityKey] ? parseInt(meta[backgroundOpacityKey], 10) : undefined,
      min: 0,
      max: 100,
      initialPosition: 100,
      onChange: function onChange(value) {
        editPost({
          meta: _objectSpread({}, meta, _defineProperty({}, backgroundOpacityKey, value + ''))
        });
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-palette',
      title: __('Color Palette', 'slide'),
      icon: 'art'
    }, e(TextareaControl, {
      label: __('Comma separated list of color values. Please refresh the page to be able to use the palette.', 'slide'),
      value: meta[colorPaletteKey],
      onChange: function onChange(value) {
        return updateMeta(value, colorPaletteKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-css',
      title: __('Custom CSS', 'slide'),
      icon: 'editor-code'
    }, e(_codeEditor.default, {
      value: meta[cssKey] || '/* Always a block prefix! */\n.wp-block-slide-slide {\n\t\n}\n',
      onChange: function onChange(value) {
        return updateMeta(value, cssKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-transition',
      title: __('Transition', 'slide'),
      icon: 'slides'
    }, e(SelectControl, {
      label: __('Transition Style', 'slide'),
      options: [{
        value: 'none',
        label: __('None', 'slide')
      }, {
        value: 'fade',
        label: __('Fade', 'slide')
      }, {
        value: 'slide',
        label: __('Slide', 'slide')
      }, {
        value: 'convex',
        label: __('Convex', 'slide')
      }, {
        value: 'concave',
        label: __('Concave', 'slide')
      }, {
        value: 'zoom',
        label: __('Zoom', 'slide')
      }],
      value: meta[transitionKey],
      onChange: function onChange(value) {
        return updateMeta(value, transitionKey);
      }
    }), e(SelectControl, {
      label: __('Background Transition Style', 'slide'),
      options: [{
        value: 'none',
        label: __('None', 'slide')
      }, {
        value: 'fade',
        label: __('Fade', 'slide')
      }, {
        value: 'slide',
        label: __('Slide', 'slide')
      }, {
        value: 'convex',
        label: __('Convex', 'slide')
      }, {
        value: 'concave',
        label: __('Concave', 'slide')
      }, {
        value: 'zoom',
        label: __('Zoom', 'slide')
      }],
      value: meta[backgroundTransitionKey],
      onChange: function onChange(value) {
        return updateMeta(value, backgroundTransitionKey);
      }
    }), e(SelectControl, {
      label: __('Transition Speed', 'slide'),
      options: [{
        value: 'default',
        label: __('Default', 'slide')
      }, {
        value: 'fast',
        label: __('Fast', 'slide')
      }, {
        value: 'slow',
        label: __('Slow', 'slide')
      }],
      value: meta[transitionSpeedKey],
      onChange: function onChange(value) {
        return updateMeta(value, transitionSpeedKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-controls',
      title: __('Controls', 'slide'),
      icon: 'leftright'
    }, e(ToggleControl, {
      label: __('Control Arrows', 'slide'),
      checked: meta[controlsKey] === 'true',
      onChange: function onChange(value) {
        return updateMeta(value + '', controlsKey);
      }
    }), e(ToggleControl, {
      label: __('Progress Bar', 'slide'),
      checked: meta[progressKey] === 'true',
      onChange: function onChange(value) {
        return updateMeta(value + '', progressKey);
      }
    })), e(PluginDocumentSettingPanel, {
      name: 'slide-pdf',
      title: __('PDF (Experimental)', 'slide'),
      icon: 'page'
    }, e('p', {}, e(ExternalLink, {
      href: addQueryArgs(link, {
        'print-pdf': true
      }),
      target: '_blank'
    }, __('Print (Save as PDF).', 'slide')), e('br'), __('Enable backgrounds and remove margins.', 'slide')))]);
  }
});
},{"./font-picker":"eotu","./font-weight-picker":"aPnW","./code-editor":"wMKM"}],"BZ3n":[function(require,module,exports) {
"use strict";

require("./block-attributes");

require("./resize");

require("./data-subscription");

require("./fragment");

require("./block");

require("./plugin");
},{"./block-attributes":"HZbO","./resize":"Xy8f","./data-subscription":"XUD5","./fragment":"S1sO","./block":"W9ez","./plugin":"GOVZ"}]},{},["BZ3n"], null)
//# sourceMappingURL=/index.js.map