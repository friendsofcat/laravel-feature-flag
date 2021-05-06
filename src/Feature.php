<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsEnabler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Feature
{
    const ON = 'on';
    const OFF = 'off';
    const FEATURE_FLAG_CACHE_KEY = 'feature_flags:all';

    /**
     * @var array
     */
    private $instance;

    public function __construct()
    {
        $this->instance = Cache::get(Feature::FEATURE_FLAG_CACHE_KEY, []);
    }

    /**
     * Check if a feature flag exists.
     *
     * @param string $featureKey
     * @param mixed $variant (optional)
     * @return bool
     */
    public function exists($featureKey)
    {
        return isset($this->instance[$featureKey]);
    }

    /**
     * Check if a feature flag is enabled.
     *
     * @param string $featureKey
     * @param mixed $variant (optional)
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return bool
     */
    public function isEnabled($featureKey, $variant = null, $user = null)
    {
        if (! $variant) {
            $variant = $this->getConfig($featureKey);
        }

        if ($variant != self::ON and $variant != self::OFF) {
            return $this->isUserEnabled($variant, $user) || $this->isRoleEnabled($variant, $user);
        }

        return $variant == self::ON;
    }

    /**
     * @param string $featureKey
     * @return mixed
     */
    public function getConfig($featureKey)
    {
        if (array_key_exists($featureKey, $this->instance)) {
            return $this->instance[$featureKey];
        }

        $featureFlag = once(function () use ($featureKey) {
            return FeatureFlag::where('key', $featureKey)->first();
        });

        if ($featureFlag === null) {
            return self::OFF;
        }

        return $featureFlag->variants;
    }

    /**
     * @param $feature_variant
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return bool
     */
    protected function isUserEnabled($feature_variant, $user = null)
    {
        if ($user_email = $this->getUserEmail($user)) {
            if (empty($feature_variant['users'])) {
                return false;
            }

            return in_array(
                strtolower($user_email),
                array_map('strtolower', $feature_variant['users']),
                true
            );
        }

        return false;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return string
     */
    public function getUserEmail($user)
    {
        if ($user && $user->email) {
            return $user->email;
        }

        if (Auth::guest()) {
            return false;
        }

        return Auth::user()->email;
    }

    private function getUserRoles($user)
    {
        return ($user && $user->roles) ? $user->roles : false;
    }

    public function isRoleEnabled($feature_variant, $user = null)
    {
        $fieldName = 'roles';

        if (empty($feature_variant[$fieldName])) {
            return false;
        }

        $user = $user ?? Auth::user();
        if ($user_roles =
            ($user instanceof FeatureFlagsEnabler)
                ? $user->getFieldValueForFeatureFlags($fieldName)
                : $this->getUserRoles($user)
        ) {
            $filtered = Arr::where(
                array_map('strtolower', $user_roles),
                function ($value, $key) use ($feature_variant, $fieldName) {
                    return in_array($value, $feature_variant[$fieldName], true);
                }
            );

            return ! empty($filtered);
        }

        return false;
    }
}
