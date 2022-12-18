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
    const BTC = 'Buy Bitcoin';
    const FUND_WALLET = 'Fund Wallet';
    const CARD_TRANSFER = 'Card Transfer';
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
    const BROADCAST_POLYGON = 'Broadcast Polygon';
    const POLYGONAddressPrivateKey = 'Create Polygon PrivateKey';

    const CREATE_BINANCE_WALLET = 'Create Binance Wallet';
    const CREATE_BINANCE_ADDRESS = 'Create Binance Address';
    const SEND_BINANCE = 'Send Binance';
    const BROADCAST_BINANCE = 'Broadcast Binance ';
    const BINANCEAddressPrivateKey = 'Create Binance PrivateKey';

    const CREATE_VIRTUAL_ACCOUNT = 'Create Virtual Account';
    const AIRTIME = 'Airtime puchase';
    const DATA = 'Data puchase';
    const ELECTRICITY = 'Power Unit puchase';
    const TV = 'TV decorder puchase';
    const VIRTUALACCOUNT = 'Create Virtual Account';
    const UPDATE_VIRTUALACCOUNT = 'Update Virtual Account';
    const VIRTUALCARD = 'Create Virtual Card';
    const CARDREQUEST = 'card request';
    const UPDATECARD = 'update card';

    const CREATEGIFTCARD_CUSTOMER = 'create giftcard customer';
    const UPDATEGIFTCARD_CUSTOMER = 'update giftcard customer details';
    const CREATEGIFTCARD = 'giftcard created';
    const LINKGIFTCARD = 'link customer to giftcard';
    const creategiftcardactivity = 'gift card activity changed';

}
