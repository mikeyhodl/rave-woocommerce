/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import { PAYMENT_METHOD_NAME } from './constants';
import {
	getBlocksConfiguration,
} from 'wcflutterwave/blocks/utils';

/**
 * Content component
 */
const Content = () => {
	return <div>{ decodeEntities( getBlocksConfiguration()?.description || __('You may be redirected to a secure page to complete your payment.', 'woocommerce-rave') ) }</div>;
};

const FLW_ASSETS = getBlocksConfiguration()?.asset_url ?? null;


const paymentMethod = {
	name: PAYMENT_METHOD_NAME,
	label: (
		<img
			src={ `${ FLW_ASSETS }/img/rave.png` }
			alt={ decodeEntities(
				settings.title || __( 'Flutterwave', 'woocommerce-rave' )
			) }
		/>
	),
	placeOrderButtonLabel: __(
		'Proceed to Flutterwave',
		'woocommerce-rave'
	),
	ariaLabel: decodeEntities(
		getBlocksConfiguration()?.title ||
		__( 'Payment via Flutterwave', 'woocommerce-rave' )
	),
	canMakePayment: () => true,
	content: <Content />,
	edit: <Content />,
	paymentMethodId: PAYMENT_METHOD_NAME,
	supports: {
		features:  getBlocksConfiguration()?.supports ?? [],
	},
}

export default paymentMethod;