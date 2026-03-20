<?php

namespace App\Mcp\Servers\Traits;

trait ReadServerTrait
{
    public function readIndex(array $query = []): array
    {
        if (empty($this->model)) {
            return [];
        }

        $modelClass = $this->model;
        $builder = $modelClass::query();

        // Full-text style search across fillable fields
        if (! empty($query['q'])) {
            $q = $query['q'];
            $instance = new $modelClass;
            $fields = $instance->getFillable();

            $builder->where(function ($b) use ($fields, $q) {
                foreach ($fields as $field) {
                    $b->orWhere($field, 'like', "%{$q}%");
                }
            });
        }

        // Advanced filters: support operators with suffixes __gte,__lte,__gt,__lt,__like
        // and relationship filters using dot notation: relation.field__op=value
        foreach ($query as $k => $v) {
            if (in_array($k, ['q', 'per_page', 'page'], true)) {
                continue;
            }

            if (str_contains($k, '.')) {
                // relation filter e.g. skills.name__like
                [$relation, $rest] = explode('.', $k, 2);
                $op = 'eq';
                $field = $rest;
                if (str_contains($rest, '__')) {
                    [$field, $op] = explode('__', $rest, 2);
                }

                $this->applyRelationFilter($builder, $relation, $field, $op, $v);

                continue;
            }

            $field = $k;
            $op = 'eq';
            if (str_contains($k, '__')) {
                [$field, $op] = explode('__', $k, 2);
            }

            $this->applyFieldFilter($builder, $field, $op, $v);
        }

        $perPage = isset($query['per_page']) ? (int) $query['per_page'] : 20;
        $page = isset($query['page']) ? (int) $query['page'] : 1;

        $paginator = $builder->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function readShow(string $id): array
    {
        if (empty($this->model)) {
            return [];
        }

        $modelClass = $this->model;
        $item = $modelClass::find($id);

        return $item ? $item->toArray() : [];
    }

    protected function applyFieldFilter($builder, string $field, string $op, $value): void
    {
        switch ($op) {
            case 'gte':
                $builder->where($field, '>=', $value);
                break;
            case 'lte':
                $builder->where($field, '<=', $value);
                break;
            case 'gt':
                $builder->where($field, '>', $value);
                break;
            case 'lt':
                $builder->where($field, '<', $value);
                break;
            case 'like':
                $builder->where($field, 'like', "%{$value}%");
                break;
            default:
                $builder->where($field, $value);
        }
    }

    protected function applyRelationFilter($builder, string $relation, string $field, string $op, $value): void
    {
        $builder->whereHas($relation, function ($q) use ($field, $op, $value) {
            switch ($op) {
                case 'gte':
                    $q->where($field, '>=', $value);
                    break;
                case 'lte':
                    $q->where($field, '<=', $value);
                    break;
                case 'gt':
                    $q->where($field, '>', $value);
                    break;
                case 'lt':
                    $q->where($field, '<', $value);
                    break;
                case 'like':
                    $q->where($field, 'like', "%{$value}%");
                    break;
                default:
                    $q->where($field, $value);
            }
        });
    }
}
