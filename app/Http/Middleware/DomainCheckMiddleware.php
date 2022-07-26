<?php

namespace App\Http\Middleware;

use App\Page;
use Closure;

class DomainCheckMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    // Dev enviroment only
    if ($request->ip() == '127.0.0.1') {
      $originUrl = "http://localhost";
    } else {
      $originUrl = $request->headers->get('origin');
    }
    // $originUrl = $request->headers->get('origin');
    $requestHost = parse_url($originUrl, PHP_URL_HOST);
    $page = Page::where('id', $request->pageId)->get('url')->first();
    $pageUrl = parse_url($page->url, PHP_URL_HOST);
    if ($requestHost == $pageUrl) {
      return $next($request);
    }
    return response()->json(["error" => "Site url not correct!"], 401);
  }
}
