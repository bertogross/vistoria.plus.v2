<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressHtmlOutput
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof \Illuminate\Http\Response) {
            $buffer = $response->getContent();

            // Split the content by <script> and <pre> tags to avoid compressing their content
            $patterns = '/(<script[^>]*?>.*?<\/script>)|(<pre.*?>.*?<\/pre>)/is';
            $parts = preg_split($patterns, $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);

            $compressedBuffer = '';

            foreach ($parts as $part) {
                if (preg_match($patterns, $part)) {
                    // If it's a <script> or <pre> tag, append without compressing
                    $compressedBuffer .= $part;
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
                    ], $part);
                }
            }

            $response->setContent($compressedBuffer);
        }

        return $response;
    }
}
