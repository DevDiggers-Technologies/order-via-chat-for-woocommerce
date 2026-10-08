# DevDiggers Order via Chat for WooCommerce

Free WooCommerce plugin by [DevDiggers](https://devdiggers.com/). Customers send a product or their whole cart, with delivery details, to your WhatsApp in one tap. No API account, no fees: the plugin opens an ordinary WhatsApp chat.

- **WordPress.org:** https://wordpress.org/plugins/devdiggers-order-via-chat-for-woocommerce/
- **Pro:** https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/
- **Docs:** https://docs.devdiggers.com/woocommerce-purchase-via-whatsapp/

## Features

- Order on WhatsApp button on product, shop and cart pages (classic cart and Cart block)
- Variable products, quantities and stock checks before WhatsApp opens
- Delivery details popup for guests, built from the store's own required checkout fields
- Send my order on WhatsApp on the order received page (classic and block checkout)
- WhatsApp box on the order edit screen with the order written out
- Floating chat button
- Exclusions by product and category, out of stock hiding, guest control
- Button styling with live preview, setup wizard
- Dashboard of order requests, requested value and most requested products
- Catalog mode: WhatsApp replaces Add to cart (enforced on the server), optional hidden prices
- Editable order request and order received messages with tags (`{items}`, `{subtotal}`, `{customer_name}`, `{address}`, ...)
- `[ddwcpvw_button]` shortcode and `ddwcpvw/order-button` block (server rendered through the shortcode)
- Optional GA4 / GTM `generate_lead` and Meta Pixel `Contact` events
- Device rules (all, phones and tablets only, desktop only) in CSS, cache safe

Pro adds the Twilio-powered chat assistant that creates the WooCommerce order, order notifications, recovery reminders, broadcasts, a message log and the DevDiggers Wallet integration. Those screens appear in Free as upgrade cards only; none of their code ships here.

## Development

```bash
npm install
npm run build              # webpack, then text domain replacement and POT
php bin/test-free-boundary.php
npm run build:release      # zip from .distignore
```

- Source lives in `src/`; `assets/` is built output. Every file in `assets/js` and `assets/css` must map to an entry in `webpack.config.js`.
- `bin/test-free-boundary.php` fails if any Pro file, API call, cron job, Pro setting or Pro bundle code reappears.

## Free and Pro together

Free stops loading at `plugins_loaded` when the Pro class `DDWCPVW_Init` exists. Both use the same option names, so the WhatsApp number and every display setting carry over when a store upgrades.
