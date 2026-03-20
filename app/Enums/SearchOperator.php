<?php

namespace App\Enums;

enum SearchOperator: string
{
    use Values;

    case Equals = '=';
    case NotEquals = '!=';
    case GreaterThan = '>';
    case LessThan = '<';
    case GreaterThanOrEqual = '>=';
    case LessThanOrEqual = '<=';
    case Like = 'like';
    case NotLike = 'not like';
    case In = 'in';
    case NotIn = 'not in';
    case Between = 'between';
}
