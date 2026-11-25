<?php

namespace App\Models\Concerns;

use App\Models\Ganaderia\Documento;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TieneDocumentos
{
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable')->latest();
    }
}
