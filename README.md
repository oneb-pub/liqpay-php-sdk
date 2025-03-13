### LiqPay PHP SDK
(Редакція від OneB)

#### Поточна документація від LiqPay
https://www.liqpay.ua/documentation/en

#### Встановлення
```bash
composer require oneb-pub/liqpay-php-sdk
```

#### Генерація посилання на оплату
```php
$client = new \LiqPay\Client('<YOUR_PUBLIC_KEY>','<YOUR_PRIVATE_KEY>');

$generator = new \LiqPay\PaymentLink($client);
$url = $generator->setAmount(100.12)
    ->setCurrency('USD')
    ->setDescription('Оплата рахунку №1251-2')
    ->setLanguage('uk')
    ->setReferenceId('12344')
    ->setReturnUrl('https://oneb.app')
    ->setExpirationDate(\Carbon\Carbon::now()->addHours(12))
    ->generate();

print_r(PHP_EOL.$url.PHP_EOL);
```