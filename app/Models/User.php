<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\UsesUuid;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\URL;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class User extends Authenticatable implements JWTSubject, Searchable
{
    use Notifiable, UsesUuid;

//    protected $appends = ['image_url'];

    protected $guard_name = 'api';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

        /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

        /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','transaction_pin',
    ];

    public function getSearchResult(): SearchResult
    {
        return new \Spatie\Searchable\SearchResult(
            $this,
            $this->name
        );
    }

    public function customer_verification()
    {
        return $this->hasOne('App\Models\CustomerValidation');
    }

    public function wallet(){

        return $this->hasOne('App\Models\Wallet');
    }
    public function paymentcards() {
        return $this->hasMany(PaymentCard::class);
    }
    public function litecoin_transactions() {
        return $this->hasMany(LitecoinTransaction::class);
    }
    public function gift_cards() {
        return $this->hasMany(GiftCard::class);
    }
    public function virtual_card_requests() {
        return $this->hasMany(VirtualCardRequest::class);
    }
    public function bitcoin_transactions() {
        return $this->hasMany(BitcoinTransaction::class);
    }
    public function bitcoin_wallet_pass() {
        return $this->hasOne(BitcoinWalletPass::class);
    }
    public function gift_card_customer() {
        return $this->hasOne(GiftCardCustomer::class);
    }
    public function bitcon_wallet() {
        return $this->hasOne(BitconWallet::class);
    }

    public function secret_q_and_a(){
        return $this->hasOne(UserSecretQAndA::class);
    }
    public function virtual_card(){
        return $this->hasOne(VirtualCard::class);
    }
    public function crypto_wallet(){
        return $this->hasOne(CryptoWallet::class);
    }
    public function etherum_wallet(){
        return $this->hasOne(EtherumWallet::class);
    }
    public function etherum_wallet_adress(){
        return $this->hasOne(EtherumWalletAdress::class);
    }
    public function litecoin_wallet_address(){
        return $this->hasOne(LitecoinWalletAddress::class);
    }
    public function bitcoin_wallet(){
        return $this->hasOne(BitconWallet::class);
    }
    public function bitcoin_private_key(){
        return $this->hasOne(BitconPrivateKey::class);
    }
    public function doge_coin_wallet(){
        return $this->hasOne(DogeCoinWallet::class);
    }
    public function doge_coin_wallet_address(){
        return $this->hasOne(DogeCoinWalletAddress::class);
    }
    public function etherum_private_key(){
        return $this->hasOne(EtherumPrivateKey::class);
    }

    public function  litecoin_private_key(){
        return $this->hasOne(LitecoinPrivateKey::class);
    }
    public function dogecoin_private_key(){
        return $this->hasOne(DogecoinPrivateKey::class);
    }
    public function litecoin_wallet(){
        return $this->hasOne(LitecoinWallet::class);
    }
    public function location(){
        return $this->hasOne(LitecoinWallet::class);
    }

    public function location_respons(){
        return $this->hasOne(LocationResponse::class);
    }
    public function _polygon_private_key(){
        return $this->hasOne(PolygonPrivateKey::class);
    }
    public function polygon_wallet_address(){
        return $this->hasOne(PolygonWalletAddress::class);
    }
    public function binance_wallet(){
        return $this->hasOne(BinanceWallet::class);
    }
    public function virtual_accounts(){
        return $this->hasOne(VirtualAccount::class);
    }
    public function dodgecoin_transactions(){
        return $this->hasMany(DogecoinTransaction::class);
    }
    public function binance_transactions(){
        return $this->hasMany(BinanceTransaction::class);
    }
    public function beneficiaries(){
        return $this->hasMany(Beneficiaries::class);
    }
    public function eth_transactions(){
        return $this->hasMany(EthTransaction::class);
    }

    public function polygon_transactions(){
        return $this->hasMany(PolygonTransaction::class);
    }

    public function useractivities(){
        return $this->hasMany(UserActivity::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accounttype() {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }


}
