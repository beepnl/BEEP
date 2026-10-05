<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'type', 'synonyms', 'continents')]
#[Guarded('id')]
class BeeRace extends Model {}
