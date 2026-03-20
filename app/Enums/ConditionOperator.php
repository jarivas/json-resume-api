<?php

namespace App\Enums;

enum ConditionOperator: string
{
    use Values;

    case And = 'and';
    case Or = 'or';
}
