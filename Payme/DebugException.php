<?php

namespace App\Payme;

use Exception;
use Throwable;

/**
 * Class DebugException
 * @package App\Payme
 */
class DebugException extends Exception
{
    /**
     * DebugException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getExceptionMessage(): string
    {
        $message = "<b>Xatolik matni:</b> ";
        $message .= $this->getMessage();
        $message .= "<br><b>Xatolik kodi:</b>: ";
        $message .= $this->getCode();
        $message .= "<br><b>Xatolik kelib chiqqan qator:</b>: ";
        $message .= $this->getLine();
        $message .= "<br><b>Xatolik kelib chiqqan fayl:</b>: ";
        $message .= $this->getFile();
        $message .= "<br><b>Xatolik izi:</b>: ";
        $message .= $this->getTraceAsString();
        return $message;
    }
}