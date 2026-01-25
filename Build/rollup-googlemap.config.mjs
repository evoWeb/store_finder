import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
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
    plugins: !compress ? [
        resolve()
    ] : [
        resolve(),
        terser({
            sourceMap: true,
        })
    ]
}
