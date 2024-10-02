import fs from 'fs';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import path from 'path';

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
    Object.keys(done.compilation.assets).forEach(file => {
      if (file.indexOf('LICENSE.txt') > 0) {
        fs.unlinkSync(done.compilation.outputOptions.path + '/' + file);
      }
    });
  }
}

const __dirname = path.resolve();
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
      filename: '..' + path.resolve(__dirname, '../Resources/Public/Stylesheet/[name].min.css')
    }),
  ]
};

export default WebpackDefault;
