<?php

namespace DigiFlazz;

use Illuminate\Support\Facades\Facade;

class DigiFlazzFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
	    return 'DigiFlazz';
	}
}