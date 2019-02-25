<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WeixinChatModel extends Model
{
    public $table = 'p_wx_chatmsg';
    public $timestamps = true;
    public $updated_at=false;
    public $created_at=true;
}
