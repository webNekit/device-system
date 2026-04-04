<?php

namespace App\Application\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchIsolationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // 1. Игнорируем скоуп, если мы запускаем команды в консоли (php artisan)
        if (app()->runningInConsole()) {
            return;
        }

        // 2. Если есть авторизованный пользователь
        if (auth()->hasUser()) {
            $user = auth()->user();

            // 3. И если он НЕ Админ -> фильтруем по его филиалу
            if (! $user->hasRole('Admin')) {
                $builder->where($model->getTable().'.branch_id', $user->branch_id);
            }
        }
    }
}
