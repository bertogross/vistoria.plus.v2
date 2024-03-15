<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressHtmlOutput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof \Illuminate\Http\Response) {
            $buffer = $response->getContent();

            // Split the content by script tags to avoid compressing JavaScript
            $scripts = preg_split('/(<script[^>]*?>.*?<\/script>)/is', $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);
            $compressedBuffer = '';

            foreach ($scripts as $script) {
                if (preg_match('/<script[^>]*?>.*?<\/script>/is', $script)) {
                    // If it's a script tag, append without compressing
                    $compressedBuffer .= $script;
                } else {
                    // Otherwise, apply compression
                    $compressedBuffer .= preg_replace([
                        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
                        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
                        '/(\s)+/s',         // shorten multiple whitespace sequences
                        '/<!--(.|\s)*?-->/' // Remove HTML comments
                    ], [
                        '>',
                        '<',
                        '\\1',
                        ''
                    ], $script);
                }
            }

            $response->setContent($compressedBuffer);
        }

        return $response;
    }
}
