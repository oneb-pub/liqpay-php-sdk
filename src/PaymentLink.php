<?php

namespace LiqPay;

use Carbon\Carbon;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class PaymentLink
{
    private Client $client;
    private $amount;
    private string $currency;
    private string $description;
    private string $reference_id;
    private ?string $return_url = null;
    private ?string $webhook_url = null;
    private $expiration_date = null;
    private ?string $language = null;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setAmount($amount): self
    {
        Assert::regex(
            (string)$amount,
            '/^(?:\d+|\d+\.\d{2})$/',
            'Значення має бути або цілим числом, або дробовим з рівно двома знаками після коми.'
        );
        $this->amount = $amount;
        return $this;
    }

    public function setCurrency(string $currency): self
    {
        Assert::inArray($currency, $this->client->get_supported_currencies(), 'Вказана валюта не підтримується');
        $this->currency = $currency;
        return $this;
    }

    public function setDescription(string $description): self
    {
        Assert::stringNotEmpty($description, 'Опис не може бути порожнім');
        $this->description = $description;
        return $this;
    }

    public function setReferenceId(string $order_id): self
    {
        Assert::stringNotEmpty($order_id, 'Портібно вказати унікальний ID покупки у Вашому магазині');
        Assert::maxLength($order_id, 255, 'Завеликий ідентифікатор покупки');
        $this->reference_id = $order_id;
        return $this;
    }

    public function setReturnUrl(string $return_url): self
    {
        Assert::maxLength($return_url, 510, 'Завеликий URL повернення');
        if (!filter_var($return_url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Значення має бути валідною URL");
        }
        $this->return_url = $return_url;
        return $this;
    }

    public function setWebhookUrl(string $webhook_url): self
    {
        Assert::maxLength($webhook_url, 510, 'Завеликий URL вебхуку');
        if (!filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Значення має бути валідною URL");
        }
        $this->webhook_url = $webhook_url;
        return $this;
    }

    public function setExpirationDate($expiration_date): self
    {
        if (is_string($expiration_date)) {
            $expiration_date = Carbon::createFromTimestamp(strtotime($expiration_date))->format('Y-m-d H:i:s');
        } elseif ($expiration_date instanceof Carbon) {
            $expiration_date = $expiration_date->format('Y-m-d H:i:s');
        } else {
            throw new InvalidArgumentException('Дата закінчення дії посилання на оплату вказана невірно');
        }
        $this->expiration_date = $expiration_date;
        return $this;
    }

    public function setLanguage(string $language): self
    {
        Assert::inArray($language, ['uk', 'en'], 'Вказана мова не підтримується');
        $this->language = $language;
        return $this;
    }

    public function generate(): string
    {
        Assert::notNull($this->amount);
        Assert::notNull($this->currency);
        Assert::notNull($this->description);
        Assert::notNull($this->reference_id);

        $params = [
            'action' => 'pay',
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'order_id' => $this->reference_id,
            'version' => '3',
        ];
        if ($this->return_url) {
            $params['result_url'] = $this->return_url;
        }
        if ($this->webhook_url) {
            $params['server_url'] = $this->webhook_url;
        }
        if ($this->expiration_date) {
            $params['expiration_date'] = $this->expiration_date;
        }
        if ($this->language) {
            $params['language'] = $this->language;
        }

        $params = $this->client->cnb_params($params);

        $data = $this->client->encode_params($params);
        $signature = $this->client->cnb_signature($params);

        return $this->client->get_checkout_url() . '?' . http_build_query([
                'data' => $data,
                'signature' => $signature,
            ]);
    }

}