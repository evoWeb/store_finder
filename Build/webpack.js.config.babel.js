import path from 'path';
import baseConfig from './webpack.config.babel';

const outPath = path.resolve(__dirname, '../Resources/Public/JavaScript');
const entry = {
  FrontendGoogleMap: path.resolve(__dirname, './Sources/TypeScript/FrontendGoogleMap.ts'),
  FrontendOsmMap: path.resolve(__dirname, './Sources/TypeScript/FrontendOsmMap.ts'),
};

module.exports = env => {
  return {
    ...baseConfig,
    entry: entry,
    mode: env.production ? 'production' : 'development',
    output: {
      path: outPath,
      filename: env.production ? '[name].min.js' : '[name].js'
    }
  }
};
