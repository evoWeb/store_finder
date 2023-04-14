import { terser } from "rollup-plugin-terser";

export default {
  input: '../Resources/Public/JavaScript/form-engine/element/backend-osm-map.js',
  output: {
    dir: '../Resources/Public/JavaScript/form-engine/element/'
  },
  plugins: [terser()]
}
