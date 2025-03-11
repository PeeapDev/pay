<?php

namespace App\Traits\User;

use App\Constants\GlobalConst;
use App\Models\UserQrCode;
trait UserPartials{
	public function createQr(){
		$user = $this->user();
	    $qrCode = $user->qrCode()->first();
        $in['user_id'] = $user->id;
        $in['qr_code'] =  $user->registered_by == GlobalConst::EMAIL ? $user->email : $user->full_mobile;
	    if(!$qrCode){
            UserQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}

	protected function user(){
		return userGuard()['user'];
	}




}
