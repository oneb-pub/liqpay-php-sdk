<?php
include __DIR__ . '/../vendor/autoload.php';

$client = new \LiqPay\Client('sandbox_test','sandbox_test');

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