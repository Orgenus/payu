<?php
namespace Orgenus\Payu;

use Illuminate\Support\Facades\Facade;



class PayuFacade extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'payu';
    }
}
