<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramEntry extends Model
{
    protected $fillable = ['patient_id', 'dentist_id', 'tooth_number', 'status', 'notes'];
    // Cada pieza registrada pertenece al expediente odontologico de un paciente.
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function dentist(): BelongsTo { return $this->belongsTo(Dentist::class); }
}
