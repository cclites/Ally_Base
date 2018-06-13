<?php

namespace App\Http\Middleware;

use App\Responses\ErrorResponse;
use Closure;
use Cache;

class PreventDuplicatePosts
{

    protected $handle;

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
        $excludedPaths = ['login', 'logout'];

        if ($method === 'POST' && !in_array($path, $excludedPaths)) {
            // Generate a hash
            $hash = md5($path . serialize($request->input()));

            // Acquire a lock to prevent race conditions (this must always be released before returning)
            $this->acquireLock();

            // Compare against previous requests
            $hashes = Cache::get('post_request_chain', []);

            if (in_array($hash, $hashes)) {
                $this->releaseLock();
                return (new ErrorResponse(409, 'You submitted the same form twice. We\'ve prevented a duplicate entry.'))->toResponse($request);
            }

            $hashes[] = $hash;
            Cache::put('post_request_chain', $hashes, 1);
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
}
