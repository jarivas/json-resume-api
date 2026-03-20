<?php

namespace App\Enums;

enum RelationOperator: string
{
    use Values;

    case Has = 'has';
    case HasNot = 'doesntHave';
    case Relation = 'relation';
}
