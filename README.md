## <img height="24px" src="https://www.liqpay.ua/logo_lp_national_dk.svg?v=1740668938035"> PHP SDK від <img height="36px" src="https://oneb.app/_ipx/q_95/image/LogoOneB.png" alt="OneB Logo">

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

$url = $client->createPaymentLink()
    ->setAmount(100.12)
    ->setCurrency('USD')
    ->setDescription('Оплата рахунку №1251-2')
    ->setLanguage('uk')
    ->setReferenceId('12345_lkm347sd') //Ваш унікальний ідентифікатор даного платежу
    ->setReturnUrl('https://oneb.app')
    ->setWebhookUrl('https://example.com/liqpay-webhook') // Викликаєте, якщо хочете отримати веб-хук із даними про платіж
    ->setExpirationDate(\Carbon\Carbon::now()->addHours(12))
    ->createToken() // Викликаєте, якщо потрібно токенізувати картку
    ->generate();

print_r(PHP_EOL.$url.PHP_EOL);
```

#### Оплата по токену
```php
$client = new \LiqPay\Client('<YOUR_PUBLIC_KEY>','<YOUR_PRIVATE_KEY>');

$charge = $client->createPaymentByToken()
    ->setCardToken('sandbox_token')
    ->setAmount(100.12)
    ->setCurrency('USD')
    ->setDescription('Оплата рахунку №1251-2')
    ->setLanguage('uk')
    ->setReferenceId('12345_lkm347sd') //Ваш унікальний ідентифікатор даного платежу
    ->setWebhookUrl('https://example.com/liqpay-webhook');
 
$result = $charge->dryCharge(); // Підготовка платежу - необовʼязковий крок
if(in_array($result['status'],['error','failure'])){
    $errorDescription = $client->tryDescribeError($result['err_code'])??$result['err_description']??'Невідома помилка';
    print "Помилка підготовки платежу: {$errorDescription}";
    exit(1);
}
    
$result = $charge->charge(); 

print_r(PHP_EOL.$result.PHP_EOL);
```

#### Отримання статусу платежу
```php
$client = new \LiqPay\Client('<YOUR_PUBLIC_KEY>','<YOUR_PRIVATE_KEY>');

$paymentInfo = $client->getPaymentStatus('12345_823gf3');

print_r(PHP_EOL.$paymentInfo.PHP_EOL);
```

P.S.
-----
API LiqPay містить також і інші фукнції та можливості, тут було релізовано те, що потребувалось.
Можливе розширення бібліотеки за потреби, або за допомогою ваших pull-реквестів