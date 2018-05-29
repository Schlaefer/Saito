const path = require('path');
const webpack = require('webpack');

module.exports = {
  mode: 'development',
  // mode: 'production',
  devtool: 'eval-source-map',
  entry: {
    app: './webroot/src/index.js',
    exports: './webroot/src/exports.js',
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'webroot/dist'),
  },
  module: {
    rules: [
      {
        test: /\.html$/,
        loader: 'underscore-template-loader',
        query: {
          engine: 'underscore',
        },
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
        query: {
            presets: ['env']
        },
      },
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
    ]
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /node_modules/, // you may add "vendor.js" here if you want to
          name: "vendor",
          chunks: "initial",
          enforce: true
        },
      },
    }
  },
  resolve: {
    /*
    alias: {
      "underscore": "lodash",
    },
    */
    extensions: ['.js', '.ts'],
    modules: [path.join(__dirname, 'webroot/src'), 'node_modules'],
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
  ],
};
