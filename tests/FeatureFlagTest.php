<?php

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\Feature;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagHelper;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagUser;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase, FeatureFlagHelper;

    protected $user;

    public function testOn()
    {
        $this->user = factory(FeatureFlagUser::class)->create();

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => 'on'
            ]
        );

        $this->registerFeatureFlags();

        $this->assertTrue($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }

    public function testOff()
    {
        $this->user = factory(FeatureFlagUser::class)->create();

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => 'off'
            ]
        );

        $this->registerFeatureFlags();

        $this->assertFalse($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }


    public function testOnForUserEmail()
    {
        $this->user = factory(FeatureFlagUser::class)->create(['email' => 'foo2@gmail.com']);

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => [
                    'users' => [
                        'foo@gmail.com',
                        'foo2@gmail.com',
                        'foo3@gmail.com'
                    ]
                ]
            ]
        );

        $this->registerFeatureFlags();

        $this->assertTrue($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }


    public function testOffForUserEmail()
    {

        $this->user = factory(FeatureFlagUser::class)->create(['email' => 'foo4@gmail.com']);

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => [
                    'users' => [
                        'foo@gmail.com',
                        'foo2@gmail.com',
                        'foo3@gmail.com'
                    ]
                ]
            ]
        );

        $this->registerFeatureFlags();

        $this->assertFalse($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }


    public function testForNotFindFeature()
    {
        $this->user = factory(FeatureFlagUser::class)->create(['email' => 'foo4@gmail.com']);

        $this->be($this->user);

        $this->assertFalse($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }

    public function testOnForUserRole()
    {
        $this->user = factory(FeatureFlagUser::class)->create(['email' => 'foo2@gmail.com']);
        $this->user->setRawAttributes(['roles' => ['admin', 'editor']]);

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => [
                    'roles' => [
                        'admin'
                    ]
                ]
            ]
        );

        $this->registerFeatureFlags();

        $this->assertTrue($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }

    public function testOffForUserRole()
    {
        $this->user = factory(FeatureFlagUser::class)->create(['email' => 'foo2@gmail.com']);
        $this->user->setRawAttributes(['roles' => ['editor']]);

        $this->be($this->user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => [
                    'roles' => [
                        'admin', 'manager'
                    ]
                ]
            ]
        );

        $this->registerFeatureFlags();

        $this->assertFalse($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }

    public function testDatabaseIsOnlyQueriedOnceForEachKey(): void
    {
        factory(FeatureFlag::class)->create(['key' => 'Feature One']);
        factory(FeatureFlag::class)->create(['key' => 'Feature Two']);

        $feature = $this->app->make(Feature::class);

        DB::enableQueryLog();

        $feature->isEnabled('feature_one');
        $feature->isEnabled('feature_one');
        $feature->isEnabled('feature_one');
        $feature->isEnabled('feature_two');

        $queries = DB::getQueryLog();

        $this->assertCount(2, $queries);
    }
}
