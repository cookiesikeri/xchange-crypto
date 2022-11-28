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
    const UPDATEPROFILE = 'Update Profile';
    const UPDATEPIN = 'Update User Pin';
    const CREATE_DOGECOIN_ADDRESS = 'Create Dogecoin Address';
    const CREATE_DOGECOIN_WALLET = 'Create Dogecoin Wallet';
    const DogeGenerateAddressPrivateKey = 'Create Dogecoin PrivateKey';
    const SEND_DOGECOIN = 'Send Dogecoin';
    const BROADCAST_DOGECOIN = 'Broadcast Dogecoin';
    const CREATE_BITCOIN_ADDRESS = 'Create Bitcoin Address';
    const CREATE_BITCOIN_WALLET = 'Create Bitcoin Wallet';
    const BitcoinGenerateAddressPrivateKey = 'Create Bitcoin PrivateKey';
    const SEND_BITCOIN = 'Send Bitcoin';
    const BROADCAST_BITCOIN = 'Broadcast Bitcoin';
    const CREATE_ETH_ADDRESS = 'Create ETH Address';
    const CREATE_ETH_WALLET = 'Create ETH Wallet';
    const ETHGenerateAddressPrivateKey = 'Create ETH PrivateKey';
    const SEND_ETH = 'Send ETH';
    const BROADCAST_ETH = 'Broadcast ETH';
    const CREATE_LITECOIN_WALLET = 'Create Litecoin Wallet';
    const CREATE_LITECOIN_ADDRESS = 'Create Litecoin Address';
    const LitecoinAddressPrivateKey = 'Create Litecoin PrivateKey';
    const SEND_LITECOIN = 'Send Litecoin';
    const BROADCAST_LITECOIN = 'Litecoin Bitcoin';
    const CREATE_POLYGON_WALLET = 'Create Polygon Wallet';
    const CREATE_POLYGON_ADDRESS = 'Create Polygon Address';
    const SEND_POLYGON = 'Send Polygon';
    const BROADCAST_POLYGON = 'Polygon Bitcoin';
    const POLYGONAddressPrivateKey = 'Create Polygon PrivateKey';

}
