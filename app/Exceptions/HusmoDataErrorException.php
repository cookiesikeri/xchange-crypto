<?php


namespace HenryEjemuta\LaravelHusmoData\Exceptions;


class HusmoDataErrorException extends \Exception
{
    /**
     * HusmoDataErrorException constructor.
     * @param string $message
     * @param $code
     */
    public function __construct(string $message, $code)
    {
        parent::__construct($message, $code);
    }
}
