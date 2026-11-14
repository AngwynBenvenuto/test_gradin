<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e){
        if($request->is('admin/*')) {
            if($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                if (view()->exists($this->getViewName($status))) {
                    return response()->view($this->getViewName($status), ['exception' => $e], $status, $e->getHeaders());
                } 
            }
        }
        return parent::render($request, $e);
    }
    protected function getViewName($status)
    {
        if (request()->is('admin/*')) {
            return "admin.errors.{$status}";
        }
        return "errors.{$status}";
    }
}
