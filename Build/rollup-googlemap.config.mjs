import { nodeResolve } from '@rollup/plugin-node-resolve';
import terser from '@rollup/plugin-terser';
const compress = process.env.COMPRESS === 'true';


export default {
  input: './Public/FrontendGoogleMap.js',
  output: {
    compact: compress,
    file: '../Resources/Public/JavaScript/FrontendGoogleMap' + (compress ? '.min' : '') + '.js',
    sourcemap: true,
    sourcemapFile: '../Resources/Public/JavaScript/FrontendGoogleMap' + (compress ? '.min' : '') + '.js.map',
  },
  plugins: !compress ? [ nodeResolve() ] : [
    nodeResolve(),
    terser({
      sourceMap: true,
    })
  ]
}
