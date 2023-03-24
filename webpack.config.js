/**
 * Webpack Configuration.
 */

const path = require("path");

// ASSETS DIRECTORY PATH
const JS_DIR = path.resolve( __dirname, 'assets/src/js' );
const IMG_DIR = path.resolve( __dirname, 'assets/src/img' );
const BUILD_DIR = path.resolve( __dirname, 'assets/build' );

const entry = {
    checkout: JS_DIR + "/checkout.js",
}

const output =  {
    path: BUILD_DIR,
    filename: "js/[name].js",
}

const rules = [
	// {
	// 	test: /\.js$/,
	// 	include: [ JS_DIR ],
	// 	exclude: /node_modules/,
	// 	use: 'babel-loader'
	// },
	// {
	// 	test: /\.scss$/,
	// 	exclude: /node_modules/,
	// 	use: [
	// 		MiniCssExtractPlugin.loader,
	// 		'css-loader',
	// 		'sass-loader',
	// 	]
	// },
	{
		test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
		use: {
			loader: 'file-loader',
			options: {
				name: 'img/[name].[ext]',
				publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
			}
		}
	},
	{
		test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
		exclude: [ IMG_DIR, /node_modules/ ],
		use: {
			loader: 'file-loader',
			options: {
				name: '[path][name].[ext]',
				publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
			}
		}
	}
];

module.exports = {
    entry,
	output,
    module: {
		rules,
    },
    externals: {
		jquery: 'jQuery'
	}
}

