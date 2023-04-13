import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import { rimrafSync } from 'rimraf';

class RemovePlugin {
  static name = 'Remove *.LICENSE.txt';

  apply(compiler) {
    compiler.hooks.done.tap(
      RemovePlugin.name,
      this.done
    );
  }

  /**
   * @param done Stats
   */
  done(done) {
    console.log(done);
    Object.keys(done.compilation.assets).forEach(file => {
      if (file.indexOf('LICENSE.txt') > 0) {
        rimrafSync(done.compilation.outputOptions.path + '/' + file);
      }
    });
  }
}

const WebpackDefault = {
  // bundling mode
  mode: 'development',

  devtool: 'source-map',

  // file resolutions
  resolve: {
    extensions: ['.ts', '.js'],
  },

  // loaders
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
      {
        test: /\.(sass|scss)$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          'sass-loader'
        ]
      }
    ]
  },

  plugins: [
    new RemovePlugin(),
    new MiniCssExtractPlugin({
      filename: '../Stylesheet/[name].css'
    }),
  ]
};

module.exports = WebpackDefault;
