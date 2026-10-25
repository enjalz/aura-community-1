<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Induk semua model selain Pengguna.
// Tabel di skripsi tidak memakai kolom created_at dan updated_at.
abstract class ModelAura extends Model
{
    public $timestamps = false;
}