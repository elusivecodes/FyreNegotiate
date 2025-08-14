<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Utility\Traits\MacroTrait;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function array_shift;
use function array_unique;
use function count;
use function explode;
use function in_array;
use function preg_match;
use function strtok;
use function substr_count;
use function trim;
use function usort;

/**
 * Negotiate
 */
abstract class Negotiate
{
    use MacroTrait;

    /**
     * Negotiate a content type.
     *
     * @param string $accepted The accept content header.
     * @param array $supported The supported content types.
     * @param bool $strict Whether to use the default fallback.
     * @return string The negotiated content types.
     */
    public static function content(string $accepted, array $supported, bool $strict = false): string
    {
        return static::getBestMatch($accepted, $supported, [
            'strict' => $strict,
            'enforceTypes' => true,
        ]);
    }

    /**
     * Negotiate an encoding.
     *
     * @param string $accepted The accept encoding header.
     * @param array $supported The supported encodings.
     * @return string The negotiated encoding.
     */
    public static function encoding(string $accepted, array $supported): string
    {
        return static::getBestMatch($accepted, $supported + ['identity']);
    }

    /**
     * Negotiate a language.
     *
     * @param string $accepted The accept language header.
     * @param array $supported The supported languages.
     * @return string The negotiated language.
     */
    public static function language(string $accepted, array $supported): string
    {
        return static::getBestMatch($accepted, $supported, [
            'matchLocales' => true,
        ]);
    }

    /**
     * Get the best match for a header.
     *
     * @param string $accepted The accepted header value.
     * @param array $supported The supported values.
     * @param array $options Options for comparison.
     * @return string The best match.
     *
     * @throws InvalidArgumentException if no supported values are supplied.
     */
    protected static function getBestMatch(string $accepted, array $supported, array $options = []): string
    {
        if ($supported === []) {
            throw new InvalidArgumentException('No supported values supplied');
        }

        $options['enforceTypes'] ??= false;
        $options['strict'] ??= false;
        $options['matchLocales'] ??= false;

        if ($options['strict']) {
            $default = '';
        } else {
            $default = $supported[0] ?? '';
        }

        if (!$accepted) {
            return $default;
        }

        $accepted = static::parseHeader($accepted);

        $supported = array_unique($supported);
        $supported = array_reduce(
            $supported,
            fn(array $acc, string $value): array => array_merge($acc, static::parseHeader($value)),
            []
        );

        foreach ($accepted as $a) {
            if (!$a['q']) {
                continue;
            }

            if ($a['value'] === '*' || $a['value'] === '*/*') {
                return $supported[0]['value'];
            }

            foreach ($supported as $b) {
                if (static::match($a, $b, $options)) {
                    return $b['value'];
                }
            }
        }

        return $default;
    }

    /**
     * Match values.
     *
     * @param array $a The first value.
     * @param array $b The second value.
     * @param array $options Options for comparison.
     * @return bool TRUE if the values match, otherwise FALSE.
     */
    protected static function match(array $a, array $b, array $options): bool
    {
        if ($a['value'] === $b['value']) {
            return static::matchParameters($a['params'], $b['params']);
        }

        if ($options['enforceTypes']) {
            return static::matchSubTypes($a['value'], $b['value']);
        }

        if ($options['matchLocales']) {
            return static::matchLocales($a['value'], $b['value']);
        }

        return false;
    }

    /**
     * Match locale strings.
     *
     * @param string $a The first locale string.
     * @param string $b The second locale string.
     * @return bool TRUE if the locale strings match, otherwise FALSE.
     */
    protected static function matchLocales(string $a, string $b): bool
    {
        return strtok($a, '-') === strtok($b, '-');
    }

    /**
     * Match parameters.
     *
     * @param array $a The first parameters.
     * @param array $b The second parameters.
     * @return bool TRUE if the parameters match, otherwise FALSE.
     */
    protected static function matchParameters(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($b as $label => $value) {
            $test = $a[$label] ?? null;

            if ($test !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Match sub types.
     *
     * @param string $a The first value.
     * @param string $b The second value.
     * @return bool TRUE if the sub types match, otherwise FALSE.
     */
    protected static function matchSubTypes(string $a, string $b): bool
    {
        [$aType, $aSubType] = explode('/', $a, 2);
        [$bType, $bSubType] = explode('/', $b, 2);

        if ($aType !== $bType) {
            return false;
        }

        if (in_array('*', [$aSubType, $bSubType])) {
            return true;
        }

        return $aSubType === $bSubType;
    }

    /**
     * Parse a header for accepted values.
     *
     * @param string $header The header string.
     * @return array The accepted values.
     */
    protected static function parseHeader(string $header): array
    {
        $results = [];
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $pairs = explode(';', $part);
            $value = array_shift($pairs);

            $parameters = [];

            foreach ($pairs as $pair) {
                if (!preg_match('/^(.+?)=(["\']?)(.*?)(?:\2)$/', $pair, $match)) {
                    continue;
                }

                $name = trim($match[1]);
                $val = trim($match[3]);

                $parameters[$name] = $val;
            }

            $quality = $parameters['q'] ?? 1;
            unset($parameters['q']);

            $results[] = [
                'value' => trim($value),
                'q' => (float) $quality,
                'params' => $parameters,
            ];
        }

        usort($results, function(array $a, array $b): int {
            $aVal = $a['q'];
            $bVal = $b['q'];

            if ($aVal === $bVal) {
                $aVal = substr_count($a['value'], '*');
                $bVal = substr_count($b['value'], '*');
            }

            if ($aVal === $bVal) {
                $aVal = count($a['params']);
                $bVal = count($b['params']);
            }

            return $bVal <=> $aVal;
        });

        return $results;
    }
}
