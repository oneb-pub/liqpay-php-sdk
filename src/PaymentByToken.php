<?php

namespace LiqPay;

use Carbon\Carbon;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class PaymentByToken
{
    private Client $client;
    private $amount;
    private string $card_token;
    private string $currency;
    private string $description;
    private string $reference_id;
    private ?string $webhook_url = null;
    private ?string $language = null;
    private bool $is_preparation = false;
    private ?bool $is_recurring = null;

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

    public function setCardToken(string $token): self
    {
        Assert::stringNotEmpty($token, 'Нема токену картки');
        $this->card_token = $token;
        return $this;
    }

    public function setReferenceId(string $order_id): self
    {
        Assert::stringNotEmpty($order_id, 'Портібно вказати унікальний ID покупки у Вашому магазині');
        Assert::maxLength($order_id, 255, 'Завеликий ідентифікатор покупки');
        $this->reference_id = $order_id;
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

    public function setLanguage(string $language): self
    {
        Assert::inArray($language, ['uk', 'en'], 'Вказана мова не підтримується');
        $this->language = $language;
        return $this;
    }

    public function sellerInitiator(): self
    {
        $this->is_recurring = true;
        return $this;
    }

    public function payerInitiator(): self
    {
        $this->is_recurring = false;
        return $this;
    }

    public function prepare(bool $prepare = true): self
    {
        $this->is_preparation = true;
        return $this;
    }

    public function charge(array $extraArguments = []): array
    {
        Assert::notNull($this->amount);
        Assert::notNull($this->card_token);
        Assert::notNull($this->currency);
        Assert::notNull($this->description);
        Assert::notNull($this->reference_id);
        Assert::notNull($this->webhook_url);

        $params = [
            'action' => 'pay',
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'order_id' => $this->reference_id,
            'version' => '3',
            'card_token' => $this->card_token,
            'server_url' => $this->webhook_url
        ];
        if ($this->language) {
            $params['language'] = $this->language;
        }
        if ($this->is_preparation) {
            $params['prepare'] = '1';
        }
        if (isset($this->is_recurring)) {
            $params['is_recurring'] = $this->is_recurring;
        }
        if ($extraArguments) {
            $params = array_merge($params, $extraArguments);
        }

        return $this->client->api('request', $params);
    }

}