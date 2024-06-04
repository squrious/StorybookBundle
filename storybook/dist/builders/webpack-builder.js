'use strict';

var httpProxyMiddleware = require('http-proxy-middleware');
var baseBuilder = require('@storybook/builder-webpack5');

function _interopNamespace(e) {
  if (e && e.__esModule) return e;
  var n = Object.create(null);
  if (e) {
    Object.keys(e).forEach(function (k) {
      if (k !== 'default') {
        var d = Object.getOwnPropertyDescriptor(e, k);
        Object.defineProperty(n, k, d.get ? d : {
          enumerable: true,
          get: function () { return e[k]; }
        });
      }
    });
  }
  n.default = e;
  return Object.freeze(n);
}

var baseBuilder__namespace = /*#__PURE__*/_interopNamespace(baseBuilder);

// src/builders/webpack-builder.ts

// node_modules/ts-dedent/esm/index.js
function dedent(templ) {
  var values = [];
  for (var _i = 1; _i < arguments.length; _i++) {
    values[_i - 1] = arguments[_i];
  }
  var strings = Array.from(typeof templ === "string" ? [templ] : templ);
  strings[strings.length - 1] = strings[strings.length - 1].replace(/\r?\n([\t ]*)$/, "");
  var indentLengths = strings.reduce(function(arr, str) {
    var matches = str.match(/\n([\t ]+|(?!\s).)/g);
    if (matches) {
      return arr.concat(matches.map(function(match) {
        var _a, _b;
        return (_b = (_a = match.match(/[\t ]/g)) === null || _a === void 0 ? void 0 : _a.length) !== null && _b !== void 0 ? _b : 0;
      }));
    }
    return arr;
  }, []);
  if (indentLengths.length) {
    var pattern_1 = new RegExp("\n[	 ]{" + Math.min.apply(Math, indentLengths) + "}", "g");
    strings = strings.map(function(str) {
      return str.replace(pattern_1, "\n");
    });
  }
  strings[0] = strings[0].replace(/^\r?\n/, "");
  var string = strings[0];
  values.forEach(function(value, i) {
    var endentations = string.match(/(?:^|\n)( *)$/);
    var endentation = endentations ? endentations[1] : "";
    var indentedValue = value;
    if (typeof value === "string" && value.includes("\n")) {
      indentedValue = String(value).split("\n").map(function(str, i2) {
        return i2 === 0 ? str : "" + endentation + str;
      }).join("\n");
    }
    string += indentedValue + strings[i + 1];
  });
  return string;
}
var esm_default = dedent;

// src/builders/webpack-builder.ts
var getConfig2 = baseBuilder__namespace.getConfig;
var bail2 = baseBuilder__namespace.bail;
var start2 = async (options) => {
  const isProd = options.options.configType === "PRODUCTION";
  const { symfony } = await options.options.presets.apply("frameworkOptions");
  if (!symfony.server) {
    throw new Error(esm_default`
        Cannot configure dev server.
        
        "server" option in "framework.options.symfony" is required for Storybook dev server to run.
        Update your main.ts|js file accordingly.
        `);
  }
  const proxyPaths = ["/_storybook/render"];
  if (symfony.proxyPaths) {
    const paths = !Array.isArray(symfony.proxyPaths) ? [symfony.proxyPaths] : symfony.proxyPaths;
    proxyPaths.push(...paths);
  }
  for (const path of proxyPaths) {
    options.router.use(
      path,
      httpProxyMiddleware.createProxyMiddleware({
        target: symfony.server,
        changeOrigin: true,
        secure: isProd,
        headers: {
          "X-Storybook-Proxy": "true"
        }
      })
    );
  }
  return baseBuilder__namespace.start(options);
};
var build2 = baseBuilder__namespace.build;
var corePresets2 = baseBuilder__namespace.corePresets;
var overridePresets2 = baseBuilder__namespace.overridePresets;

exports.bail = bail2;
exports.build = build2;
exports.corePresets = corePresets2;
exports.getConfig = getConfig2;
exports.overridePresets = overridePresets2;
exports.start = start2;
//# sourceMappingURL=out.js.map
//# sourceMappingURL=webpack-builder.js.map