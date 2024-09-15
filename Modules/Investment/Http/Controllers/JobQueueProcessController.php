<?php

namespace Modules\Investment\Http\Controllers;

use App\Http\Controllers\Controller;
use Artisan;

class JobQueueProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __invoke()
    {
        Artisan::call('queue:work');
    }
}
