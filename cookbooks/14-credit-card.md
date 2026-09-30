# Credit Card (Wallet Top-up)

Shipit wallet customers can save a credit/debit card for automatic top-ups when
the wallet balance drops below 15%. Cards are stored with Stripe, not Shipit.

**Important:** Call these endpoints with the **merchant user's** API key and
secret (the credentials returned from `/v1/register`), not a platform partner key.

## Add a credit card

```php
$response = $shipit->creditCard()->add('https://myapp.example/store/1/shipit/card/return');

// Redirect the merchant to Stripe Checkout
header('Location: ' . $response->redirect);
exit;
```

After the merchant completes Stripe Checkout, Shipit redirects them to your
`returnUrl` with `event=credit_card.added`, for example:

```
https://myapp.example/store/1/shipit/card/return?event=credit_card.added
```

## Check whether a card is configured

```php
$status = $shipit->creditCard()->has();

if ($status->hasCreditCard) {
    // Merchant can create shipments that bill the wallet
}
```

## Remove a credit card

```php
$result = $shipit->creditCard()->remove();

if ($result->isSuccess()) {
    echo $result->message; // "Credit card removed successfully."
}
```

## See also

- [Balance & Accounting](./10-balance-accounting.md)
- [Shipit API Reference – Credit Card](https://apidocs.shipit.ax/)
- [Payment methods (product docs)](https://shipit.fi/en-US/instructions/payment-methods)
