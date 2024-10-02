import path from 'path';
import baseConfig from './webpack.config.js';

const __dirname = path.resolve();
const outPath = path.resolve(__dirname, '../Resources/Public/JavaScript');
const entry = {
  FrontendGoogleMap: path.resolve(__dirname, './Sources/TypeScript/FrontendGoogleMap.ts'),
  FrontendOsmMap: path.resolve(__dirname, './Sources/TypeScript/FrontendOsmMap.ts'),
};

export default (env, argv) => {
  return {
    ...baseConfig,
    entry: entry,
    mode: argv.mode,
    output: {
      path: outPath,
      filename: argv.mode === 'production' ? '[name].min.js' : '[name].js'
    }
  };
};
