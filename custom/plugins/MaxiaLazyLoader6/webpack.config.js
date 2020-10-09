var path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: './src/Resources/js/main.js',
    output: {
        path: path.resolve(__dirname, 'src/Resources/public'),
        filename: 'all.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                query: {
                    presets: ['es2015']
                }
            }
        ]
    },
    stats: {
        colors: true
    },
    devtool: 'source-map'
};