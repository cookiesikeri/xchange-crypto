<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ActivityType extends Enum
{
    const WALLET_TRANSFER =   'Wallet Transfer';
    const LOGIN =   'Auth-login';
    const REGISTER =   'Auth-register';
    const AIRTIME = 'Airtime';
    const BTC = 'Buy Bitcoin';
    const FUND_WALLET = 'Fund Wallet';
    const CARD_TRANSFER = 'Card Transfer';
    const DATA = 'Data';
    const UPDATEPROFILE = 'Update-Profile';
    const UPDATEPIN = 'Update-User-Pin';
}
