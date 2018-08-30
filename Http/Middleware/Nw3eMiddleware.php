<?php

namespace Newestapps\Eee\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Newestapps\Eee\Entity\Nw3eIndex;
use Newestapps\Eee\Entity\SSLCredential;
use Newestapps\Eee\Exceptions\CertNotFoundException;
use Newestapps\Eee\Exceptions\ConnectionNotAuthenticatedException;

class Nw3eMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws CertNotFoundException
     * @throws ConnectionNotAuthenticatedException
     */
    public function handle($request, Closure $next) {
        if (empty($request->user())) {

            if ($request->wantsJson() && $request->acceptsJson()) {
                return new JsonResponse([
                    'error' => 'unauthenticated_connection',
                    'message' => 'This request is not authenticated with a user signature, please provide a valid token!'
                ], 403, [
                    'X-NW3E-ERROR' => 'UNAUTHENTICATED-CONNECTION'
                ]);
            } else {
                throw new ConnectionNotAuthenticatedException();
            }

        }

        $index = \Newestapps\Eee\Facades\Nw3e::getIndex($request->user()->id);
        $cert = SSLCredential::fromIndex($index)->first();

        if (empty($cert)) {
            if ($request->wantsJson() && $request->acceptsJson()) {
                return new JsonResponse([
                    'error' => 'encryption_not_handled',
                    'message' => 'We cannot deal with encryptions without a user valid signed certification! Try to authenticate user first!'
                ], 403, [
                    'X-NW3E-ERROR' => 'INVALID-CRYPT-SOURCE'
                ]);
            } else {
                throw new CertNotFoundException();
            }
        }

        app()->instance(Nw3eIndex::class, $index);
        app()->instance(SSLCredential::class, $cert);

        return $next($request);
    }

}
