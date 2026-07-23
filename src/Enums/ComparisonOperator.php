<?php

namespace BradieTilley\Achievements\Enums;

enum ComparisonOperator: string
{
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case Equal = '=';
    case NotEqual = '!=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
}
