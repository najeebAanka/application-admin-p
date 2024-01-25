<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Attribute;
use Illuminate\Http\Request;
use function App\CPU\translate;

class AttributeController extends Controller
{
    public function get_attributes()
    {
        $attributes = Attribute::all();
        return Helpers::sendSuccess(translate('Data Got!'),$attributes);
    }
}
