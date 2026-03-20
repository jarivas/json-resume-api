<?php

namespace App\Http\Requests;

use App\Enums\ConditionOperator;
use App\Enums\RelationOperator;
use App\Enums\SearchOperator;
use Illuminate\Foundation\Http\FormRequest;

class BaseSearch extends FormRequest
{
    public function rules()
    {
        $searchOperatorValues = implode(',', SearchOperator::values());
        $relationOperatorValues = implode(',', RelationOperator::values());
        $conditionOperatorValues = implode(',', ConditionOperator::values());

        return [
            'sort_by' => 'array',
            'sort_by.field' => 'string',
            'sort_by.order' => 'in:asc,desc',
            'filter_by' => 'array',
            'filter_by.*.field' => 'string',
            'filter_by.*.operator' => "in:$searchOperatorValues",
            'filter_by.*.value' => 'sometimes',
            'filter_by.*.condition' => "in:$conditionOperatorValues",
            'relation' => 'array',
            'relation.name' => 'string',
            'relation.operator' => "in:$relationOperatorValues",
            'relation.filter_by.*.field' => 'string',
            'relation.filter_by.*.operator' => "in:$searchOperatorValues",
            'relation.filter_by.*.value' => 'sometimes',
            'relation.filter_by.*.condition' => "in:$conditionOperatorValues",
        ];
    }
}
