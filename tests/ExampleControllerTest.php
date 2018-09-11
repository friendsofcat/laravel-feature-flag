<?php

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\AddExampleFeaturesTableSeeder;
use FriendsOfCat\LaravelFeatureFlags\ExampleController;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagSettingsController;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testShouldSeeExample()
    {
        $example_controller = \App::make(ExampleController::class);
        $data = $example_controller->seeTwitterField()->getData();
        $user = $data['user'];
        $this->assertEquals($user->email, 'test@gmail.com');
    }
}
