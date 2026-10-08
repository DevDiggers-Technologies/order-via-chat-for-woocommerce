=== Order via Chat for WooCommerce ===
Contributors: devdiggers
Plugin URI: https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/
Author: DevDiggers
Author URI: https://devdiggers.com/
Tags: whatsapp, whatsapp order, woocommerce whatsapp, click to chat, catalog mode
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 9.0
WC tested up to: 11.2
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add an Order on WhatsApp button to WooCommerce. Customers send a product or their cart, with delivery details, to your WhatsApp in one tap.

== Description ==

Plenty of shoppers would rather ask than fill in a checkout form. They want to check a size, agree a delivery time or simply talk to a person before they pay. In many countries that conversation happens on WhatsApp.

**[Order via Chat for WooCommerce](https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/)** puts an Order on WhatsApp button on your product, shop and cart pages. One tap opens WhatsApp with the order already written out: the products, options, quantities, prices, an estimated subtotal and where to deliver. The customer presses send, and you finish the sale in the chat.

It works with your personal WhatsApp or the free WhatsApp Business app. There is no API account to set up, no monthly fee and no message limit, because the plugin only opens a chat. Nothing is sent through a third party server.

= Quick links =

* [View Demo](https://demo.devdiggers.com/woocommerce-purchase-via-whatsapp/)
* [Documentation](https://docs.devdiggers.com/woocommerce-purchase-via-whatsapp/)
* [Support](https://wordpress.org/support/plugin/order-via-chat-for-woocommerce/)
* [Upgrade to Pro](https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/)

= How it works =

1. You add your WhatsApp number and choose where the button appears.
2. A customer taps Order on WhatsApp on a product, or sends their whole cart from the cart page.
3. If they are a guest, or their account has no address yet, a short popup asks for their delivery details. Your own required checkout fields are used.
4. WhatsApp opens on their phone or computer with the full order typed in, ready to send to you.
5. You confirm the order, the price and the delivery in the chat.

= What the free plugin does =

=== WhatsApp order button on product, shop and cart pages ===

The button sits where customers already decide to buy, so ordering by chat is never more than one tap away.

* Order on WhatsApp button on single product pages, in eight positions
* Button on shop and category pages, before or after Add to cart, including Product Collection block grids
* Send the whole cart from the cart page, in the classic cart and the Cart block
* Variable products: the chosen options and the matching variation are sent, and the button waits until an option is picked
* The quantity field is respected, and stock is checked before WhatsApp opens
* Show the buttons on all devices, on phones and tablets only, or on desktop only

=== A complete order message, written your way ===

You get everything you need to confirm the order without a single follow up question.

* Product names, options, quantities, unit prices and product links
* An estimated subtotal at today's prices, including sale prices
* The delivery address, taken from the customer's account or from a short popup that uses your store's required checkout fields
* Edit the message with tags such as {items}, {subtotal}, {customer_name}, {customer_phone} and {address}. Lines whose tag is empty drop out on their own

=== Catalog mode: take orders on WhatsApp only ===

Some stores do not want a checkout at all. They quote, confirm and collect payment in the chat.

* The WhatsApp button replaces Add to cart on the products you sell through WhatsApp
* Customers still pick options and quantity on the product page
* Adding those products to the cart is blocked on the server too, so a hidden button cannot be bypassed
* Hide prices for wholesale, custom or "ask for price" products, and leave prices out of the message
* Products you exclude keep their normal checkout

=== Order button block and shortcode ===

* An Order on WhatsApp block for the block editor, with a live preview
* The `[ddwcpvw_button]` shortcode for page builders and custom templates
* Pass `id="123"` for a specific product, or `type="cart"` to send the whole cart

=== After a normal checkout ===

* A Send my order on WhatsApp button on the order received page, with the order number, items, shipping, tax, payment method and total. Works with the block checkout too
* Edit that message with tags such as {order_number}, {order_total} and {order_summary}
* A WhatsApp box on the order edit screen: one click opens a chat with the customer, the order already written out and editable before you send

=== Floating WhatsApp chat button ===

* A WhatsApp bubble in the corner of every page, or on store pages only
* Its own number, prefilled message and optional text label
* On product pages the product name and link are added to the message
* Left or right corner, and an option to hide it on phones

=== Control and design ===

* Exclude products or whole categories, which keep their normal checkout
* Hide the button on out of stock products
* Ask guests to log in first, or let them order with the popup
* Button text, colours, corner roundness, full width and WhatsApp icon, with a live preview
* A setup wizard that gets you running in about a minute

=== Order request analytics ===

* A dashboard with order requests, requested value, units requested and whole cart requests, compared with the previous period
* Daily charts and a list of your most requested products
* All figures are counted on your own site and stored in your own database
* Optional events for Google Analytics 4, Google Tag Manager and Meta Pixel when a customer opens WhatsApp with an order, using the tracking code already on your site

There is no limit on products, requests or messages in the free version.

= Who this is for =

* Stores whose customers prefer to order by message
* Shops that sell made to order, personalised or quote based products
* Wholesale and B2B catalogs that agree prices in the chat
* Local businesses that deliver by hand or take payment on delivery
* Anyone who wants a WhatsApp click to chat button without an API account

= Free vs Pro =

The free plugin opens WhatsApp and lets you take it from there. [Pro](https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/) connects your number to the official WhatsApp Business Platform through Twilio, so your store can talk to customers on its own. Pro installs on top of the free plugin and keeps your settings.

=== Orders created in the chat (Pro) ===

* A chat assistant that turns the request into a real WooCommerce order, with shipping and payment chosen in the chat
* A secure payment link for card gateways, and cash on delivery or bank transfer confirmed in the chat
* STATUS, HELP and AGENT replies, with handover to a person

=== Automatic WhatsApp messages (Pro) ===

* Order confirmations and status updates on WhatsApp
* A new order alert on your own phone
* Reminders for unfinished chats and unpaid orders
* Broadcast messages to customer groups
* WhatsApp opt in at checkout, with STOP and START handled for you

=== Records and integrations (Pro) ===

* A full message log with delivery status and CSV export
* A dashboard with WhatsApp orders, revenue and delivery rate
* Pay from a DevDiggers Wallet balance inside the chat

== Installation ==

= Automatic installation =

1. In your WordPress admin, go to **Plugins > Add New**.
2. Search for "Order via Chat for WooCommerce".
3. Click **Install Now**, then **Activate**. WooCommerce must be active.

= Manual installation =

1. Download the plugin zip file.
2. Go to **Plugins > Add New > Upload Plugin** and upload the zip.
3. Activate the plugin.

= After activating =

1. Follow the setup wizard, or go to **DevDiggers > Order via Chat > Configuration**.
2. Add your WhatsApp number with the country code and save.
3. Choose where the button appears under **Configuration > Display**.
4. Open one of your products and tap the WhatsApp button to try it.

= Requirements =

* WordPress 6.5 or later
* WooCommerce 9.0 or later
* PHP 7.4 or later

== Frequently Asked Questions ==

= How do I add an Order on WhatsApp button to WooCommerce? =

Install and activate the plugin, add your WhatsApp number in the setup wizard, and the button appears on your product and cart pages. Change its position, text and colours under **Configuration > Display**.

= Do I need a WhatsApp Business API or Twilio account? =

No. The free plugin opens an ordinary WhatsApp chat with your number, so your personal WhatsApp or the free WhatsApp Business app is enough. Only Pro, which sends messages from your store automatically, uses Twilio.

= Is an order created in WooCommerce when a customer taps the button? =

No. The customer sends you their request in WhatsApp and you agree the order with them there. If you want the chat to create the WooCommerce order by itself, that is what Pro does.

= Can I take orders on WhatsApp only, without a checkout? =

Yes. Turn on Catalog Mode under **Configuration > General**. The WhatsApp button replaces Add to cart on the products you sell through WhatsApp, and you can hide their prices too.

= Can I change the message the customer sends? =

Yes. Under **Configuration > General > WhatsApp Messages** you can rewrite the order request and the order received message with tags such as {items}, {subtotal}, {customer_name} and {address}.

= Does it work with variable products? =

Yes, on the product page. The button waits until the customer picks the options, then sends the exact variation. On shop pages the button is shown on simple products only, because options have to be picked first.

= Does it work with the Cart and Checkout blocks? =

Yes. The cart button appears under the Cart block, the shop button works in Product Collection grids, and the order received button appears on the block order confirmation page too.

= Can I add the button to a page builder or custom template? =

Yes. Use the Order on WhatsApp block, or the shortcode `[ddwcpvw_button]`. Add `id="123"` for a specific product, or `type="cart"` to send the cart. The shortcode works in Elementor, Divi and other builders.

= Which number format should I use? =

Use the full international number with the country code, for example +15551234567. If you leave out the plus, the plugin adds your store's country code.

= Can I hide the button for some products? =

Yes. Exclude single products or whole categories under **Configuration > General**. You can also hide it on out of stock products.

= Can I show the button on phones only? =

Yes. Under **Configuration > Display > Devices**, choose "Phones and tablets only". The setting uses CSS, so it works with page caching.

= What does the dashboard count? =

Every time a customer sends a product or cart to WhatsApp through the button, the plugin counts one request, the units and their value at the price shown. It cannot see whether the customer then pressed send in WhatsApp, so treat the figures as interest, not sales.

= Can I track WhatsApp orders in Google Analytics or Meta Pixel? =

Yes. When the Analytics Events option is on, each order request fires generate_lead (GA4 or Tag Manager) and Contact (Meta Pixel). It is off until you turn it on, uses the tracking code you already have, and sends nothing anywhere itself.

= Does the plugin collect personal data? =

The delivery details a customer types in the popup are only placed into their WhatsApp message. They are not stored by the plugin. The dashboard stores counts per day and per product, with no customer details.

= Does it support HPOS? =

Yes. The plugin declares compatibility with WooCommerce High Performance Order Storage and with the Cart and Checkout blocks.

= Will my settings be kept if I upgrade to Pro? =

Yes. Pro reads the same settings, including your WhatsApp number, button design and messages.

= Can I translate the plugin? =

Yes. It is translation ready and ships with a POT file in the `i18n` folder.

= Where can I get help? =

Use the [WordPress.org support forum](https://wordpress.org/support/plugin/order-via-chat-for-woocommerce/) or [contact DevDiggers](https://devdiggers.com/contact/).

== Screenshots ==

1. Order on WhatsApp button on a product page
2. Delivery details popup for guests, using your checkout fields
3. The order message, ready to send in WhatsApp
4. Send the whole cart from the cart page
5. Dashboard with order requests, requested value and the most requested products
6. General settings with catalog mode and the message templates
7. Display settings with a live button preview
8. WhatsApp box on the order edit screen

== External services ==

**WhatsApp click to chat (wa.me)**

* What it is: WhatsApp's public click to chat address, https://wa.me/.
* What it is used for: Opening a WhatsApp chat with your store's number, with the order message already typed.
* When data is sent: Only when a visitor taps an Order on WhatsApp, send order or floating chat button, or when an administrator clicks Open in WhatsApp on an order screen. The plugin itself never calls WhatsApp from your server.
* What data is sent: The visitor's browser opens the address with your WhatsApp number and the prefilled message: the products, quantities, prices and, if entered, the customer's name, phone, email and delivery address.
* Provided by WhatsApp LLC (Meta): [Terms of Service](https://www.whatsapp.com/legal/terms-of-service), [Privacy Policy](https://www.whatsapp.com/legal/privacy-policy).

The bundled DevDiggers framework, which draws the admin screens, connects to the services below. All of them run only inside the WordPress admin.

**1. DevDiggers extensions directory**

* What it is: A read only API on devdiggers.com that returns the public list of DevDiggers extensions.
* What it is used for: Showing available DevDiggers extensions on the Extensions admin page.
* When data is sent: Only when a logged in administrator opens the Extensions admin page. The response is cached for 24 hours.
* What data is sent: A standard outbound HTTP request only, meaning your server's IP address and a plugin user agent string. No personal data and no store data are sent. The page also loads each listed extension's image from devdiggers.com in the administrator's browser.
* Endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins

**2. Newsletter subscription (optional)**

* What it is: A contact and newsletter endpoint on devdiggers.com.
* What it is used for: Adding your email address to the DevDiggers newsletter, only if you choose to subscribe.
* When data is sent: Only when an administrator submits the optional newsletter form in the plugin dashboard. Nothing is sent automatically.
* What data is sent: The email address you enter and your site URL.
* Endpoint: https://devdiggers.com/?fluentcrm=1&route=contact

The services above are provided by DevDiggers. By using them you agree to the DevDiggers Terms and Conditions (https://devdiggers.com/terms-and-conditions/) and Privacy Policy (https://devdiggers.com/privacy-policy/).

**3. Gravatar (avatar images)**

* What it is: Gravatar, the avatar service operated by Automattic.
* What it is used for: WordPress core's `get_avatar_url()` shows the logged in administrator's avatar in the plugin dashboard header. This is standard WordPress behaviour.
* When data is sent: Only when a logged in administrator opens the plugin dashboard and avatars are enabled under Settings > Discussion. Turning avatars off there stops the request.
* What data is sent: A hash of the administrator's email address inside the image URL, plus the usual request data such as IP address and user agent.
* Endpoint: https://secure.gravatar.com/avatar/
* Terms of Service: https://automattic.com/terms/
* Privacy Policy: https://automattic.com/privacy/

This plugin contains no licence checks, no update checks and no telemetry.

== Source code and build tools ==

The admin, storefront and block editor JavaScript and CSS are compiled from the `src/` folder with webpack and Babel. The complete, human readable source code, including all build configuration, is published in our public GitHub repository:

https://github.com/DevDiggers-Technologies/order-via-chat-for-woocommerce

To build the assets from source:

1. Run `npm install` to install the build dependencies listed in `package.json`.
2. Run `npm run build` to compile the production assets into `assets/js` and `assets/css`.

The build configuration is in `webpack.config.js` and `babel.config.js`. Two third party libraries used by the admin screens ship unmodified under the MIT licence inside `devdiggers-framework/assets/js/`: Chart.js (https://www.chartjs.org) and Select2 (https://select2.org).

== Changelog ==

= 1.0.0 =
* Initial free release.

== Upgrade Notice ==

= 1.0.0 =
First release. After activating, run the setup wizard and add your WhatsApp number.
