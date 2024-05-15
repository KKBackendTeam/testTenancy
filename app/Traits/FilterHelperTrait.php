<?php

namespace App\Traits;

use Carbon\Carbon;

trait FilterHelperTrait
{
    public $chooseLikeWiseOperator = [
        '!' => 'not like',
        '*' => 'like',
        '=' => 'like',
        '{' => 'like',
        '}' => 'like'
    ];

    public $chooseOperatorForNumbers = [
        '>=' => '>=',
        '<=' => '<=',
        '>' => '>',
        '<' => '<',
        '=' => '=',
        '!' => '!=',
        '*' => '='
    ];

    public function workWithMultipleAttributes($data, $var1, $var2, $var3)
    {
        $explodeVia = (strpos($data, '||') !== false) ? '||' : '&&';
        $st = explode($explodeVia, $data);
        $araayOfValue = [];
        foreach ($st as $key => $s) {
            $value = $this->seprateOperatorValue($s);
            $araayOfValue['first_' . $key] = $this->firstPartCreator($value, $var1, $var2, $var3);
            $araayOfValue['second_' . $key] = $this->lastPartCreator($value);
        }
        return [$explodeVia, $araayOfValue];
    }

    public function workWithSingleAttribute($data, $var1, $var2, $var3)
    {
        $arrayOfValue = [];
        $value = $this->seprateOperatorValue($data);
        $arrayOfValue['first_0'] = $this->firstPartCreator($value, $var1, $var2, $var3);
        $arrayOfValue['second_0'] = $this->lastPartCreator($value);

        return $arrayOfValue;
    }

    public function workWithMultipleNumberAttributes($data)
    {
        $explodeVia = (strpos($data, '||') !== false) ? '||' : '&&';
        $st = explode($explodeVia, $data);
        $arrayOfValue = [];
        foreach ($st as $key => $s) {
            $arrayOfValue += $this->workWithSingleNumberAttribute($s, $key);
        }
        return [$explodeVia, $arrayOfValue];
    }

    public function workWithSingleNumberAttribute($data, $key)
    {
        $key = $key != 0 ? $key : 0;
        $arrayOfValue = [];
        $value = $this->seprateOperatorValue($data);
        $arrayOfValue['first_' . $key] = $this->chooseOperatorForNumbers[$value['operator']];
        $arrayOfValue['second_' . $key] = $value['value'];

        return $arrayOfValue;
    }

    public function stringPartConcation($var1, $var2, $var3)
    {
        if (!is_null($var3)) {
            $firstValue = 'concat(' . $var1 . ",' '," . $var2 . ",' '," . $var3 . ') ~* ?';
        } elseif (!is_null($var2)) {
            $firstValue = 'concat(' . $var1 . ",' '," . $var2 . ') ~* ?';
        } else {
            $firstValue = 'concat(' . $var1 . ') ~* ?';
        }
        return $firstValue;
    }

    public function firstPartCreator($value, $var1, $var2, $var3)
    {
        if (!is_null($var3)) {
            $firstValue = 'lower(concat(' . $var1 . ",' '," . $var2 . ",' '," . $var3 . ')) ';
        } elseif (!is_null($var2)) {
            $firstValue = 'lower(concat(' . $var1 . ",' '," . $var2 . ')) ';
        } else {
            $firstValue = 'lower(' . $var1 . ') ';
        }
        return $firstValue . $this->chooseLikeWiseOperator[$value['operator']] . ' ?';
    }

    public function datePartCreator($value, $variable)
    {
        // Carbon::createFromFormat('d/m/Y', $value['value'])->format('Y-m-d')
        $dateArray[0] = $variable;
        $dateArray[1] = $this->chooseOperatorForNumbers[$value['operator']];
        $dateArray[2] = $this->isCorrectDate($value['value']);

        return $dateArray;
    }

    public function lastPartCreator($operatorValue)
    {
        $trimValue = trim(strtolower($operatorValue['value']));
        if ($operatorValue['operator'] == '{') {
            $secondPart = $trimValue . "%";
        } elseif ($operatorValue['operator'] == '}') {
            $secondPart = "%" . $trimValue;
        } else {
            $secondPart = "%" . $trimValue . "%";
        }
        return $secondPart;
    }

    public function dateLastPartCreator($operatorValue)
    {
        $trimValue = trim(strtolower($operatorValue['value']));
        if ($operatorValue['operator'] == '{') {
            $secondPart = $trimValue . "%";
        } elseif ($operatorValue['operator'] == '}') {
            $secondPart = "%" . $trimValue;
        } else {
            $secondPart = "%" . $trimValue . "%";
        }
        return $secondPart;
    }

    public function workWithMultipleAttributesWithArrayValue($statusArray, $value)
    {
        $explodeVia = (strpos($value, '||') !== false) ? '||' : '&&';
        $st = explode($explodeVia, $value);
        $arrayOfValue = [];
        foreach ($st as $single) {
            foreach ($this->findStatusIdHelper($statusArray, trim($single)) as $data) {
                array_push($arrayOfValue, $data);
            }
        }

        return $arrayOfValue;
    }

    public function workWithMultipleAttributesWithDate($data, $variableName)
    {
        $explodeVia = (strpos($data, '||') !== false) ? '||' : '&&';
        $st = explode($explodeVia, $data);
        $arrayOfValue = [];
        foreach ($st as $key => $s) {
            $arrayOfValue += $this->workWithSingleAttributeWithDate($s, $key, $variableName);
        }
        return [$explodeVia, $arrayOfValue];
    }

    public function workWithSingleAttributeWithDate($data, $key, $variableName)
    {
        $key = $key != 0 ? $key : 0;
        $value = $this->seprateOperatorValue($data);
        $dateArray = $this->datePartCreator($value, $variableName);
        $araayOfValue['first_' . $key] = $dateArray[0];
        $araayOfValue['second_' . $key] = $dateArray[1];
        $araayOfValue['third_' . $key] = $dateArray[2];
        return $araayOfValue;
    }

    public function seprateOperatorValue($value)
    {
        $temp = trim($value);
        $arrayOfSymbol = ['>=', '<=', '<', '>', '=', '!', '*', '{', '}'];
        $dualBegin = trim(substr($temp, 0, 2));
        $dualLast = substr($temp, 2);
        $singleBegin = trim(substr($temp, 0, 1));
        $singleLast = trim(substr($temp, 1));

        $dualBeginFromEnd = trim(substr($temp, -2));
        $dualLastFromEnd = substr($temp, 0, -2);
        $singleBeginFromEnd = trim(substr($temp, -1));
        $singleLastFromEnd = trim(substr($temp, 0, -1));

        if (in_array($dualBegin, $arrayOfSymbol)) {
            $operator = $dualBegin;
            $value = $dualLast;
        } elseif (in_array($singleBegin, $arrayOfSymbol)) {
            $operator = $singleBegin;
            $value = $singleLast;
        } elseif (in_array($dualBeginFromEnd, $arrayOfSymbol)) {
            $operator = $dualBeginFromEnd;
            $value = $dualLastFromEnd;
        } elseif (in_array($singleBeginFromEnd, $arrayOfSymbol)) {
            $operator = $singleBeginFromEnd;
            $value = $singleLastFromEnd;
        } else {
            $operator = "=";
            $value = $temp;
        }
        return array("operator" => $operator, "value" => trim($value));
    }

    public function findStatusIdHelper($statusArray, $statusValue)
    {
        $operatorValue = $this->seprateOperatorValue($statusValue);
        $likeWiseOperator = $this->chooseLikeWiseOperator[$operatorValue['operator']];

        if (!empty($operatorValue['value'])) {
            $matches = array_filter($statusArray, function ($var) use ($operatorValue) {
                return strpos(strtolower($var), strtolower($operatorValue['value'])) !== false;
            });

            if ($likeWiseOperator == 'not like') {
                foreach ($matches as $key => $singleStatus) {
                    unset($statusArray[$key]);
                }
                return array_keys($statusArray);
            }
            return array_keys($matches);
        } else {
            return [];
        }
    }

    public function checkEmptyNotEmptyValue($value)
    {
        return $value == '[empty]' ? 'whereNull' : 'whereNotNull';
    }

    public function takeRegexSubstring($string)
    {
        return substr($string, 4);
    }

    public function isCorrectDate($date)
    {
        try {
            $arrayOfDate = explode('/', $date);
            $formatDate = Carbon::createFromDate($arrayOfDate[2], $arrayOfDate[1], $arrayOfDate[0]);
            if (($arrayOfDate[1] > 12) || ($formatDate->lastOfMonth()->day < $arrayOfDate[0])) {
                return false;
            }
            return Carbon::createFromDate($arrayOfDate[2], $arrayOfDate[1], $arrayOfDate[0])->toDateString();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function takeRegexFromArray($statusArray, $string)
    {
        $regex = $this->takeRegexSubstring($string);
        $fl_array = preg_grep('/' . $regex . '/', $statusArray);
        return array_keys($fl_array);
    }

    public function filterWithStringAttributes($requestVariable, $query, $first, $second, $third)
    {
        // Check if $requestVariable is empty
        if (!empty($requestVariable)) {
            if (($requestVariable === '[empty]') || ($requestVariable === '[nonempty]')) {
                $query->{$this->checkEmptyNotEmptyValue($requestVariable)}($first);
            } elseif (strpos($requestVariable, '||') !== false || strpos($requestVariable, '&&') !== false) {
                $operatorValue = $this->workWithMultipleAttributes($requestVariable, $first, $second, $third);
                $query->whereRaw($operatorValue[1]['first_0'], $operatorValue[1]['second_0']);
                $query->{$operatorValue[0] == '||' ? 'orWhereRaw' : 'whereRaw'}($operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
            } elseif (substr($requestVariable, 0, 4) === 'rgx:') {
                $query->whereRaw($this->stringPartConcation($first, $second, $third), $this->takeRegexSubstring($requestVariable));
            } else {
                $operatorValue = $this->workWithSingleAttribute($requestVariable, $first, $second, $third);
                $query->whereRaw($operatorValue['first_0'], $operatorValue['second_0']);
            }
        }
    }

    public function filterWithNumberAttributes($requestVariable, $query, $first, $second, $third)
    {
        if (($requestVariable === '[empty]') || ($requestVariable === '[nonempty]')) {

            $query->{$this->checkEmptyNotEmptyValue($requestVariable)}($first);
        } else if ((strpos($requestVariable, '||') !== false || strpos($requestVariable, '&&') !== false)) {

            $operatorValue = $this->workWithMultipleNumberAttributes($requestVariable);
            if (is_numeric($operatorValue[1]['second_0']) && is_numeric($operatorValue[1]['second_1'])) {
                $query->where($first, $operatorValue[1]['first_0'], $operatorValue[1]['second_0']);
                $query->{$operatorValue[0] == '||' ? 'orWhere' : 'where'}($first, $operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
            }
        } elseif (substr($requestVariable, 0, 4) === 'rgx:') {
            $query->whereRaw($this->stringPartConcation($first, $second, $third), $this->takeRegexSubstring($requestVariable));
        } else {
            $operatorValue = $this->workWithSingleNumberAttribute($requestVariable, 0);
            if (is_numeric($operatorValue['second_0'])) {
                $query->where($first, $operatorValue['first_0'], $operatorValue['second_0']);
            } else {
                $query->where($first, '>', '15000000');
            }
        }
    }

    public function filterWithDateAttributes($requestVariable, $query, $first, $second, $third)
    {
        if (($requestVariable === '[empty]') || ($requestVariable === '[nonempty]')) {

            $query->{$this->checkEmptyNotEmptyValue($requestVariable)}($first);
        } else if (strpos($requestVariable, '||') !== false || strpos($requestVariable, '&&') !== false) {

            $operatorValue = $this->workWithMultipleAttributesWithDate($requestVariable, $first);
            if ($operatorValue[1]['third_0'] && $operatorValue[1]['third_1']) {
                $query->whereDate($operatorValue[1]['first_0'], $operatorValue[1]['second_0'], $operatorValue[1]['third_0']);
                $query->{$operatorValue[0] == '||' ? 'orWhereDate' : 'whereDate'}($operatorValue[1]['first_1'], $operatorValue[1]['second_1'], $operatorValue[1]['third_1']);
            }
        } elseif (substr($requestVariable, 0, 4) === 'rgx:') {
            $query->whereRaw($this->stringPartConcation($first, $second, $third), $this->takeRegexSubstring($requestVariable));
        } else {
            $operatorValue = $this->workWithSingleAttributeWithDate($requestVariable, 0, $first);
            if ($operatorValue['third_0']) {
                $query->whereDate($operatorValue['first_0'], $operatorValue['second_0'], $operatorValue['third_0']);
            } else {
                $query->where($first, '10-23-1995');
            }
        }
    }

    public function filterWithStatusAttributes($requestVariable, $query, $databaseVariable, $statusArray)
    {
        if (($requestVariable === '[empty]') || ($requestVariable === '[nonempty]')) {
            $query->{$this->checkEmptyNotEmptyValue($requestVariable)}($databaseVariable);
        } else if (strpos($requestVariable, '||') !== false || strpos($requestVariable, '&&') !== false) {
            $query->whereIn($databaseVariable, $this->workWithMultipleAttributesWithArrayValue($statusArray, $requestVariable));
        } elseif (substr($requestVariable, 0, 4) === 'rgx:') {
            $query->whereIn($requestVariable, $this->takeRegexFromArray($statusArray, $requestVariable));
        } else {
            $query->whereIn($databaseVariable, $this->workWithMultipleAttributesWithArrayValue($statusArray, $requestVariable));
        }
    }

}
