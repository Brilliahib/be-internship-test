<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends BaseModel
{
    use HasFactory;

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
