<?php
include __DIR__ . '/../vendor/autoload.php';

$client = new \LiqPay\Client('sandbox_i15811467776','sandbox_lvZ4MkXLtaXx0ogS64Rt9Qj0ogIRKClMsdtgSs12');

$generator = new \LiqPay\PaymentLink($client);
$url = $generator->setAmount(100.12)
    ->setCurrency('USD')
    ->setDescription('Оплата рахунку OneB №1251-2')
    ->setLanguage('uk')
    ->setReferenceId('12344')
    ->setReturnUrl('https://oneb.app')
    ->setExpirationDate(\Carbon\Carbon::now()->addHours(12))
    ->generate();

print_r(PHP_EOL.$url.PHP_EOL);