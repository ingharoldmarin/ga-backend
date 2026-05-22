<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    // Campos excluidos del log por seguridad o por ser irrelevantes
    private array $excluded = ['password', 'remember_token', 'updated_at'];

    public function created(Model $model): void
    {
        $this->log('created', $model, [], $this->sanitize($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getDirty();
        if (empty($dirty)) return;

        $old = collect($model->getOriginal())->only(array_keys($dirty))->all();
        $new = collect($dirty)->all();

        $this->log('updated', $model, $this->sanitize($old), $this->sanitize($new));
    }

    public function deleted(Model $model): void
    {
        $isSoftDelete = method_exists($model, 'getDeletedAtColumn') && ! $model->isForceDeleting();
        $action = $isSoftDelete ? 'deleted' : 'force_deleted';
        $this->log($action, $model, $this->sanitize($model->getAttributes()), []);
    }

    public function restored(Model $model): void
    {
        $this->log('restored', $model, [], $this->sanitize($model->getAttributes()));
    }

    private function sanitize(array $data): array
    {
        return array_diff_key($data, array_flip($this->excluded));
    }

    private function log(string $action, Model $model, array $old, array $new): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'old_values' => empty($old) ? null : $old,
            'new_values' => empty($new) ? null : $new,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
