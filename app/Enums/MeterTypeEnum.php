<?php

namespace App\Enums;


use HenryEjemuta\LaravelHusmoData\Exceptions\HusmoDataErrorException;

class MeterTypeEnum
{
    private static $cache = [];
    private static $telcoms = [
        'prepaid' => 1,
        'postpaid' => 2,
    ];

    private $code, $name;

    private function __construct(int $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getName(): string
    {
        return ucfirst($this->name);
    }

    public function toArray(): array
    {
        return ['code' => $this->getCode(), 'name' => $this->getName()];
    }

    /**
     * @param $name
     * @return MeterTypeEnum|null
     * @throws HusmoDataErrorException
     */
    public static function getMeterType($name): ?MeterTypeEnum
    {
        $cleanedName = strtolower(trim($name));
        if (!key_exists($cleanedName, self::$telcoms))
            throw new HusmoDataErrorException("No Meter Type available with the name '$name'", 999);
        if (!key_exists($cleanedName, self::$cache)) {
            self::$cache[$cleanedName] = new MeterTypeEnum(self::$telcoms[$cleanedName], $cleanedName);
        }
        return self::$cache[$cleanedName];
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
