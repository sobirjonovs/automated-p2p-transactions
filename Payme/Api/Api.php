<?php

namespace App\Payme\Api;

use App\Api\Api as BaseApi;
use App\Session\Session;
use DateTime;
use DateTimeZone;
use Exception;
use Throwable;

/**
 * Class PaymeApi
 * @property mixed|null api_session
 * @property mixed|null device
 */
class Api extends BaseApi
{
    /**
     * cURL options
     */
    public const
        USER_AGENT = "Payme API",
        API_TIMEZONE = "Asia/Tashkent",
        ENDPOINT_URL = "https://payme.uz/api/",
        API_LOGIN_URL = "users.log_in",
        API_SESSION_ACTIVATE_URL = "sessions.activate",
        API_SEND_ACTIVATION_CODE = "sessions.get_activation_code",
        API_REGISTER_DEVICE_URL = "devices.register",
        API_CHEQUE_URL = 'cheque.get_all',
        API_GET_CARDS_URL = 'cards.get_all';

    /**
     * Config
     * */
    public const
        DEVICE_NAME = "Payme API";

    /**
     * Error codes
     * */
    public const
        ERROR_SESSION_EXPIRED = -32504;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var bool|mixed|null
     */
    public bool $is_active_session = false;

    /**
     * @var false|int|mixed|null
     */
    public $cheques;

    /**
     * Bu massivda ikkita element bo'ladi:
     * 1. login
     * 2. password
     * Bularni $this->setCredentials(array $credentials) metodi orqali
     * to'ldirish mumkin.
     * 3. Login parollarni yozish
     * <b>Namuna:</b> $this->setCredentials(['login' => 'payme nomer', 'password' => 'payme parol']);
     * @var array|mixed|null
     */
    public $credentials = [];

    /**
     * Api constructor.
     */
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param string $url
     * @param array|string $postfields
     * @param array $headers
     * @return BaseApi
     */
    protected function post(string $url, $postfields, array $headers = []): BaseApi
    {
        $headers = array_merge([
            'Content-Type: text/plain',
            'Accept: */*',
            'Connection: keep-alive'
        ], $headers);
        $params['method'] = $url;
        $params['params'] = $postfields;
        $response = parent::post(self::ENDPOINT_URL . $url, json_encode($params), $headers);
        $this->api_session = $this->getApiSession($response);
        return $response;
    }

    /**
     * @param array $headers
     * @return Api
     */
    public function login(array $headers = []): Api
    {
        $this->post(self::API_LOGIN_URL, $this->credentials, $headers);
        $this->is_active_session = (bool)$this->device ?? false;
        return $this;
    }

    /**
     * @param string $code
     * @return Api
     */
    public function activate(string $code): Api
    {
        $this->post(self::API_SESSION_ACTIVATE_URL, ['code' => $code, 'device' => true], ["API-SESSION: $this->api_session"]);
        return $this;
    }

    /**
     * @return Api
     */
    public function sendActivationCode(): Api
    {
        $this->post(self::API_SEND_ACTIVATION_CODE, [], ["API-SESSION: $this->api_session"]);
        return $this;
    }

    /**
     * @return Api
     */
    public function registerDevice(): Api
    {
        $this->post(self::API_REGISTER_DEVICE_URL, [
            'display' => self::DEVICE_NAME,
            'type' => 2
        ], ["API-SESSION: $this->api_session"]);
        $this->device = "{$this->getContent()['result']['_id']}; {$this->getContent()['result']['key']};";
        return $this;
    }

    /**
     * @param array $sort
     * @param bool $chainable
     * @return false|int|mixed|Api
     */
    public function getAllCheques(array $sort = [])
    {
        $sort = $sort ?: [
            'count' => 20,
            'group' => 'time'
        ];
        $this->login(["Device: $this->device"]);
        $this->post(self::API_CHEQUE_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);
        return $this->getContent()['result']['cheques'];
    }

    /**
     * @return false|int|mixed|null
     */
    public function getCheques()
    {
        return $this->cheques;
    }

    /**
     * Chainable cheques
     * @param array $sort
     * @return $this
     */
    public function cheques(array $sort = []): Api
    {
        $this->cheques = $this->getAllCheques($sort);
        return $this;
    }

    /**
     * @return false|int|mixed
     */
    public function getMyCards()
    {
        $this->login(["Device: $this->device"]);
        $this->post(self::API_GET_CARDS_URL, [], ["API-SESSION: $this->api_session", "Device: $this->device"]);
        return $this->getContent()['result']['cards'];
    }

    /**
     * @param string $card_id
     * @param array $sort
     * @return $this
     */
    public function selectCard(string $card_id, array $sort = []): Api
    {
        try {
            $date = $this->date();
            $this->cheques = $this->getAllCheques([
                'card' => $card_id,
                'count' => $sort['count'] ?? 20,
                'from' => $sort['from'] ?? null,
                'group' => $sort['group'] ?? 'time',
                'offset' => $sort['offset'] ?? 0,
                'to' => $sort['to'] ?? [
                        'day' => intval($date->format('d')),
                        'month' => intval($date->format('m')) - 1,
                        'year' => intval($date->format('Y'))
                    ]
            ]);
            return $this;
        } catch (Exception $exception) {
            echo $this->getExceptionMessage($exception);
            die();
        } catch (Throwable $throwable) {
            echo $this->getExceptionMessage($throwable);
            die();
        }
    }

    /**
     * @param $exception
     * @return string
     */
    private function getExceptionMessage($exception): string
    {
        $message = "<b>Xatolik matni:</b> ";
        $message .= $exception->getMessage();
        $message .= "<br><b>Xatolik kodi:</b>: ";
        $message .= $exception->getCode();
        $message .= "<br><b>Xatolik kelib chiqqan qator:</b>: ";
        $message .= $exception->getLine();
        $message .= "<br><b>Xatolik kelib chiqqan fayl:</b>: ";
        $message .= $exception->getFile();
        $message .= "<br><b>Xatolik izi:</b>: ";
        $message .= $exception->getTraceAsString();
        return $message;
    }

    /**
     * @param string $comment
     * @param int $amount
     * @return false|int|mixed|null
     */
    public function findByComment(string $comment, int $amount)
    {
        $filter = array_filter($this->cheques, function ($cheque) use ($amount, $comment) {
            return $cheque['description'] == $comment && $cheque['amount'] == $amount;
        });

        return !empty($filter) ? $filter : false;
    }

    /**
     * @throws Exception
     */
    public function date(): DateTime
    {
        return new DateTime('now', new DateTimeZone(self::API_TIMEZONE));
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param string $device
     * @return Api
     */
    public function setDevice(string $device): Api
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @param array $credentials
     * @return $this
     */
    public function setCredentials(array $credentials): Api
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * @param BaseApi $api
     * @return mixed
     */
    public function getApiSession(BaseApi $api)
    {
        return $api->getHeader($api->getContent(true))['api-session'];
    }

    /**
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->session->store($property, $value);
    }

    /**
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        return $this->session->get($property);
    }
}