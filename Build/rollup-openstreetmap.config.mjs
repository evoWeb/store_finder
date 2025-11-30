import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import terser from '@rollup/plugin-terser';

const compress = process.env.COMPRESS === 'true';


export default {
    input: './Public/FrontendOsmMap.js',
    output: {
        compact: compress,
        file: '../Resources/Public/JavaScript/FrontendOsmMap' + (compress ? '.min' : '') + '.js',
        sourcemap: true,
        sourcemapFile: '../Resources/Public/JavaScript/FrontendOsmMap' + (compress ? '.min' : '') + '.js.map',
    },
    plugins: !compress ? [
        resolve(),
        commonjs({
            include: /node_modules/,
            // Manually define named exports for the Leaflet source file
            syntheticNamedExports: [
                'node_modules/leaflet/dist/leaflet-src.js',
            ],
        }),
    ] : [
        resolve(),
        commonjs({
            include: /node_modules/,
            // Manually define named exports for the Leaflet source file
            syntheticNamedExports: [
                'node_modules/leaflet/dist/leaflet-src.js',
            ],
        }),
        terser({
            sourceMap: true,
        })
    ]
}
