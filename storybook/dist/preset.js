'use strict';

var __require = /* @__PURE__ */ ((x) => typeof require !== "undefined" ? require : typeof Proxy !== "undefined" ? new Proxy(x, {
  get: (a, b) => (typeof require !== "undefined" ? require : a)[b]
}) : x)(function(x) {
  if (typeof require !== "undefined")
    return require.apply(this, arguments);
  throw Error('Dynamic require of "' + x + '" is not supported');
});

// src/preset.ts
var addons = [__require.resolve("./server/framework-preset")];
var core = async (config, options) => {
  const framework = await options.presets.apply("framework");
  return {
    ...config,
    builder: {
      name: __require.resolve("./builders/webpack-builder"),
      options: typeof framework === "string" ? {} : framework.options.builder || {}
    }
  };
};
var previewAnnotations = async (entry = [], options) => {
  const docsEnabled = Object.keys(await options.presets.apply("docs", {}, options)).length > 0;
  return entry.concat(__require.resolve("./entry-preview")).concat(docsEnabled ? [__require.resolve("./entry-preview-docs")] : []);
};

exports.addons = addons;
exports.core = core;
exports.previewAnnotations = previewAnnotations;
//# sourceMappingURL=out.js.map
//# sourceMappingURL=preset.js.map