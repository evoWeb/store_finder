import path from 'path';
import baseConfig from './webpack.config.babel';

const outPath = path.resolve(__dirname, '../Resources/Public/JavaScript');
const entry = {
  layout: path.resolve(__dirname, './Sources/Scss/layout.scss'),
};

module.exports = {
  ...baseConfig,
  mode: 'production',
  entry: entry,
  output: {
    path: outPath,
    filename: '[name].pack.css'
  }
};
