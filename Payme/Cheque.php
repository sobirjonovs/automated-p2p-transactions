<?php

namespace App\Payme;

use Exception;
use Throwable;

/**
 * Class Cheque
 * @package App\Payme
 */
class Cheque extends Formatter
{
    public const ECARD_TRX_ID = "ecard_trx_id";
    public const ECARD_EMITTER = "ecard_emitter";
    public const CARD = "card";
    public const OWNER = "owner";

    /**
     * Cheque id
     * @property string $id
     */
    private string $id;
    /**
     * Cheque create time
     * @property int $createTime
     */
    private int $createTime;
    /**
     * Cheque pay time
     * @property int $payTime
     */
    private int $payTime;
    /**
     * Cheque cancel time
     * @property int $cancelTime
     */
    private int $cancelTime;
    /**
     * Cheque state
     * @property int $state
     */
    private int $state;
    /**
     * Cheque type
     * @property int $type
     */
    private int $type;
    /**
     * Cheque is external
     * @property bool $external
     */
    private bool $external;
    /**
     * Cheque operation
     * @property int $operation
     */
    private int $operation;
    /**
     * Cheque category
     * @property ?array $category
     */
    private ?array $category;
    /**
     * Cheque error
     * @property string|null $error
     */
    private ?string $error;
    /**
     * Cheque comment
     * @property string $description
     */
    private string $description;
    /**
     * Cheque detail
     * @property string|null $detail
     */
    private ?string $detail;
    /**
     * Cheque amount
     * @property int $amount
     */
    private int $amount;
    /**
     * Cheque currency
     * @property int $currency
     */
    private int $currency;
    /**
     * Cheque commission
     * @property int $commission
     */
    private int $commission;
    /**
     * Cheque account
     * @property array $account
     */
    private array $account;
    /**
     * Cheque card
     * @property array $card
     */
    private array $card;

    /**
     * Cheque constructor.
     * @param string $id
     * @param int $createTime
     * @param int $payTime
     * @param int $cancelTime
     * @param int $state
     * @param int $type
     * @param bool $external
     * @param int $operation
     * @param ?array $category
     * @param string|null $error
     * @param string $description
     * @param string|null $detail
     * @param int $amount
     * @param int $currency
     * @param int $commission
     * @param array $account
     * @param array $card
     */
    public function __construct(
        string $id,
        int $createTime,
        int $payTime,
        int $cancelTime,
        int $state,
        int $type,
        bool $external,
        int $operation,
        ?array $category,
        ?string $error,
        string $description,
        ?string $detail,
        int $amount,
        int $currency,
        int $commission,
        array $account,
        array $card
    )
    {
        $this->id = $id;
        $this->createTime = $createTime;
        $this->payTime = $payTime;
        $this->cancelTime = $cancelTime;
        $this->state = $state;
        $this->type = $type;
        $this->external = $external;
        $this->operation = $operation;
        $this->category = $category;
        $this->error = $error;
        $this->description = $description;
        $this->detail = $detail;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->commission = $commission;
        $this->account = $account;
        $this->card = $card;
    }

    /**
     * @param string $comment
     * @param int $amount
     * @return bool
     */
    public function hasPaymentWithComment(string $comment, int $amount): bool
    {
        return $this->description == $comment && $this->amount == $amount;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCreateTime(): string
    {
        return $this->formatDate($this->createTime);
    }

    /**
     * @return string
     */
    public function getPayTime(): string
    {
        return $this->formatDate($this->payTime);
    }

    /**
     * @return string
     */
    public function getCancelTime(): string
    {
        return $this->formatDate($this->cancelTime);
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->formatMoney($this->amount);
    }

    /**
     * @return string
     */
    public function getCommission(): string
    {
        return $this->formatMoney($this->commission);
    }

    /**
     * @return array
     */
    public function getAccount(): array
    {
        return $this->account;
    }

    /**
     * @return Account
     * @throws DebugException
     */
    public function getEcardTrx(): Account
    {
        try {
            $account = array_filter($this->account, function ($account) {
                return $account['name'] === self::ECARD_TRX_ID;
            });
            return new Account(...array_values(array_shift($account)));
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Account
     * @throws DebugException
     */
    public function getEcardEmitter(): Account
    {
        try {
            $account = array_filter($this->account, function ($account) {
                return $account['name'] === self::ECARD_EMITTER;
            });
            return new Account(...array_values(array_shift($account)));
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Account
     * @throws DebugException
     */
    public function getCard(): Account
    {
        try {
            $account = array_filter($this->account, function ($account) {
                return $account['name'] === self::CARD;
            });
            return new Account(...array_values(array_shift($account)));
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Account
     * @throws DebugException
     */
    public function getOwner(): Account
    {
        try {
            $account = array_filter($this->account, function ($account) {
                return $account['name'] === self::OWNER;
            });
            return new Account(...array_values(array_shift($account)));
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @return Card
     * @throws DebugException
     */
    public function getCardData(): Card
    {
        try {
            return new Card(
                $this->card['_id'], $this->card['name'], $this->card['number'],
                $this->card['expire'], true, $this->card['name'],
                0, $this->card['main'], time()
            );
        } catch (Exception | Throwable $exception) {
            throw new DebugException($exception);
        }
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int $createTime
     */
    public function setCreateTime(int $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * @param int $payTime
     */
    public function setPayTime(int $payTime): void
    {
        $this->payTime = $payTime;
    }

    /**
     * @param int $cancelTime
     */
    public function setCancelTime(int $cancelTime): void
    {
        $this->cancelTime = $cancelTime;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param int $commission
     */
    public function setCommission(int $commission): void
    {
        $this->commission = $commission;
    }

    /**
     * @param array $account
     */
    public function setAccount(array $account): void
    {
        $this->account = $account;
    }

    /**
     * @return bool
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * @param bool $external
     */
    public function setExternal(bool $external): void
    {
        $this->external = $external;
    }

    /**
     * @return int
     */
    public function getOperation(): int
    {
        return $this->operation;
    }

    /**
     * @param int $operation
     */
    public function setOperation(int $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return array
     */
    public function getCategory(): array
    {
        return $this->category;
    }

    /**
     * @param array $category
     */
    public function setCategory(array $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     */
    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @param string|null $detail
     */
    public function setDetail(?string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * @param int $currency
     */
    public function setCurrency(int $currency): void
    {
        $this->currency = $currency;
    }
}