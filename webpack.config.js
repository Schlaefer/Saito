const path = require('path');
const webpack = require('webpack');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

module.exports = {
  mode: 'development',
  // mode: 'production',
  devtool: 'eval-source-map',
  entry: {
    app: './frontend/src/index.js',
    exports: './frontend/src/exports.js',
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'webroot/js'),
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
    modules: [path.join(__dirname, 'frontend/src'), 'node_modules'],
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
    // Set the locales to include for moment.js
    new MomentLocalesPlugin({localesToKeep: ['de', 'en']}),
  ],
};
