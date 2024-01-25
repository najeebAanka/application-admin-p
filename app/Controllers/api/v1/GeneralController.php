<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\HelpTopic;

class GeneralController extends Controller
{
    public function faq(){

        return Helpers::sendSuccess("Data Got!",HelpTopic::orderBy('ranking')->get());
    }
}
