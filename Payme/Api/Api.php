<?php

namespace App\Payme\Api;

use App\Api\Api as BaseApi;
use App\Payme\Card;
use App\Payme\Cheque;
use App\Payme\DebugException;
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
        API_CHEQUE_GET_URL = 'cheque.get',
        API_CHEQUE_VERIFY_URL = 'cheque.verify',
        API_CHEQUE_CREATE_URL = 'cheque.create',
        API_CHEQUE_PAY_URL = 'cheque.pay',
        API_GET_CARDS_URL = 'cards.get_all',
        API_GET_CARDS_P2P_INFO_URL = 'cards.get_p2p_info',
        API_P2P_CREATE_URL = 'p2p.create';

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
     * @throws DebugException
     */
    protected function post(string $url, $postfields, array $headers = []): BaseApi
    {
        try {
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
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param array $headers
     * @return Api
     * @throws DebugException
     */
    public function login(array $headers = []): Api
    {
        try {
            $this->post(self::API_LOGIN_URL, $this->credentials, $headers);
            $this->is_active_session = (bool)$this->device ?? false;
            return $this;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param string $code
     * @return Api
     * @throws DebugException
     */
    public function activate(string $code): Api
    {
        try {
            $this->post(self::API_SESSION_ACTIVATE_URL, ['code' => $code, 'device' => true], ["API-SESSION: $this->api_session"]);
            return $this;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Api
     * @throws DebugException
     */
    public function sendActivationCode(): Api
    {
        try {
            $this->post(self::API_SEND_ACTIVATION_CODE, [], ["API-SESSION: $this->api_session"]);
            return $this;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Api
     * @throws DebugException
     */
    public function registerDevice(): Api
    {
        try {
            $this->post(self::API_REGISTER_DEVICE_URL, [
                'display' => self::DEVICE_NAME,
                'type' => 2
            ], ["API-SESSION: $this->api_session"]);
            $this->device = "{$this->getContent()['result']['_id']}; {$this->getContent()['result']['key']};";
            return $this;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param array $sort
     * @return Cheque[]
     * @throws DebugException
     */
    public function getAllCheques(array $sort = []): array
    {
        try {
            $sort = $sort ?: [
                'count' => 90,
                'group' => 'time'
            ];
            $this->login(["Device: $this->device"]);
            $this->post(self::API_CHEQUE_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            return array_map(function ($cheque) {
                return new Cheque(...array_values(array_slice($cheque, 0, 17)));
            }, $this->getContent()['result']['cheques']);
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Cheque[]
     * @throws DebugException
     */
    public function getCheques(): array
    {
        try {
            return $this->cheques;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * Chainable cheques
     * @param array $sort
     * @return $this
     * @throws DebugException
     */
    public function cheques(array $sort = []): Api
    {
        try {
            $this->cheques = $this->getAllCheques($sort);
            return $this;
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function chequeCreate(array $sort = []): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_CHEQUE_CREATE_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            if (!isset($this->getContent()['error'])){
                return ['cheque_id' => $this->getContent()['result']['cheque']['_id']];
            }
            return ['error_message' => $this->getContent()['error']['message']];
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function p2pCreate(array $sort = []): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_P2P_CREATE_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);
            if (!isset($this->getContent()['error'])){
                return ['cheque_id' => $this->getContent()['result']['cheque']['_id']];
            }else{
                return ['error_message' => $this->getContent()['error']['message']];
            }

        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function chequeVerify(array $sort = []): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_CHEQUE_VERIFY_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            if (isset($this->getContent()['result'])){
                return ['method' => $this->getContent()['result']['method']];
            }
            return ['message' => false];

        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function chequePay(array $sort = []): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_CHEQUE_PAY_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            if (!isset($this->getContent()['error'])){
                return ['cheque_id' => $this->getContent()['result']['cheque']['_id']];
            }else{
                return ['error_message' => $this->getContent()['error']['message']];
            }
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function getCardInfo(array $sort = []): array
    {
        try {

            $this->login(["Device: $this->device"]);
            $this->post(self::API_GET_CARDS_P2P_INFO_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            return $this->getContent();
//            return array_map(function ($cheque) {
//                return new Cheque(...array_values(array_slice($cheque, 0, 17)));
//            }, $this->getContent()['result']['cheques']);
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    public function getCheque(array $sort = []): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_CHEQUE_GET_URL, $sort, ["API-SESSION: $this->api_session", "Device: $this->device"]);

            return $this->getContent();
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Card[]
     * @throws DebugException
     */
    public function getMyCards(): array
    {
        try {
            $this->login(["Device: $this->device"]);
            $this->post(self::API_GET_CARDS_URL, [], ["API-SESSION: $this->api_session", "Device: $this->device"]);

            return array_map(function ($card) {
                return new Card(
                    $card['_id'], $card['name'], $card['number'],
                    $card['expire'], $card['active'], $card['owner'],
                    $card['balance'], $card['main'], $card['date']
                );
            }, $this->getContent()['result']['cards']);
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param string $card_id
     * @param array $sort
     * @return $this
     * @throws DebugException
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
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param string $comment
     * @param int $amount
     * @return array
     * @throws DebugException
     */
    public function findByComment(string $comment, int $amount): array
    {
        try {
            return array_filter($this->cheques, function ($cheque) use ($amount, $comment) {
                return $cheque->hasPaymentWithComment($comment, $amount);
            });
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @throws Exception
     */
    public function date(): DateTime
    {
        try {
            return new DateTime('now', new DateTimeZone(self::API_TIMEZONE));
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
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
     * @throws DebugException
     */
    public function getApiSession(BaseApi $api)
    {
        try {
            return $this->getHeaderKey('api-session');
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param string $key
     * @return mixed|string
     * @throws DebugException
     */

    public function getHeaderKey(string $key)
    {
        try {
            $result = array_change_key_case($this->getHeader($this->getContent(true)));
            if (isset($result[$key])){
                return $result[$key];
            }
            return '';
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param $property
     * @param $value
     * @throws DebugException
     */
    public function __set($property, $value)
    {
        try {
            $this->session->store($property, $value);
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param $property
     * @return mixed|null
     * @throws DebugException
     */
    public function __get($property)
    {
        try {
            return $this->session->get($property);
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }
}
