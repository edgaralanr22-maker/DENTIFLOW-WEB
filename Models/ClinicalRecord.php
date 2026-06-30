<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRecord extends Model
{
    protected $fillable = ['patient_id', 'dentist_id', 'appointment_id', 'recorded_at', 'reason', 'diagnosis', 'procedure', 'notes', 'prescription', 'next_visit'];
    protected $casts = ['recorded_at' => 'datetime', 'next_visit' => 'date'];

    // Cada nota clinica pertenece a un paciente y se consulta desde el expediente.
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function dentist(): BelongsTo { return $this->belongsTo(Dentist::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
