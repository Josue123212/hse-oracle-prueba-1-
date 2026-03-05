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
            foreach ($proxied as $field) {
                // Check if the attribute is set in the attributes array
                // We access attributes directly to avoid triggering accessors
                $attributes = $model->getAttributes();
                if (array_key_exists($field, $attributes)) {
                    // Store in temporary array property (not attribute)
                    $model->tempProxies[$field] = $attributes[$field];
                    // Remove from attributes so it's not saved to DB
                    unset($model->attributes[$field]);
                }
            }
        });

        static::saved(function (Model $model) {
            $updates = $model->tempProxies; // Access the property
            
            if (!empty($updates) && $model->activity) {
                $model->activity->update($updates);
            }
            
            // Clear temp proxies
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
        return null;
    }

    public function getResponsableDelegadoIdAttribute($value)
    {
        if ($value !== null) return $value;
        if (in_array('responsable_delegado_id', $this->getProxiedFields())) {
            return $this->activity?->responsable_delegado_id;
        }
        return null;
    }

    public function getApoyoAttribute($value)
    {
        if ($value !== null) return $value;
        if (in_array('apoyo', $this->getProxiedFields())) {
            return $this->activity?->apoyo;
        }
        return null;
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
        return $this->activity ? $this->activity->location() : (new \App\Models\Activity)->location();
    }

    public function responsableDelegado()
    {
        return $this->activity ? $this->activity->responsableDelegado() : (new \App\Models\Activity)->responsableDelegado();
    }
}
