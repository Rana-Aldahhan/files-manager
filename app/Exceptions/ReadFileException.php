<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;

class ReadFileException extends Exception
{
    protected $code = 403;
    /**
     * report the exception
     * 
     * @return void
     */
    public function report()
    {
        //
    }


    /**
     * render the exception as an HTTP response
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function render($request)
    {
        return $this;
    }
}