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


    protected array $errorMessages = [
        "limit" => "Перевищено ліміт на суму або кількість платежів клієнта",
        "frod" => "Транзакція визначена як нетипова / ризикова згідно Anti-Fraud правилами Банку",
        "decline" => "Транзакція визначена як нетипова / ризикова згідно Anti-Fraud системи Банку",
        "err_auth" => "Необхідна авторизація",
        "err_cache" => "Минув час зберігання даних для даної операції",
        "user_not_found" => "Користувач не знайдений",
        "err_sms_send" => "Не вдалося відправити смс",
        "err_sms_otp" => "Пароль із смс вказаний невірно",
        "shop_blocked" => "Магазин заблоковано",
        "shop_not_active" => "Магазин не активний",
        "invalid_signature" => "Невірний підпис запиту",
        "order_id_empty" => "Передано порожній order_id",
        "err_shop_not_agent" => "Ви не є агентом для вказаного магазину",
        "err_card_def_notfound" => "Картка для отримання платежів не знайдена у гаманці",
        "err_no_card_token" => "У користувача немає картки з таким card_token",
        "err_card_liqpay_def" => "Вкажіть іншу картку",
        "err_card_type" => "Невірний тип картки",
        "err_card_country" => "Вкажіть іншу картку",
        "err_limit_amount" => "Сума переказу менше або більше заданого ліміту",
        "err_payment_amount_limit" => "Сума переказу менше або більше заданого ліміту",
        "amount_limit" => "Перевищено ліміт суми",
        "payment_err_sender_card" => "Вкажіть іншу картку відправника",
        "payment_processing" => "Платіж обробляється",
        "err_payment_discount" => "Знижка для даного платежу не знайдена",
        "err_wallet" => "Не вдалося завантажити гаманець",
        "err_get_verify_code" => "Необхідна верифікація картки",
        "err_verify_code" => "Невірний код верифікації",
        "wait_info" => "Очікується додаткова інформація, спробуйте пізніше",
        "err_path" => "Невірна адреса запиту",
        "err_payment_cash_acq" => "Платіж не може бути проведений в цьому магазині",
        "err_split_amount" => "Сума платежів розщеплення не співпадає з сумою платежу",
        "err_card_receiver_def" => "Отримувач не встановив картку для отримання платежів",
        "payment_err_status" => "Невірний статус платежу",
        "public_key_not_found" => "Не знайдено public_key",
        "payment_not_found" => "Платіж не знайдено",
        "payment_not_subscribed" => "Платіж не є регулярним",
        "wrong_amount_currency" => "Валюта платежу не співпадає с валютою debit",
        "err_amount_hold" => "Сума не може бути більше суми платежу",
        "err_access" => "Помилка доступу",
        "order_id_duplicate" => "Такий order_id вже існує",
        "err_blocked" => "Доступ в акаунт закрито",
        "err_empty" => "Параметр не заповнений",
        "err_empty_phone" => "Параметр phone не заповнений",
        "err_missing" => "Параметр не передано",
        "err_wrong" => "Невірно вказано параметр",
        "err_wrong_currency" => "Невірно вказана валюта. Використовуйте: USD, UAH, EUR",
        "err_phone" => "Введено невірний номер телефону",
        "err_card" => "Невірно вказано номер картки",
        "err_card_bin" => "Бін картки не знайдено",
        "err_terminal_notfound" => "Термінал не знайдено",
        "err_commission_notfound" => "Комісія не знайдена",
        "err_payment_create" => "Не вдалося створити платіж",
        "err_mpi" => "Не вдалося перевірити картку",
        "err_currency_is_not_allowed" => "Валюта заборонена",
        "err_look" => "Не вдалося завершити операцію",
        "err_mods_empty" => "Не вдалося завершити операцію",
        "payment_err_type" => "Невірний тип платежу",
        "err_payment_currency" => "Валюта картки чи переводу заборонені",
        "err_payment_exchangerates" => "Не вдалося знайти відповідний курс валют",
        "err_signature" => "Невірний підпис запиту",
        "err_api_action" => "Не переданий параметр action",
        "err_api_callback" => "Не переданий параметр callback",
        "err_api_ip" => "У цьому мерчанті заборонений виклик API з цього IP",
        "expired_phone" => "Закінчився термін підтвердження платежу введенням номера телефону",
        "expired_3ds" => "Закінчився термін 3DS верифікації клієнта",
        "expired_otp" => "Закінчився термін підтвердження платежу OTP паролем",
        "expired_cvv" => "Закінчився термін підтвердження платежу введeнням CVV коду",
        "expired_p24" => "Закінчився термін вибору картки в Приват24",
        "expired_sender" => "Закінчився термін отримання даних відправника",
        "expired_pin" => "Закінчився термін підтвердження платежу pin-кодом картки",
        "expired_ivr" => "Закінчився термін підтвердження платежу викликом IVR",
        "expired_captcha" => "Закінчився термін підтвердження платежу за допомогою captcha",
        "expired_password" => "Закінчився термін підтвердження платежу паролем Приват24",
        "expired_senderapp" => "Закінчився термін підтвердження платежу формою в Приват24",
        "expired_prepared" => "Закінчився термін завершення створеного платежу",
        "expired_mp" => "Закінчився термін завершення платежу у гаманці MasterPass",
        "expired_qr" => "Закінчився термін підтвердження платежу скануванням QR коду",
        "5" => "Картка не підтримує 3DSecure",
        // Помилки фінансові
        "90" => "Загальна помилка під час обробки",
        "101" => "Токен створений не цим мерчантом",
        "102" => "Надісланий токен не активний",
        "103" => "Досягнута максимальна сума покупок по токену",
        "104" => "Ліміт транзакцій по токену вичерпаний",
        "105" => "Картка не підтримується",
        "106" => "Мерчанту не дозволена преавторізація",
        "107" => "Еквайєр не підтримує 3ds",
        "108" => "Такий токен не існує",
        "109" => "Перевищено ліміт спроб з даного IP",
        "110" => "Сесія закінчилася",
        "111" => "Бранч картки заблокований",
        "112" => "Досягнуто денний ліміт картки по бранчу",
        "113" => "Тимчасово закрита можливість проведення P2P-платежів з карток ПБ на картки зарубіжних банків",
        "114" => "Досягнуто ліміт по комплітам",
        "115" => "Невірне ім'я одержувача",
        "2903" => "Досягнуто денний ліміт використання картки",
        "2915" => "Такий order_id вже існує",
        "3914" => "Платежі для даної країни заборонені",
        "9851" => "Термін дії картки закінчився",
        "9852" => "Невірний номер картки",
        "9854" => "Платіж відхилено. Спробуйте пізніше",
        "9855" => "Картка не підтримує даний вид транзакції",
        "9857" => "Картка не підтримує даний вид транзакції",
        "9859" => "Недостатньо коштів",
        "9860" => "Перевищено ліміт операцій по картці",
        "9861" => "Перевищено ліміт на оплату в інтернеті",
        "9863" => "На картці встановлено обмеження. Зверніться в підтримку банку",
        "9867" => "Невірно вказана сума транзакції",
        "9868" => "Платіж відхилено. Банк не підтвердив операцію. Зверніться в банк",
        "9872" => "Банк не підтвердив операцію. Зверніться в банк",
        "9882" => "Невірно передані параметри або транзакція з такою умовою не дозволена",
        "9886" => "Мерчанту не дозволено використовувати рекурентні платежі",
        "9961" => "Платіж відхилено. Зверніться в підтримку банку",
        "9989" => "Платіж відхилено. Перевірте правильність введених реквізитів картки"
    ];


    private GuzzleRequester $requester;

    /**
     * Конструктор.
     *
     * @param string $public_key
     * @param string $private_key
     * @param string|null $api_url (опціонально)
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
     * @param array $params
     * @param int $timeout
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
            'data' => $data,
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
            'url' => $this->_checkout_url,
            'data' => $this->encode_params($params),
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

    /**
     * @param string $reference_id
     * @return array
     * @link https://www.liqpay.ua/doc/api/information/status_payment?tab=1
     */
    public function getPaymentStatus(string $reference_id): array
    {
        return $this->api('request', [
            'version' => '3',
            'public_key' => $this->_public_key,
            'action' => 'status',
            'order_id' => $reference_id,
        ]);
    }

    /**
     * @param string $data Base64-кодована строка яка прийшла в POST (form-data) ключі data
     * @param string $receivedSignature Строка підпису яка прийшла в POST (form-data) ключі signature
     * @return bool
     * @link https://www.liqpay.ua/doc/api/callback
     */
    public function validateWebhook(string $data, string $receivedSignature): bool
    {
        $expectedSignature = base64_encode(sha1($this->_private_key . $data . $this->_private_key, true));
        if ($receivedSignature === $expectedSignature) {
            $paymentData = json_decode(base64_decode($data), true);
            if ($paymentData) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return PaymentLink
     * @link https://www.liqpay.ua/doc/api/internet_acquiring/checkout?tab=1
     */
    public function createPaymentLink(): PaymentLink
    {
        return new PaymentLink($this);
    }

    /**
     * @return PaymentByToken
     * @link https://www.liqpay.ua/doc/api/internet_acquiring/token?tab=1
     */
    public function createPaymentByToken(): PaymentByToken
    {
        return new PaymentByToken($this);
    }

    /**
     * @param string $reference_id
     * @param string $info
     * @return array
     * @link https://www.liqpay.ua/doc/api/information/adding_data?tab=1
     */
    public function addInfoToPayment(string $reference_id, string $info): array
    {
        return $this->api('request', [
            'version' => '3',
            'public_key' => $this->_public_key,
            'action' => 'data',
            'order_id' => $reference_id,
            'info' => $info,
        ]);
    }

    /**
     * @return array Інформація про мерчант (якщо використовуєте партнерське API)
     * @link https://www.liqpay.ua/doc/api/partnership/info_merchant?tab=1
     */
    public function getMerchantInfo(): array
    {
        return $this->api('request', [
            'version' => '3',
            'public_key' => $this->_public_key,
            'action' => 'agent_info_merchant',
            'merchant_public_key' => $this->_public_key,
        ]);
    }

    /**
     * Опис помилки українською
     * @param string $error_code
     * @return string|null NULL якщо не знайшов опису
     */
    public function tryDescribeError(string $error_code): ?string
    {
        return $this->errors[$error_code] ?? null;
    }
}
