import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import terser from '@rollup/plugin-terser';
import commonjs from '@rollup/plugin-commonjs';

const compress = process.env.COMPRESS === 'true',
    outDir = '../Resources/Public/JavaScript/';

export default {
    input: './Public/form-engine/element/backend-osm-map.js',
    output: {
        compact: compress,
        format: 'es',
        file: '../Resources/Public/JavaScript/form-engine/element/backend-osm-map' + (compress ? '.min' : '') + '.js',
        sourcemap: true,
        sourcemapFile: '../Resources/Public/JavaScript/form-engine/element/backend-osm-map' + (compress ? '.min' : '') + '.js.map',
    },
    plugins: !compress ? [] : [
        typescript({
            outDir: outDir,
            sourceMap: true,
            outputToFilesystem: true,
        }),
        resolve(),
        commonjs({ transformMixedEsModules: true }),
        terser({ sourceMap: true })
    ],
    external: [
        '@evoweb/store-finder/leaflet/leaflet-src.esm.js'
    ]
}
