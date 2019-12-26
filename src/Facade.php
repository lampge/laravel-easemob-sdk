<?php
/**
 * Date: 2019/7/13 17:53
 * Copyright (c) lampge <lampge@sina.com>
 */

namespace lampge\Easemob;

use Illuminate\Support\Facades\Facade as LaravelFacade;
use lampge\Easemob\Services\ChatRoom;
use lampge\Easemob\Services\Friend;
use lampge\Easemob\Services\Group;
use lampge\Easemob\Services\Message;
use lampge\Easemob\Services\User;
use lampge\Easemob\Services\Conference;

class Facade extends LaravelFacade
{
    public static function getFacadeAccessor()
    {
        return 'easemob.user';
    }

    /**
     * @return User
     * @author lampge
     */
    public static function user()
    {
        return app('easemob.user');
    }

    /**
     * @return Friend
     * @author lampge
     */
    public static function friend()
    {
        return app('easemob.friend');
    }

    /**
     * @return Group
     * @author lampge
     */
    public static function group()
    {
        return app('easemob.group');
    }

    /**
     * @return ChatRoom
     * @author lampge
     */
    public static function chatRoom()
    {
        return app('easemob.chat-room');
    }

    /**
     * @return Conference
     * @author lampge
     */
    public static function conference()
    {
        return app('easemob.conference');
    }

    /**
     * @return Message
     * @author lampge
     */
    public static function message()
    {
        return app('easemob.message');
    }
}
