<?php

namespace Wotz\FilamentBrigadaCms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Oddvalue\LaravelDrafts\Facades\LaravelDrafts;
use Symfony\Component\HttpFoundation\Response;

class DisableDraftPreview
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        LaravelDrafts::disablePreviewMode();

        return $next($request);
    }
}
