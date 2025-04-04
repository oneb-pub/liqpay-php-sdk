<?php
include __DIR__ . '/../vendor/autoload.php';

$client = new \LiqPay\Client('YOUR_PUBLIC_KEY','YOUR_PRIVATE_KEY');

//$status = $client->getPaymentStatus('12345');
//dd($status);

//$merchantInfo = $client->getMerchantInfo();
//dd($merchantInfo);

$reference_id = 'charge12349';

$generator = $client->createPaymentByToken();
$res = $generator->setAmount(100.12)
    ->setCurrency('UAH')
    ->setDescription('Оплата рахунку OneB №1251-2')
    ->setLanguage('uk')
    ->setReferenceId($reference_id)
    ->setWebhookUrl('https://webhook.site/8902cdfb-c319-4b37-9f46-a2d1d1bdc321')
    ->setCardToken('sandbox_token')
    ->dryCharge();

dd($res);

//$info = $client->addInfoToPayment($reference_id,'Some dop info');

print_r(PHP_EOL.$url.PHP_EOL);

//dd($info);

