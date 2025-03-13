<img height="56px" src="https://oneb.app/_ipx/q_95/image/LogoOneB.png" alt="OneB Logo">
---

### <img height="14px" src="https://www.liqpay.ua/logo_lp_national_dk.svg?v=1740668938035"> PHP SDK
Редакція від OneB
#### Requirements
- php >=7.4

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