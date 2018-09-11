<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 8/22/17
 * Time: 8:56 PM
 */

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsForJavascript;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\App;

class FeatureFlagsForJavascriptTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetNoResults()
    {
        $fjs = new FeatureFlagsForJavascript();
        $result = $fjs->get();
        $this->assertEmpty($result);
    }


    public function testGetWithResults()
    {

        factory(\FriendsOfCat\LaravelFeatureFlags\FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => '{ "users": [ "foo@gmail.com", "foo2@gmail.com", "foo3@gmail.com" ] }'
            ]
        );

        $fjs = new FeatureFlagsForJavascript();
        $result = $fjs->get();
        $this->assertNotEmpty($result);
    }
}
