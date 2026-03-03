<?php

namespace App\Traits;

use App\Scopes\ProgramScope;

trait FilteredByProgram
{
    protected static function bootFilteredByProgram()
    {
        static::addGlobalScope(new ProgramScope);
    }
}
