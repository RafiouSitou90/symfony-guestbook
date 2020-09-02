const webpack = require('webpack');
const Encore = require("@symfony/webpack-encore");
const HtmlWebpackPlugin = require("html-webpack-plugin");

Encore
    .setOutputPath("public/")
    .setPublicPath("/")
    .cleanupOutputBeforeBuild()
    .addEntry("app", "./src/app.js")
    .enablePreactPreset()
    .enableSassLoader()
    .enableSingleRuntimeChunk()
    .addPlugin(new HtmlWebpackPlugin({
        template: "src/index.ejs",
        alwaysWriteToDisk: true
    }))
    .addPlugin(new webpack.DefinePlugin({
         // 'ENV_API_ENDPOINT': JSON.stringify(process.env.API_ENDPOINT),
         'http://127.0.0.1:8000': JSON.stringify(process.env.API_ENDPOINT),
    }))
;

module.exports = Encore.getWebpackConfig();

