import path from 'path';
import baseConfig from './webpack.config.js';

const __dirname = path.resolve();
const outPath = '/tmp';
const entry = {
  layout: path.resolve(__dirname, './Sources/Scss/layout.scss'),
};

export default (env, argv) => {
  return {
    ...baseConfig,
    entry: entry,
    mode: argv.mode,
    output: {
      path: outPath,
      filename: '[name].min.js'
    }
  };
};
