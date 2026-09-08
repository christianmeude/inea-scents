<?php

namespace Tests\Feature;

use App\Support\PackageSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PackageSanitizerTest extends TestCase
{
    public static function stringCases(): array
    {
        return [
            'null' => [null, []],
            'non-array scalar' => ['nope', []],
            'empty array' => [[], []],
            'clean passthrough' => [['Pool', 'Setup'], ['Pool', 'Setup']],
            'drops null/empty/whitespace, trims' => [
                [null, '', '   ', ' Pool ', 'Setup'],
                ['Pool', 'Setup'],
            ],
            'drops non-strings' => [[12, 4.5, true, 'OK'], ['OK']],
            'json string input' => ['[" Pool ",null,""]', ['Pool']],
            'invalid json string' => ['{oops', []],
        ];
    }

    #[DataProvider('stringCases')]
    public function test_strings(mixed $input, array $expected): void
    {
        $this->assertSame($expected, PackageSanitizer::strings($input));
    }

    public static function paxCases(): array
    {
        return [
            'null' => [null, []],
            'non-array scalar' => [7, []],
            'clean passthrough' => [[20, 50], [20, 50]],
            'numeric strings coerce' => [['12', ' 7 '], [12, 7]],
            'whole floats coerce' => [[12.0, 7.0], [12, 7]],
            'fractional floats drop' => [[12.5], []],
            'zero/negative/invalid drop' => [[0, -3, 'abc', '', null, '  '], []],
            'json string input' => ['["12",null,50]', [12, 50]],
            'mixed row' => [
                [null, '20', 30, 40.0, 12.5, 0, 'x'],
                [20, 30, 40],
            ],
        ];
    }

    #[DataProvider('paxCases')]
    public function test_pax_options(mixed $input, array $expected): void
    {
        $this->assertSame($expected, PackageSanitizer::paxOptions($input));
    }
}
