<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class MajaController extends Controller
{
    public $h2h = 'DowQGLgsyokvqMsOLbkRqNz4qq6lySnd';

    public function inquiry()
    {
        echo $this->h2h;
        echo 'inquiry';
    }

    public function payment()
    {
        echo 'payment';
    }

    public function reversal()
    {
        echo 'reversal';
    }
}
