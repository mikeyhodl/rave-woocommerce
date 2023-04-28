## Activate

```
start_path: /wp-admin/plugins.php?plugin_status=search&s=rave
```

If you have activated the plugin skip this step.

### Click Activate under Flutterwave WooCommerce plugin.

I see **Plugin activated** notice.

### Disable WooCommerce While the Plugin is still Activated/

I see a notice to either **Activate WooCommerce** or **Disable Flutterwave WooCommerce**.
 
## Enable the Flutterwave WooCommerce Live Mode

To be able to make collections with Flutterwave WooCommerce, you need to enable it as a payment Acquirer, supply your API keys and setup the Webhook feature.

### Go to WooCommerce > Settings > Payments

I see Flutterwave. Flutterwave allows you to accept payment from cards and bank accounts in multiple currencies. You can also accept payment offline via USSD and POS. Click on **Manage**

### Click into Flutterwave via Manage button

I see the Flutterwave settings form.

### Click Enable Flutterwave WooCommerce Checkbox
Let's enable test mode to see the full flow without any issues.
1.  Fill the Test Public Key text field
2.  Fill the Test Secret Key text field

### Click Save changes button at the bottom

I see **Your settings have been saved** notice.

## Test Checkout with Flutterwave in Test Mode

To be able to test checkout flow with Flutterwave in Test Mode, you will need add a test product to the cart, and then checkout using Flutterwave as the payment method.
