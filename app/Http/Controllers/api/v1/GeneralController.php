<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\HelpTopic;
use App\Resources\FAQ;
use function App\CPU\translate;

class GeneralController extends Controller
{
    public function faq(){

        return Helpers::sendSuccess(translate("Data Got!"),FAQ::collection(HelpTopic::orderBy('ranking')->get()));
    }
}
