<?php

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('operational_controls');
echo "Columns in operational_controls: " . implode(', ', $columns) . "\n";
