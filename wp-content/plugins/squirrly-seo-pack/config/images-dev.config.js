const RtlCssPlugin = require("rtlcss-webpack-plugin");
const defaultConfig = require("@wordpress/scripts/config/webpack.config");


// Make sure any symlinks in the project folder are resolved:
const fs = require( "fs" )
const path = require( "path" )
const pluginDir = fs.realpathSync( process.cwd() )
const resolvePlugin = relativePath => path.resolve( pluginDir, relativePath )

const autoprefixer = require( "autoprefixer" )

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
    devtool: "eval",
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
    }


};