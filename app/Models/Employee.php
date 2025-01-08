<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends BaseModel
{
    use HasFactory;
    
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
