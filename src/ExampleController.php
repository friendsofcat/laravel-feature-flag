<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class ExampleController
 * @package FriendsOfCat\LaravelFeatureFlags
 * @codeCoverageIgnore
 */
class ExampleController extends Controller
{

    public function seeTwitterField()
    {
        /**
          * Gate is based around an authenticated user
          */
        if (class_exists('\App\User')) {
            $user = factory(\App\User::class)->create();
        } else {
            $user = new User();
            $user->email = 'test@gmail.com';
        }

        \Auth::login($user);
        return view('twitter::full_page_twitter_show', compact('user'));
    }
}
