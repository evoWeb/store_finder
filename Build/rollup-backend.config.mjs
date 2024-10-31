import terser from '@rollup/plugin-terser';
const compress = process.env.COMPRESS === 'true';

export default {
  input: './Public/form-engine/element/backend-osm-map.js',
  output: {
    compact: compress,
    file: '../Resources/Public/JavaScript/form-engine/element/backend-osm-map' + (compress ? '.min' : '') + '.js',
    sourcemap: true,
    sourcemapFile: '../Resources/Public/JavaScript/form-engine/element/backend-osm-map' + (compress ? '.min' : '') + '.js.map',
  },
  plugins: !compress ? [] : [
    terser({
      sourceMap: true,
    })
  ],
  external: [
    '@evoweb/store-finder/leaflet/leaflet-src.esm.js'
  ]
}
