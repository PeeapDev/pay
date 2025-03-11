<?php

namespace App\Traits\Agent;

use App\Constants\GlobalConst;
use App\Models\AgentQrCode;

trait UserPartials{
	public function createQr(){
		$user = $this->user();
	    $qrCode = $user->qrCode()->first();
        $in['agent_id'] = $user->id;
        $in['qr_code'] =  $user->registered_by == GlobalConst::EMAIL ? $user->email : $user->full_mobile;
	    if(!$qrCode){
            AgentQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}

	protected function user(){
		return userGuard()['user'];
	}




}
