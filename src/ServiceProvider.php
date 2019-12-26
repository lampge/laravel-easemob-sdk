<?php

namespace lampge\Easemob;

/**
 * Date: 2019/7/13 17:07
 * Copyright (c) lampge <lampge@sina.com>
 */
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use lampge\Easemob\Services\ChatRoom;
use lampge\Easemob\Services\Conference;
use lampge\Easemob\Services\Friend;
use lampge\Easemob\Services\Group;
use lampge\Easemob\Services\Message;
use lampge\Easemob\Services\User;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__ . '/../config/easemob.php') => config_path('easemob.php')
        ], 'config');
    }

    public function register()
    {
        $apps = [
            'user'       => User::class,
            'friend'     => Friend::class,
            'chat-room'  => ChatRoom::class,
            'group'      => Group::class,
            'conference' => Conference::class,
            'message'    => Message::class
        ];

        foreach ($apps as $name => $class) {
            $this->app->singleton("easemob.{$name}", function () use ($class) {
                return new $class(config('easemob'));
            });
        }

        $this->app->singleton('easemob.http', function () {
            $baseHost = config('easemob.domain_name');
            $client   = new Client([
                'base_uri' => $baseHost,
                'headers'  => [
                    'accept' => 'application/json'
                ]
            ]);
            return $client;
        });
    }
}
