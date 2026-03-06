<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait ProxiesActivityFields
{
    // Define $proxiedFields in the model, e.g.:
    // protected array $proxiedFields = ['location_id', 'responsable_delegado_id', 'apoyo'];

    // Temporary storage for proxied values during save
    protected array $tempProxies = [];

    public static function bootProxiesActivityFields()
    {
        static::saving(function (Model $model) {
            $proxied = $model->getProxiedFields();
            $attributes = $model->getAttributes();
            
            foreach ($proxied as $field) {
                // Si el campo existe en los atributos (fue seteado masivamente o individualmente)
                if (array_key_exists($field, $attributes)) {
                    // Guardamos el valor temporalmente
                    $model->tempProxies[$field] = $attributes[$field];
                    
                    // Lo eliminamos de los atributos del modelo para que no intente guardarlo en su tabla
                    unset($model[$field]);
                }
            }
        });

        static::saved(function (Model $model) {
            // Si hay datos proxeados y existe la relación con activity
            if (!empty($model->tempProxies) && $model->activity) {
                $model->activity->update($model->tempProxies);
            }
            
            // Limpiamos
            $model->tempProxies = [];
        });
    }


    public function getProxiedFields(): array
    {
        return property_exists($this, 'proxiedFields') ? $this->proxiedFields : [];
    }

    public function getLocationIdAttribute($value)
    {
        if ($value !== null) return $value;
        if (in_array('location_id', $this->getProxiedFields())) {
            return $this->activity?->location_id;
        }
        return $value;
    }

    public function getResponsableDelegadoIdAttribute($value)
    {
        if ($value !== null) return $value;
        if (in_array('responsable_delegado_id', $this->getProxiedFields())) {
            return $this->activity?->responsable_delegado_id;
        }
        return $value; // Return null if not proxied and not set
    }

    public function getApoyoAttribute($value)
    {
        if ($value !== null) return $value;
        if (in_array('apoyo', $this->getProxiedFields())) {
            return $this->activity?->apoyo;
        }
        return $value;
    }

    public function getEsObligatoriaAttribute($value)
    {
        if ($value !== null) return $value;
        return $this->activity?->es_obligatoria ?? true;
    }

    public function location()
    {
        // Return relationship from Activity (proxy)
        // If activity doesn't exist yet, return a dummy relation from a new Activity instance
        // This allows Filament to get the related model and query options
        if (!in_array('location_id', $this->getProxiedFields())) {
            // If the model has its own location_id, define the relationship normally
             return $this->belongsTo(\App\Models\Location::class);
        }
        return $this->activity ? $this->activity->location() : (new \App\Models\Activity)->location();
    }

    public function responsableDelegado()
    {
        if (!in_array('responsable_delegado_id', $this->getProxiedFields())) {
             return $this->belongsTo(\App\Models\Position::class, 'responsable_delegado_id');
        }
        return $this->activity ? $this->activity->responsableDelegado() : (new \App\Models\Activity)->responsableDelegado();
    }
}
