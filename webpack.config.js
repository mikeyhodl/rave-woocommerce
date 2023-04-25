/**
 * Webpack Configuration.
 */

const path = require( 'path' );
const webpack = require( 'webpack' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

const CLIENT_DIR = path.resolve( __dirname, 'client' );

const entry = {
	index: CLIENT_DIR + '/blocks/index.js',
};

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
				publicPath:
					'production' === process.env.NODE_ENV ? '../' : '../../',
			},
		},
	},
	// {
	// 	test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
	// 	exclude: [ IMG_DIR, /node_modules/ ],
	// 	use: {
	// 		loader: 'file-loader',
	// 		options: {
	// 			name: '[path][name].[ext]',
	// 			publicPath:
	// 				'production' === process.env.NODE_ENV ? '../' : '../../',
	// 		},
	// 	},
	// },
];

module.exports = {
	...defaultConfig,
	devtool:
		process.env.NODE_ENV === 'production'
			? 'hidden-source-map'
			: defaultConfig.devtool,
	optimization: {
		...defaultConfig.optimization,
		minimizer: [
			...defaultConfig.optimization.minimizer.map( ( plugin ) => {
				if ( plugin.constructor.name === 'TerserPlugin' ) {
					// wp-scripts does not allow to override the Terser minimizer sourceMap option, without this
					// `devtool: 'hidden-source-map'` is not generated for js files.
					plugin.options.sourceMap = true;
				}
				return plugin;
			} ),
		],
		splitChunks: undefined,
	},
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin(),
		// new webpack.DefinePlugin( {
		// 	__PAYMENT_METHOD_FEES_ENABLED: JSON.stringify(
		// 		process.env.PAYMENT_METHOD_FEES_ENABLED === 'true'
		// 	),
		// } ),
	],
	resolve: {
		extensions: [ '.json', '.js', '.jsx' ],
		modules: [ CLIENT_DIR, 'node_modules' ],
		alias: {
			wcflutterwave: CLIENT_DIR,
		},
	},
	entry,
	// module: {
	// 	rules,
	// },
	// externals: {
	// 	jquery: 'jQuery',
	// },
};
