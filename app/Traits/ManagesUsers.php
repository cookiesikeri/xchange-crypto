<?php


namespace App\Traits;


use App\Enums\AccountRequestType;
use App\Enums\AccountTypes;
use App\Enums\WithdrawalLimit;
use App\Mail\PremiumAccountEmail;
use App\Models\AccountRequest;
use App\Models\CustomerValidation;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stevebauman\Location\Facades\Location;

trait ManagesUsers
{

    public function ownsRecord($id)
    {
        return auth()->id() === $id;
    }

    public function saveUserActivity($activity, $type, $user_id)
    {
        $ip = request()->ip();
        if ($ip == '127.0.0.1') {
            $ip = '';
        }
        $data = Location::get($ip);
        return UserActivity::on('mysql::write')->create([
            'user_id' => $user_id,
            'activity' => $activity,
            'type' => $type,
            'city' => $data->cityName,
            'region' => $data->regionName,
            'country' => $data->countryName,
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
        ]);
    }




    public function isAdminShutdownStatus()
    {
        if (auth()->check()) {
            if(auth()->user()->shutdown_level === 2) {
                auth()->logout();
                return true;
            }else {
                return false;
            }
        }
        return false;
    }
}
