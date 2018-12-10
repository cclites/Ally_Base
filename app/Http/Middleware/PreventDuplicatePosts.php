<?php

namespace App\Http\Middleware;

use App\Responses\ErrorResponse;
use Carbon\Carbon;
use Closure;
use Cache;

class PreventDuplicatePosts
{

    protected $handle;

    /**
     * Array of URI paths to exclude from the middleware
     *
     * @var array
     */
    protected $excludedPaths = [
        'login',
        'logout',
    ];

    /**
     * Array of route names to exclude from the middleware
     *
     * @var array
     */
    protected $excludedRoutes = [
        'business.care-match.client-matches',
    ];

    /**
     * The number of seconds to prevent duplicate posts for
     * @var int
     */
    protected $timeout = 8;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $method = $request->getMethod();
        $path = $request->path();

        if ($method === 'POST' && !$this->isExcluded()) {
            // Generate a hash
            $hash = md5($path . serialize($request->input()));

            // Acquire a lock to prevent race conditions (this must always be released before returning)
            $this->acquireLock();

            // Compare against previous requests
            $hashes = Cache::get('post_request_chain', []);

            if (isset($hashes[$hash]) && time() <= $hashes[$hash]) {
                $this->releaseLock();
                return (new ErrorResponse(409, 'You submitted the same form twice. We\'ve prevented a duplicate entry.'))->toResponse($request);
            }

            // Add key to cache  ($hash => $expiration)
            $expiration = time() + $this->timeout;
            $hashes[$hash] = $expiration;
            Cache::put('post_request_chain', $hashes, Carbon::now()->addSeconds($this->timeout * 2));

            // Release lock
            $this->releaseLock();
        }

        return $next($request);
    }

    function openLockFile()
    {
        if (!$this->handle) {
            $this->handle = fopen(sys_get_temp_dir() . '/postrequestchain', 'a+');
        }
        return $this->handle;
    }

    function acquireLock() {
        flock($this->openLockFile(), LOCK_EX);
    }

    function releaseLock() {
        flock($this->openLockFile(), LOCK_UN);
    }

    function isExcluded() {
        if (! config('ally.prevent_dupe_posts', true)) {
            return true;
        }

        return in_array(\Request::path(), $this->excludedPaths)
        || in_array(\Route::currentRouteName(), $this->excludedRoutes);
    }
}
