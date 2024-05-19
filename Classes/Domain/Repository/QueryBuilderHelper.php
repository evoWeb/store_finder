<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Utility class that parses sql statements with regard to types and parameters.
 */
class QueryBuilderHelper
{
    public static function getStatement(QueryBuilder $queryBuilder): string
    {
        [$sql, $parameters, $types] = self::expandListParameters(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            $queryBuilder->getParameterTypes(),
        );

        array_walk($parameters, function ($value, $key) use (&$sql, $types) {
            if ($types[$key] == ParameterType::STRING) {
                $value = '\'' . $value . '\'';
            } elseif ($types[$key] == ArrayParameterType::INTEGER) {
                $value = implode(', ', $value);
            } elseif ($types[$key] == ArrayParameterType::STRING) {
                $value = $value ? '"' . implode('", "', $value) . '"' : '""';
            }
            if (is_int($key)) {
                $sql = substr_replace($sql, (string)$value, strpos($sql, '?'), 1);
            } else {
                $sql = str_replace(':' . $key, $value, $sql);
            }
        });

        return $sql;
    }

    /**
     * Gets an array of the placeholders in a sql statements as keys and their positions in the query string.
     *
     * For a statement with positional parameters, returns a zero-indexed list of placeholder position.
     * For a statement with named parameters, returns a map of placeholder positions to their parameter names.
     */
    public static function getPlaceholderPositions(string $statement, bool $isPositional = true): array
    {
        return $isPositional
            ? self::getPositionalPlaceholderPositions($statement)
            : self::getNamedPlaceholderPositions($statement);
    }

    /**
     * Returns a zero-indexed list of placeholder position.
     *
     * @return list<int>
     */
    private static function getPositionalPlaceholderPositions(string $statement): array
    {
        return self::collectPlaceholders(
            $statement,
            '?',
            '\?',
            static function (string $_, int $placeholderPosition, int $fragmentPosition, array &$carry): void {
                $carry[] = $placeholderPosition + $fragmentPosition;
            }
        );
    }

    /**
     * Returns a map of placeholder positions to their parameter names.
     *
     * @return array<int,string>
     */
    private static function getNamedPlaceholderPositions(string $statement): array
    {
        return self::collectPlaceholders(
            $statement,
            ':',
            '(?<!:):[a-zA-Z_][a-zA-Z0-9_]*',
            static function (
                string $placeholder,
                int $placeholderPosition,
                int $fragmentPosition,
                array &$carry
            ): void {
                $carry[$placeholderPosition + $fragmentPosition] = substr($placeholder, 1);
            }
        );
    }

    private static function collectPlaceholders(
        string $statement,
        string $match,
        string $token,
        callable $collector
    ): array {
        if (!str_contains($statement, $match)) {
            return [];
        }

        $carry = [];

        foreach (self::getUnquotedStatementFragments($statement) as $fragment) {
            preg_match_all('/' . $token . '/', $fragment[0], $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $placeholder) {
                $collector($placeholder[0], $placeholder[1], $fragment[1], $carry);
            }
        }

        return $carry;
    }

    /**
     * For a positional query this method can rewrite the sql statement with regard to array parameters.
     *
     * @throws \Exception
     */
    public static function expandListParameters(string $query, array $params, array $types): array
    {
        $isPositional = is_int(key($params));
        $arrayPositions = [];
        $bindIndex = -1;

        if ($isPositional) {
            // make sure that $types has the same keys as $params
            // to allow omitting parameters with unspecified types
            $types += array_fill_keys(array_keys($params), null);

            ksort($params);
            ksort($types);
        }

        foreach ($types as $name => $type) {
            ++$bindIndex;

            if ($type !== ArrayParameterType::INTEGER && $type !== ArrayParameterType::STRING) {
                continue;
            }

            if ($isPositional) {
                $name = $bindIndex;
            }

            $arrayPositions[$name] = false;
        }

        if ((!$arrayPositions && $isPositional)) {
            return [$query, $params, $types];
        }

        if ($isPositional) {
            $paramOffset = 0;
            $queryOffset = 0;
            $params = array_values($params);
            $types = array_values($types);

            $paramPos = self::getPositionalPlaceholderPositions($query);

            foreach ($paramPos as $needle => $needlePos) {
                if (!isset($arrayPositions[$needle])) {
                    continue;
                }

                $needle += $paramOffset;
                $needlePos += $queryOffset;
                $count = count($params[$needle]);

                $params = array_merge(
                    array_slice($params, 0, $needle),
                    $params[$needle],
                    array_slice($params, $needle + 1)
                );

                $types = array_merge(
                    array_slice($types, 0, $needle),
                    $count ?
                        // array needles are at {@link \Doctrine\DBAL\ParameterType} constants
                        // + {@link \Doctrine\DBAL\Connection::ARRAY_PARAM_OFFSET}
                        array_fill(0, $count, $types[$needle]) :
                        [],
                    array_slice($types, $needle + 1)
                );

                $expandStr = $count ? implode(', ', array_fill(0, $count, '?')) : 'NULL';
                $query = substr($query, 0, $needlePos) . $expandStr . substr($query, $needlePos + 1);

                $paramOffset += $count - 1; // Grows larger by number of parameters minus the replaced needle.
                $queryOffset += strlen($expandStr) - 1;
            }

            return [$query, $params, $types];
        }

        $queryOffset = 0;
        $typesOrd = [];
        $paramsOrd = [];

        $paramPos = self::getNamedPlaceholderPositions($query);

        foreach ($paramPos as $pos => $paramName) {
            $paramLen = strlen($paramName) + 1;
            $value = self::extractParam($paramName, $params, true);

            if (!isset($arrayPositions[$paramName]) && !isset($arrayPositions[':' . $paramName])) {
                $pos += $queryOffset;
                $queryOffset -= $paramLen - 1;
                $paramsOrd[] = $value;
                $typesOrd[] = self::extractParam($paramName, $types, false, ParameterType::STRING);
                $query = substr($query, 0, $pos) . '?' . substr($query, $pos + $paramLen);

                continue;
            }

            $count = count($value);
            $expandStr = $count > 0 ? implode(', ', array_fill(0, $count, '?')) : 'NULL';

            foreach ($value as $val) {
                $paramsOrd[] = $val;
                $typesOrd[] = self::extractParam($paramName, $types, false);
            }

            $pos += $queryOffset;
            $queryOffset += strlen($expandStr) - $paramLen;
            $query = substr($query, 0, $pos) . $expandStr . substr($query, $pos + $paramLen);
        }

        return [$query, $paramsOrd, $typesOrd];
    }

    /**
     * Slice the SQL statement around pairs of quotes and
     * return string fragments of outside SQL of quoted literals.
     * Each fragment is captured as a 2-element array:
     *
     * 0 => matched fragment string,
     * 1 => offset of fragment in $statement
     */
    private static function getUnquotedStatementFragments(string $statement): array
    {
        $literal = "(?:'(?:\\\\)+'|'(?:[^'\\\\]|\\\\'?|'')*')"
            . '|' . '(?:"(?:\\\\)+"|"(?:[^"\\\\]|\\\\"?)*")'
            . '|' . '(?:`(?:\\\\)+`|`(?:[^`\\\\]|\\\\`?)*`)'
            . '|' . '(?<!\b(?i:ARRAY))\[(?:[^\]])*\]';
        $expression = sprintf('/((.+(?i:ARRAY)\\[.+\\])|([^\'"`\\[]+))(?:%s)?/s', $literal);

        preg_match_all($expression, $statement, $fragments, PREG_OFFSET_CAPTURE);

        return $fragments[1];
    }

    private static function extractParam(
        string $paramName,
        array $paramsOrTypes,
        bool $isParam,
        mixed $defaultValue = null
    ): mixed {
        if (array_key_exists($paramName, $paramsOrTypes)) {
            return $paramsOrTypes[$paramName];
        }

        // Hash keys can be prefixed with a colon for compatibility
        if (array_key_exists(':' . $paramName, $paramsOrTypes)) {
            return $paramsOrTypes[':' . $paramName];
        }

        if ($defaultValue !== null) {
            return $defaultValue;
        }

        if ($isParam) {
            throw new \Exception($paramName);
        }

        throw new \Exception($paramName);
    }
}
