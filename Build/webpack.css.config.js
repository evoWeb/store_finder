const path = require('path');
const baseConfig = require('./webpack.config');

const outPath = '/tmp';
const entry = {
  layout: path.resolve(__dirname, './Sources/Scss/layout.scss'),
};

module.exports = (env, argv) => {
  return {
    ...baseConfig,
    entry: entry,
    mode: argv.mode,
    output: {
      path: outPath,
      filename: '[name].pack.js'
    }
  };
};
