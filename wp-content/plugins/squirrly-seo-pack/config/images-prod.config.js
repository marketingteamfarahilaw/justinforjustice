const RtlCssPlugin = require("rtlcss-webpack-plugin");
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

// Make sure any symlinks in the project folder are resolved:
const fs = require( "fs" )
const path = require( "path" )
const pluginDir = fs.realpathSync( process.cwd() )
const resolvePlugin = relativePath => path.resolve( pluginDir, relativePath )
// Source maps are resource heavy and can cause out of memory issue for large source files.
const autoprefixer = require( "autoprefixer" )
const shouldUseSourceMap = process.env.GENERATE_SOURCEMAP === "true"
const webpack = require( "webpack" )

const paths = {
    pluginPath: resolvePlugin( "view/assets/js/images" ),
    pluginIndex: "src/index.js",
}

module.exports = {
    plugins: [
        new RtlCssPlugin({filename: `[name]-rtl.css`})
    ],
    entry: {
        "index": paths.pluginPath + '/' + paths.pluginIndex,
    },
    output: {
        pathinfo: true,
        path: paths.pluginPath,
        filename: "[name].js",
    },
    devtool: shouldUseSourceMap ? "source-map" : false,
    module: {
        rules: [
            {
                test: /\.(js|jsx|mjs)$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: "babel-loader",
                    options: {
                        cacheDirectory: true,
                    },
                },
            }
        ],
    },

    stats: "minimal"

};