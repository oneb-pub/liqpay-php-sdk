<?php
declare(strict_types=1);

namespace LiqPay;

use InvalidArgumentException;
use src\Exceptions\LiqPayApiException;

class Client
{
    private string $_api_url = 'https://www.liqpay.ua/api/';
    private string $_checkout_url = 'https://www.liqpay.ua/api/3/checkout';
    protected array $_supportedCurrencies = ['EUR', 'USD', 'UAH'];
    protected array $_supportedLangs = ['uk', 'en'];
    private string $_public_key;
    private string $_private_key;
    private ?int $_server_response_code = null;

    protected array $_button_translations = [
        'uk' => 'Сплатити',
        'en' => 'Pay'
    ];
    protected array $_actions = [
        "pay", "hold", "subscribe", "paydonate"
    ];
    private GuzzleRequester $requester;

    /**
     * Конструктор.
     *
     * @param string      $public_key
     * @param string      $private_key
     * @param string|null $api_url   (опціонально)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $public_key, string $private_key, ?string $api_url = null)
    {
        if (empty($public_key)) {
            throw new InvalidArgumentException('public_key is empty');
        }
        if (empty($private_key)) {
            throw new InvalidArgumentException('private_key is empty');
        }

        $this->requester = new GuzzleRequester();

        $this->_public_key = $public_key;
        $this->_private_key = $private_key;

        if (null !== $api_url) {
            $this->_api_url = $api_url;
        }
    }

    /**
     * Викликає API.
     *
     * @param string $path
     * @param array  $params
     * @param int    $timeout
     *
     * @return mixed
     */
    public function api(string $path, array $params = [], int $timeout = 5): array
    {
        $params = $this->check_required_params($params);
        $url = $this->_api_url . $path;
        $data = $this->encode_params($params);
        $signature = $this->str_to_sign($this->_private_key . $data . $this->_private_key);
        $postFields = [
            'data'      => $data,
            'signature' => $signature
        ];

        $responseData = $this->requester->makeRequest($url, $postFields, $timeout);
        $this->_server_response_code = $this->requester->getLastResponseCode();
        if ($responseData === null) {
            throw new LiqPayApiException('API call error');
        }
        return $responseData;
    }

    /**
     * Повертає останній HTTP-код відповіді.
     *
     * @return int|null
     */
    public function getResponseCode(): ?int
    {
        return $this->_server_response_code;
    }

    /**
     * Генерує HTML-форму для оплати.
     *
     * @param array $params
     *
     * @return string
     */
    public function cnb_form(array $params): string
    {
        $language = 'uk';
        if (isset($params['language']) && in_array($params['language'], $this->_supportedLangs, true)) {
            $language = $params['language'];
        }

        $params = $this->cnb_params($params);
        $data = $this->encode_params($params);
        $signature = $this->cnb_signature($params);

        return sprintf(
            '<form method="POST" action="%s" accept-charset="utf-8">
                %s
                %s
                <script type="text/javascript" src="https://static.liqpay.ua/libjs/sdk_button.js"></script>
                <sdk-button label="%s" background="#77CC5D" onClick="submit()"></sdk-button>
            </form>',
            $this->_checkout_url,
            sprintf('<input type="hidden" name="data" value="%s" />', $data),
            sprintf('<input type="hidden" name="signature" value="%s" />', $signature),
            $this->_button_translations[$language]
        );
    }

    /**
     * Повертає сирі дані для створення власної форми.
     *
     * @param array $params
     *
     * @return array
     */
    public function cnb_form_raw(array $params): array
    {
        $params = $this->cnb_params($params);

        return [
            'url'       => $this->_checkout_url,
            'data'      => $this->encode_params($params),
            'signature' => $this->cnb_signature($params)
        ];
    }

    /**
     * Обчислює підпис для checkout.
     *
     * @param array $params
     *
     * @return string
     */
    public function cnb_signature(array $params): string
    {
        $params = $this->cnb_params($params);
        $json = $this->encode_params($params);
        return $this->str_to_sign($this->_private_key . $json . $this->_private_key);
    }

    /**
     * Перевіряє обов’язкові параметри.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function check_required_params(array $params): array
    {
        $params['public_key'] = $this->_public_key;

        if (!isset($params['version'])) {
            throw new InvalidArgumentException('version is null');
        }

        if (!isset($params['action'])) {
            throw new InvalidArgumentException('action is null');
        }
        return $params;
    }

    /**
     * Підготовка параметрів для checkout.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function cnb_params(array $params): array
    {
        $params = $this->check_required_params($params);

        if (!isset($params['amount'])) {
            throw new InvalidArgumentException('amount is null');
        }

        if (!isset($params['currency'])) {
            throw new InvalidArgumentException('currency is null');
        }
        if (!in_array($params['currency'], $this->_supportedCurrencies, true)) {
            throw new InvalidArgumentException('currency is not supported');
        }

        if (!isset($params['description'])) {
            throw new InvalidArgumentException('description is null');
        }

        return $params;
    }

    /**
     * Кодування параметрів.
     *
     * @param array $params
     *
     * @return string
     */
    public function encode_params(array $params): string
    {
        return base64_encode(json_encode($params));
    }

    /**
     * Декодування параметрів.
     *
     * @param string $params
     *
     * @return array
     */
    public function decode_params(string $params): array
    {
        return json_decode(base64_decode($params), true);
    }

    /**
     * Створення підпису.
     *
     * @param string $str
     *
     * @return string
     */
    public function str_to_sign(string $str): string
    {
        return base64_encode(sha1($str, true));
    }

    public function get_supported_langs(): array
    {
        return $this->_supportedLangs;
    }

    public function get_supported_currencies(): array
    {
        return $this->_supportedCurrencies;
    }

    public function get_checkout_url(): string
    {
        return $this->_checkout_url;
    }
}
