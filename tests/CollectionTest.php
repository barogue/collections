<?php

namespace Barogue\Collections\Tests;

use ArrayIterator;
use Barogue\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * Data provider for different data conversions
     *
     * @return array[]
     */
    public static function providesConversions(): array
    {
        return [
            'nothing' => [null, []],
            'array' => [[1, 2, 3], [1, 2, 3]],
            'collection' => [new Collection([1, 2, 3]), [1, 2, 3]],
            'array-iterator' => [new ArrayIterator([1, 2, 3]), [1, 2, 3]],
        ];
    }

    /**
     * @covers \Barogue\Collections\Collection::changeKeyCase()
     */
    public function testChangeKeyCase()
    {
        $collection = new Collection(['FirSt' => 1, 'SecOnd' => 4]);
        $this->assertSame(['first' => 1, 'second' => 4], $collection->changeKeyCase(CASE_LOWER)->getArray());
        $this->assertSame(['FIRST' => 1, 'SECOND' => 4], $collection->changeKeyCase(CASE_UPPER)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::changeKeyLowerCase()
     */
    public function testChangeKeyLowerCase()
    {
        $collection = new Collection(['FirSt' => 1, 'SecOnd' => 4]);
        $this->assertSame(['first' => 1, 'second' => 4], $collection->changeKeyLowerCase()->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::changeKeyUpperCase()
     */
    public function testChangeKeyUpperCase()
    {
        $collection = new Collection(['FirSt' => 1, 'SecOnd' => 4]);
        $this->assertSame(['FIRST' => 1, 'SECOND' => 4], $collection->changeKeyUpperCase()->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::chunk()
     */
    public function testChunk()
    {
        $collection = Collection::range(0, 99);

        // Chunked - Keep keys
        $chunks = $collection->chunk(20);
        $this->assertInstanceOf(Collection::class, $chunks);
        $this->assertCount(5, $chunks);
        for ($i = 0; $i < 5; $i++) {
            $this->assertInstanceOf(Collection::class, $chunks[$i]);
            $this->assertCount(20, $chunks[$i]);
            for ($j = 0; $j < 20; $j++) {
                $b = $i * 20;
                $key = $b + $j;
                $value = $b + $j;
                $this->assertSame($value, $chunks[$i][$key]);
            }
        }

        // Chunked - Do not keep keys
        $chunks = $collection->chunk(20, false);
        $this->assertInstanceOf(Collection::class, $chunks);
        $this->assertCount(5, $chunks);
        for ($i = 0; $i < 5; $i++) {
            $this->assertInstanceOf(Collection::class, $chunks[$i]);
            $this->assertCount(20, $chunks[$i]);
            for ($j = 0; $j < 20; $j++) {
                $b = $i * 20;
                $key = $j;
                $value = $b + $j;
                $this->assertSame($value, $chunks[$i][$key]);
            }
        }
    }

    /**
     * @covers \Barogue\Collections\Collection::column()
     */
    public function testColumn()
    {
        $collection = new Collection([
            'player_1' => [
                'name' => 'John',
                'hp' => 50,
                'exp' => 1000
            ],
            'player_2' => [
                'name' => 'Jane',
                'hp' => 70,
                'exp' => 1000
            ]
        ]);
        $this->assertSame([50, 70], $collection->column('hp')->getArray());
        $this->assertSame(['John' => 50, 'Jane' => 70], $collection->column('hp', 'name')->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::column()
     */
    public function testColumnIntegerKeys()
    {
        $collection = new Collection([
            'player_1' => [
                'stats' => [
                    'name' => 'John',
                    'hp' => 50,
                    'exp' => 1000
                ],
                3 => 'hello 3',
                4 => 'hello 4',
            ],
            'player_2' => [
                'stats' => [
                    'name' => 'Jane',
                    'hp' => 70,
                    'exp' => 1000
                ],
                3 => 'goodbye 3',
                4 => 'goodbye 4',
            ]
        ]);
        $this->assertSame(['hello 3', 'goodbye 3'], $collection->column(3)->getArray());
        $this->assertSame(['hello 4' => 50, 'goodbye 4' => 70], $collection->column('stats.hp', 4)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::column()
     */
    public function testColumnNested()
    {
        $collection = new Collection([
            'player_1' => [
                'stats' => [
                    'name' => 'John',
                    'hp' => 50,
                    'exp' => 1000
                ]
            ],
            'player_2' => [
                'stats' => [
                    'name' => 'Jane',
                    'hp' => 70,
                    'exp' => 1000
                ]
            ]
        ]);
        $this->assertSame([50, 70], $collection->column('stats.hp')->getArray());
        $this->assertSame(['John' => 50, 'Jane' => 70], $collection->column('stats.hp', 'stats.name')->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::combine()
     *
     * @dataProvider providesConversions()
     */
    public function testCombine($input, $output)
    {
        if ($input !== null) {
            $this->assertSame(array_combine([9, 8, 7], $output), Collection::instance([9, 8, 7])->combine($input)->getArray());
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @covers \Barogue\Collections\Collection::__construct()
     *
     * @dataProvider providesConversions()
     */
    public function testConstructor($input, $output)
    {
        $collection = new Collection($input);
        if (empty($output)) {
            $this->assertEmpty($collection);
        } else {
            $this->assertNotEmpty($collection);
        }
        $this->assertCount(count($output), $collection);
        foreach ($output as $key => $value) {
            $this->assertSame($value, $collection[$key]);
        }
    }

    /**
     * @covers \Barogue\Collections\Collection::convertToArray()
     *
     * @dataProvider providesConversions()
     */
    public function testConvertToArray($input, $output)
    {
        $this->assertSame($output, Collection::convertToArray($input));
    }

    /**
     * @covers \Barogue\Collections\Collection::count()
     */
    public function testCount()
    {
        $collection = new Collection();
        $this->assertCount(0, $collection);
        $collection[] = 1;
        $collection[] = 2;
        $collection[] = 3;
        $this->assertCount(3, $collection);
    }

    /**
     * @covers \Barogue\Collections\Collection::countValues()
     */
    public function testCountValues()
    {
        $collection = new Collection([1, 2, 3, 1, 2, 4, 'a', 'a', 1]);

        $appearances = $collection->countValues(); // [1 => 3, 2 => 2, 3 => 1, 4 => 1, 'a' => 2]

        $this->assertInstanceOf(Collection::class, $appearances);

        $this->assertSame([1 => 3, 2 => 2, 3 => 1, 4 => 1, 'a' => 2], $appearances->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::deflate()
     */
    public function testDeflateKeepKeys()
    {
        $collection = new Collection();
        $collection['a.b.c'] = 1;
        $collection['a.c.a'] = 2;
        $this->assertSame([
            'a' => [
                'b' => [
                    'c' => 1
                ],
                'c' => [
                    'a' => 2
                ]
            ]
        ], $collection->getArray());

        $deflated = $collection->deflate();
        $this->assertSame([
            'a.b.c' => 1,
            'a.c.a' => 2,
        ], $deflated->getArray());

        $this->assertSame([
            'a' => [
                'b' => [
                    'c' => 1
                ],
                'c' => [
                    'a' => 2
                ]
            ]
        ], $collection->getArray());

        $this->assertNotSame($collection, $deflated);
    }

    /**
     * @covers \Barogue\Collections\Collection::deflate()
     */
    public function testDeflateNoKeepKeys()
    {
        $collection = new Collection();
        $collection['a.b.c'] = 1;
        $collection['a.c.a'] = 2;
        $this->assertSame([
            'a' => [
                'b' => [
                    'c' => 1
                ],
                'c' => [
                    'a' => 2
                ]
            ]
        ], $collection->getArray());

        $deflated = $collection->deflate(false);
        $this->assertSame([
            0 => 1,
            1 => 2,
        ], $deflated->getArray());

        $this->assertSame([
            'a' => [
                'b' => [
                    'c' => 1
                ],
                'c' => [
                    'a' => 2
                ]
            ]
        ], $collection->getArray());

        $this->assertNotSame($collection, $deflated);
    }

    /**
     * @covers \Barogue\Collections\Collection::diff()
     */
    public function testDiff()
    {
        $a = [1, 2, 3, 5, 6, 7, 8, 9, 10];
        $b = [6, 7, 8, 9, 10, 11, 12, 13];
        $c = [1, 2, 3, 12, 13];
        $d = [1, 2, 3];

        $this->assertSame(array_diff($a, $b), Collection::instance($a)->diff($b)->getArray());
        $this->assertSame(array_diff($a, $b, $c, $d), Collection::instance($a)->diff($b, $c, $d)->getArray());
        $this->assertSame(array_diff($a, $b, $c, $d), Collection::instance($a)->diff($b, Collection::instance($c), Collection::instance($d))->getArray());
        $this->assertSame(array_diff($a, $b, $c, $d), Collection::instance($a)->diff(Collection::instance($b), Collection::instance($c), Collection::instance($d))->getArray());
        $this->assertSame(array_diff($a, $b), Collection::instance($a)->diff(Collection::instance($b))->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::exists()
     */
    public function testExists()
    {
        $collection = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => [
                'a' => 1,
                'b' => null,
                'c' => 3,
            ],
        ]);
        $this->assertSame(true, $collection->exists('a'));
        $this->assertSame(false, $collection->exists('z'));
        $this->assertSame(false, $collection->exists('a.a'));
        $this->assertSame(true, $collection->exists('c.a'));
        $this->assertSame(true, $collection->exists('c.b'));
        $this->assertSame(false, $collection->exists('c.z'));
    }

    /**
     * @covers \Barogue\Collections\Collection::filter()
     */
    public function testFilter()
    {
        // No callback removes empty values
        $this->assertSame([2 => 1, 4 => 2, 5 => 3], Collection::instance([null, '', 1, null, 2, 3, null])->filter()->getArray());

        // Callback
        $collection = Collection::range(1, 100)->filter(function ($value) {
            return $value < 10;
        });
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], $collection->getArray());

        // String callback
        $collection = new Collection([1, 2, 'hello', null]);
        $this->assertSame([2 => 'hello'], $collection->filter('is_string')->getArray());

        // Filter based on keys
        $collection = new Collection([9, 8, 7, 6, 5, 4, 3, 2, 1]);
        $this->assertSame([6 => 3, 7 => 2, 8 => 1], $collection->filter(function ($value, $key) {
            return $key > 5;
        }, ARRAY_FILTER_USE_BOTH)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::first()
     */
    public function testFirst()
    {
        // No callback, no default, no items
        $collection = new Collection();
        $this->assertSame(null, $collection->first());

        // No callback, no default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(10, $collection->first());

        // No callback, no default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(10, $collection->first());

        // No callback, yes default, no items
        $collection = new Collection();
        $this->assertSame('test', $collection->first(null, 'test'));

        // No callback, yes default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(10, $collection->first(null, 'test'));

        // No callback, yes default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(10, $collection->first(null, 'test'));

        // Callback that fails to match anything, no default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(null, $collection->first(function ($value) {
            return $value > 10;
        }));

        // Callback that fails to match anything, with default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame('test', $collection->first(function ($value) {
            return $value > 10;
        }, 'test'));

        // Callback that matches on value
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(3, $collection->first(function ($value) {
            return $value > 2;
        }));

        // Callback that matches on key
        $collection = new Collection([9 => 1, 8 => 2,  7 => 3, 6 => 4]);
        $this->assertSame(2, $collection->first(function ($value, $key) {
            return $key < 9;
        }));
    }

    /**
     * @covers \Barogue\Collections\Collection::firstKey()
     */
    public function testFirstKey()
    {
        // No callback, no default, no items
        $collection = new Collection();
        $this->assertSame(null, $collection->firstKey());

        // No callback, no default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(0, $collection->firstKey());

        // No callback, no default, x items
        $collection = new Collection(['a' => 10, 'b' => 9, 'c' => 8]);
        $this->assertSame('a', $collection->firstKey());

        // No callback, yes default, no items
        $collection = new Collection();
        $this->assertSame('test', $collection->firstKey(null, 'test'));

        // No callback, yes default, 1 item
        $collection = new Collection(['a' => 10]);
        $this->assertSame('a', $collection->firstKey(null, 'test'));

        // No callback, yes default, x items
        $collection = new Collection(['a' => 10, 'b' => 9, 'c' => 8]);
        $this->assertSame('a', $collection->firstKey(null, 'test'));

        // Callback that fails to match anything, no default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(null, $collection->firstKey(function ($value) {
            return $value > 10;
        }));

        // Callback that fails to match anything, with default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame('test', $collection->firstKey(function ($value) {
            return $value > 10;
        }, 'test'));

        // Callback that matches on value
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(2, $collection->firstKey(function ($value) {
            return $value > 2;
        }));

        // Callback that matches on key
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(3, $collection->firstKey(function ($value, $key) {
            return $key > 2;
        }));
    }

    /**
     * @covers \Barogue\Collections\Collection::flip()
     */
    public function testFlip()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $this->assertSame(['a' => 0, 'b' => 1, 'c' => 2], $collection->flip()->getArray());

        $collection = new Collection(['a', 'b', 'c', 'a']);
        $this->assertSame(['a' => 3, 'b' => 1, 'c' => 2], $collection->flip()->getArray());
        $this->assertSame([3 => 'a', 1 => 'b', 2 => 'c'], $collection->flip()->flip()->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::getArray()
     */
    public function testGetArray()
    {
        $array = [
            'a' => [
                'b' => [
                    'a' => 10,
                    'b' => 11,
                    'c' => 12,
                ]
            ]
        ];
        $collection = new Collection($array);
        $this->assertSame($array, $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetGet()
     */
    public function testGetNestedKey()
    {
        $collection = new Collection([
            'a' => [
                'b' => [
                    'a' => 10,
                    'b' => 11,
                    'c' => 12,
                ]
            ]
        ]);
        $this->assertSame(10, $collection['a.b.a']);
        $this->assertSame(11, $collection['a.b.b']);
        $this->assertSame(12, $collection['a.b.c']);
    }

    /**
     * @covers \Barogue\Collections\Collection::implode()
     */
    public function testImplode()
    {
        $this->assertSame('', Collection::instance()->implode());
        $this->assertSame('', Collection::instance()->implode(', '));
        $this->assertSame('', Collection::instance()->implode(', ', ' and '));

        $this->assertSame('a', Collection::instance(['a'])->implode());
        $this->assertSame('a', Collection::instance(['a'])->implode(', '));
        $this->assertSame('a', Collection::instance(['a'])->implode(', ', ' and '));

        $this->assertSame('ab', Collection::instance(['a', 'b'])->implode());
        $this->assertSame('a, b', Collection::instance(['a', 'b'])->implode(', '));
        $this->assertSame('a and b', Collection::instance(['a', 'b'])->implode(', ', ' and '));

        $this->assertSame('abc', Collection::instance(['a', 'b', 'c'])->implode());
        $this->assertSame('a, b, c', Collection::instance(['a', 'b', 'c'])->implode(', '));
        $this->assertSame('a, b and c', Collection::instance(['a', 'b', 'c'])->implode(', ', ' and '));

        $this->assertSame('abcd', Collection::instance(['a', 'b', 'c', 'd'])->implode());
        $this->assertSame('a, b, c, d', Collection::instance(['a', 'b', 'c', 'd'])->implode(', '));
        $this->assertSame('a, b, c and d', Collection::instance(['a', 'b', 'c', 'd'])->implode(', ', ' and '));
    }

    /**
     * @covers \Barogue\Collections\Collection::inflate()
     */
    public function testInflate()
    {
        $collection = new Collection(['a.b.c' => 1]);
        $inflated = $collection->inflate();
        $this->assertSame([
            'a' => [
                'b' => [
                    'c' => 1,
                ]
            ]
        ], $inflated->getArray());
        $this->assertSame([
            'a.b.c' => 1
        ], $collection->getArray());
        $this->assertNotSame($collection, $inflated);
    }

    /**
     * @covers \Barogue\Collections\Collection::instance()
     *
     * @dataProvider providesConversions()
     */
    public function testInstance($input, $output)
    {
        $collection = Collection::instance($input);
        if (empty($output)) {
            $this->assertEmpty($collection);
        } else {
            $this->assertNotEmpty($collection);
        }
        $this->assertCount(count($output), $collection);
        foreach ($output as $key => $value) {
            $this->assertSame($value, $collection[$key]);
        }
    }

    /**
     * @covers \Barogue\Collections\Collection::isEmpty()
     */
    public function testIsEmpty()
    {
        $collection = new Collection();
        $this->assertTrue($collection->isEmpty());
        $collection[] = 1;
        $this->assertFalse($collection->isEmpty());
        unset($collection[0]);
        $this->assertTrue($collection->isEmpty());
    }

    /**
     * @covers \Barogue\Collections\Collection::isList()
     */
    public function testIsList()
    {
        $this->assertSame(true, Collection::range(0, 100)->isList());
        $this->assertSame(false, Collection::instance(['a' => 1])->isList());
    }

    /**
     * @covers \Barogue\Collections\Collection::getIterator()
     */
    public function testIterable()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection = new Collection($array);

        foreach ($collection as $key => $value) {
            $this->assertSame(current($array), $value);
            $this->assertSame(key($array), $key);
            next($array);
        }

        $this->assertSame($array, iterator_to_array($collection));
    }

    /**
     * @covers \Barogue\Collections\Collection::keys()
     */
    public function testKeys()
    {
        $collection = new Collection();
        $collection['test'] = 1;
        $collection[] = 1;
        $collection['sudo'] = 1;
        $keys = $collection->keys();
        $this->assertSame(['test', 0, 'sudo'], $keys->getArray());

        $collection = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => [
                'a' => 1,
                'b' => null,
                'c' => 3,
            ],
        ]);
        $this->assertSame(['a', 'b', 'c'], $collection->keys()->getArray());
        $this->assertSame(['a', 'b', 'c.a', 'c.b', 'c.c'], $collection->keys(true)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::last()
     */
    public function testLast()
    {
        // No callback, no default, no items
        $collection = new Collection();
        $this->assertSame(null, $collection->last());

        // No callback, no default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(10, $collection->last());

        // No callback, no default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(8, $collection->last());

        // No callback, yes default, no items
        $collection = new Collection();
        $this->assertSame('test', $collection->last(null, 'test'));

        // No callback, yes default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(10, $collection->last(null, 'test'));

        // No callback, yes default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(8, $collection->last(null, 'test'));

        // Callback that fails to match anything, no default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(null, $collection->last(function ($value) {
            return $value > 10;
        }));

        // Callback that fails to match anything, with default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame('test', $collection->last(function ($value) {
            return $value > 10;
        }, 'test'));

        // Callback that matches on value
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(3, $collection->last(function ($value) {
            return $value < 4;
        }));

        // Callback that matches on key
        $collection = new Collection([9 => 1, 8 => 2,  7 => 3, 6 => 4]);
        $this->assertSame(3, $collection->last(function ($value, $key) {
            return $key > 6;
        }));
    }

    /**
     * @covers \Barogue\Collections\Collection::lastKey()
     */
    public function testLastKey()
    {
        // No callback, no default, no items
        $collection = new Collection();
        $this->assertSame(null, $collection->lastKey());

        // No callback, no default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(0, $collection->lastKey());

        // No callback, no default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(2, $collection->lastKey());

        // No callback, yes default, no items
        $collection = new Collection();
        $this->assertSame('test', $collection->lastKey(null, 'test'));

        // No callback, yes default, 1 item
        $collection = new Collection([10]);
        $this->assertSame(0, $collection->lastKey(null, 'test'));

        // No callback, yes default, x items
        $collection = new Collection([10, 9, 8]);
        $this->assertSame(2, $collection->lastKey(null, 'test'));

        // Callback that fails to match anything, no default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(null, $collection->lastKey(function ($value) {
            return $value > 10;
        }));

        // Callback that fails to match anything, with default
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame('test', $collection->lastKey(function ($value) {
            return $value > 10;
        }, 'test'));

        // Callback that matches on value
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(2, $collection->lastKey(function ($value) {
            return $value < 4;
        }));

        // Callback that matches on key
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame(3, $collection->lastKey(function ($value, $key) {
            return $key < 4;
        }));
    }

    /**
     * @covers \Barogue\Collections\Collection::map()
     */
    public function testMapNestedWithKey()
    {
        $collection = new Collection();
        $collection['a'] = 1;
        $collection['b.a'] = 2;
        $collection['b.b'] = 3;
        $collection['c.z'] = 4;

        $mapped = $collection->map(function ($value, $key) {
            return $key.'-'.$value;
        }, true);

        $this->assertSame('a-1', $mapped['a']);
        $this->assertSame('b.a-2', $mapped['b.a']);
        $this->assertSame('b.b-3', $mapped['b.b']);
        $this->assertSame('c.z-4', $mapped['c.z']);

        $this->assertSame([
            'a' => 'a-1',
            'b' => [
                'a' => 'b.a-2',
                'b' => 'b.b-3',
            ],
            'c' => [
                'z' => 'c.z-4',
            ],
        ], $mapped->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::map()
     */
    public function testMapNestedWithoutKey()
    {
        $collection = new Collection();
        $collection['a'] = 1;
        $collection['b.a'] = 2;
        $collection['b.b'] = 3;
        $collection['c.z'] = 4;

        $mapped = $collection->map(function ($value) {
            return $value * 10;
        }, true);

        $this->assertSame(10, $mapped['a']);
        $this->assertSame(20, $mapped['b.a']);
        $this->assertSame(30, $mapped['b.b']);
        $this->assertSame(40, $mapped['c.z']);

        $this->assertSame([
            'a' => 10,
            'b' => [
                'a' => 20,
                'b' => 30,
            ],
            'c' => [
                'z' => 40,
            ],
        ], $mapped->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::map()
     */
    public function testMapWithKey()
    {
        $collection = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $mapped = $collection->map(function ($value, $key) {
            return $key.'-'.$value;
        });

        $this->assertSame('a-1', $mapped['a']);
        $this->assertSame('b-2', $mapped['b']);
        $this->assertSame('c-3', $mapped['c']);

        $this->assertSame([
            'a' => 'a-1',
            'b' => 'b-2',
            'c' => 'c-3',
        ], $mapped->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::map()
     */
    public function testMapWithoutKey()
    {
        $collection = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $mapped = $collection->map(function ($value) {
            return $value * 10;
        });

        $this->assertSame(10, $mapped['a']);
        $this->assertSame(20, $mapped['b']);
        $this->assertSame(30, $mapped['c']);

        $this->assertSame([
            'a' => 10,
            'b' => 20,
            'c' => 30,
        ], $mapped->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetExists()
     */
    public function testOffsetExists()
    {
        $collection = new Collection(['a' => 'b']);
        $this->assertTrue(isset($collection['a']));
        $this->assertFalse(isset($collection['b']));
        unset($collection['a']);
        $this->assertFalse(isset($collection['a']));
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetExists()
     */
    public function testOffsetExistsNestKey()
    {
        $collection = new Collection([
            'a' => [
                'b' => [
                    'a' => 10,
                    'b' => 11,
                    'c' => 12,
                ]
            ]
        ]);
        $this->assertTrue(isset($collection['a.b.a']));
        $this->assertTrue(isset($collection['a.b.b']));
        $this->assertTrue(isset($collection['a.b.c']));
        $this->assertFalse(isset($collection['a.b.d']));
        $this->assertFalse(isset($collection['a.a']));
        unset($collection['a.b.c']);
        $this->assertFalse(isset($collection['a.b.c']));
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetGet()
     */
    public function testOffsetGet()
    {
        $collection = new Collection(['a' => 'b']);
        $this->assertSame('b', $collection['a']);
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetSet()
     *
     */
    public function testOffsetSet()
    {
        $collection = new Collection();
        $this->assertCount(0, $collection);
        $collection[] = 1;
        $collection[] = 2;
        $collection[] = 3;
        $collection['test'] = 4;
        $collection[2] = 99;
        $this->assertCount(4, $collection);
        $this->assertSame(1, $collection[0]);
        $this->assertSame(2, $collection[1]);
        $this->assertSame(99, $collection[2]);
        $this->assertSame(4, $collection['test']);
    }

    /**
     * @covers \Barogue\Collections\Collection::pop()
     */
    public function testPop()
    {
        // Pop <= 1
        $collection = new Collection([1, 2, 3, 4, 5]);
        $popped = $collection->pop(0);
        $this->assertSame(null, $popped);
        $this->assertSame([1, 2, 3, 4, 5], $collection->getArray());

        // Pop more than in the collection
        $collection = new Collection([1, 2, 3]);
        $popped = $collection->pop(5);
        $this->assertSame([3, 2, 1, null, null], $popped->getArray());
        $this->assertSame([], $collection->getArray());

        // Pop 1
        $collection = new Collection([1, 2, 3, 4, 5]);
        $popped = $collection->pop();
        $this->assertSame(5, $popped);
        $this->assertSame([1, 2, 3, 4], $collection->getArray());

        // Pop most
        $collection = new Collection([1, 2, 3, 4, 5]);
        $popped = $collection->pop(3);
        $this->assertSame([5, 4, 3], $popped->getArray());
        $this->assertSame([1, 2], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::push()
     */
    public function testPush()
    {
        $collection = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $collection->push('test');
        $this->assertCount(4, $collection);
        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            0 => 'test'
        ], $collection->getArray());

        $collection->push('hello', 'how', 'are', ['you' => '?']);
        $this->assertCount(8, $collection);
        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            0 => 'test',
            1 => 'hello',
            2 => 'how',
            3 => 'are',
            4 => [
                'you' => '?'
            ],
        ], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::randomKey()
     */
    public function testRandomKey()
    {
        srand(1);
        $this->assertSame('dog', Collection::instance(['cat' => 1, 'dog' => 2, 'bird' => 3])->randomKey());
    }

    /**
     * @covers \Barogue\Collections\Collection::randomKeys()
     */
    public function testRandomKeys()
    {
        srand(1);
        $this->assertSame(['cat', 'bird'], Collection::instance(['cat' => 1, 'dog' => 2, 'bird' => 3])->randomKeys(2)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::range()
     */
    public function testRange()
    {
        $this->assertSame(range(1, 100), Collection::range(1, 100)->getArray());
        $this->assertSame(range(1, 100, 20), Collection::range(1, 100, 20)->getArray());
        $this->assertSame(range('a', 'z'), Collection::range('a', 'z')->getArray());
        $this->assertSame([0, 2, 4, 6, 8, 10], Collection::range(0, 10, 2)->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::reduce()
     * @covers \Barogue\Collections\Collection::factorial()
     */
    public function testReduce()
    {
        // Without an initial value
        $this->assertSame(15, Collection::instance([1, 2, 3, 4, 5])->reduce(fn ($carry, $value) => $carry + $value));

        // With an initial value
        $this->assertSame(5, Collection::instance([1, 2, 3, 4, 5])->reduce(fn ($carry, $value) => $carry + $value, -10));

        // 10 factorial
        $this->assertSame(3628800, Collection::range(10, 1)->reduce(fn ($carry, $value) => $carry * $value, 1));
        $this->assertSame(3628800, Collection::factorial(10));
    }

    /**
     * @covers \Barogue\Collections\Collection::reverse()
     */
    public function testReverse()
    {
        $collection = new Collection(['a' => 1, 1 => 2, 'c' => 'd']);
        $reversed = $collection->reverse();
        $this->assertSame(['c' => 'd', 1 => 2, 'a' => 1], $reversed->getArray());
        $this->assertSame(['a' => 1, 1 => 2, 'c' => 'd'], $collection->getArray());
        $this->assertNotSame($collection, $reversed);
    }

    /**
     * @covers \Barogue\Collections\Collection::reverseSort()
     */
    public function testReverseSort()
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $sorted = $collection->reverseSort();
        $this->assertSame([4 => 5, 3 => 4, 2 => 3, 1 => 2, 0 => 1], $sorted->getArray());
        $this->assertSame([5, 4, 3, 2, 1], $sorted->values()->getArray());
        $this->assertSame([1, 2, 3, 4, 5], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetSet()
     */
    public function testSetNestedKey()
    {
        $collection = new Collection();
        $collection['a.b.a'] = 10;
        $collection['a.b.b'] = 10;
        $collection['a.b.c'] = 10;
        $this->assertSame([
            'a' => [
                'b' => [
                    'a' => 10,
                    'b' => 10,
                    'c' => 10,
                ]
            ]
        ], iterator_to_array($collection));
    }

    /**
     * @covers \Barogue\Collections\Collection::shift()
     */
    public function testShift()
    {
        // Shift <= 1
        $collection = new Collection([1, 2, 3, 4, 5]);
        $shifted = $collection->shift(0);
        $this->assertSame(null, $shifted);
        $this->assertSame([1, 2, 3, 4, 5], $collection->getArray());

        // Shift more than in the collection
        $collection = new Collection([1, 2, 3]);
        $shifted = $collection->shift(5);
        $this->assertSame([1, 2, 3, null, null], $shifted->getArray());
        $this->assertSame([], $collection->getArray());

        // Shift 1
        $collection = new Collection([1, 2, 3, 4, 5]);
        $shifted = $collection->shift();
        $this->assertSame(1, $shifted);
        $this->assertSame([2, 3, 4, 5], $collection->getArray());

        // Shift most
        $collection = new Collection([1, 2, 3, 4, 5]);
        $shifted = $collection->shift(3);
        $this->assertSame([1, 2, 3], $shifted->getArray());
        $this->assertSame([4, 5], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::shuffle()
     */
    public function testShuffle()
    {
        $collection = new Collection([
            'a' => 5,
            'b' => 4,
            'c' => 3,
            'd' => 2,
            'e' => 1,
        ]);
        srand(1);
        $this->assertSame([
            'b' => 4,
            'c' => 3,
            'e' => 1,
            'd' => 2,
            'a' => 5,
        ], $collection->shuffle()->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::sort()
     */
    public function testSort()
    {
        $collection = new Collection([5, 4, 3, 2, 1]);
        $sorted = $collection->sort();
        $this->assertSame([4 => 1, 3 => 2, 2 => 3, 1 => 4, 0 => 5], $sorted->getArray());
        $this->assertSame([1, 2, 3, 4, 5], $sorted->values()->getArray());
        $this->assertSame([5, 4, 3, 2, 1], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::sortKeys()
     */
    public function testSortKeys()
    {
        $collection = new Collection(['a' => 5, 'c' => 4, 'z' => 3, 'b' => 2, 'e' => 1]);
        $sorted = $collection->sortKeys();
        $this->assertSame(['a' => 5, 'b' => 2, 'c' => 4, 'e' => 1, 'z' => 3], $sorted->getArray());
        $this->assertSame([5, 2, 4, 1, 3], $sorted->values()->getArray());
        $this->assertSame(['a' => 5, 'c' => 4, 'z' => 3, 'b' => 2, 'e' => 1], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::sortKeysCallback()
     */
    public function testSortKeysWithCallback()
    {
        $collection = new Collection([5, 4, 3, 2, 1]);
        $sorted = $collection->sortKeysCallback(function ($a, $b) {
            $aEven = $a % 2 == 0 ? 1 : 0;
            $bEven = $b % 2 == 0 ? 1 : 0;
            return $aEven === $bEven ? $a <=> $b : $aEven <=> $bEven;
        });
        $this->assertSame([1 => 4, 3 => 2, 0 => 5, 2 => 3, 4 => 1], $sorted->getArray());
        $this->assertSame([4, 2, 5, 3, 1], $sorted->values()->getArray());
        $this->assertSame([5, 4, 3, 2, 1], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::sortCallback()
     */
    public function testSortWithCallback()
    {
        $collection = new Collection([5, 4, 3, 2, 1]);
        $sorted = $collection->sortCallback(function ($a, $b) {
            $aEven = $a % 2 == 0 ? 1 : 0;
            $bEven = $b % 2 == 0 ? 1 : 0;
            return $aEven === $bEven ? $a <=> $b : $aEven <=> $bEven;
        });
        $this->assertSame([4 => 1, 2 => 3, 0 => 5, 3 => 2, 1 => 4], $sorted->getArray());
        $this->assertSame([1, 3, 5, 2, 4], $sorted->values()->getArray());
        $this->assertSame([5, 4, 3, 2, 1], $collection->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::sum()
     */
    public function testSum()
    {
        $this->assertSame(6, Collection::instance([1, 2, 3])->sum());
        $this->assertSame(5050, Collection::range(1, 100)->sum());
        $this->assertSame(3, Collection::instance([1, 2, 'a'])->sum());
    }

    /**
     * @covers \Barogue\Collections\Collection::offsetUnset()
     */
    public function testUnset()
    {
        $collection = new Collection(['a' => 'b']);
        $this->assertTrue(isset($collection['a']));
        $this->assertFalse(isset($collection['b']));
        unset($collection['a']);
        $this->assertFalse(isset($collection['a']));
    }

    /**
     * @covers \Barogue\Collections\Collection::values()
     */
    public function testValues()
    {
        $collection = new Collection([
            'a' => 5,
            'b' => 4
        ]);
        $this->assertSame([5, 4], $collection->values()->getArray());
    }

    /**
     * @covers \Barogue\Collections\Collection::__construct()
     */
    public function testWorksWithList()
    {
        $collection = new Collection(['coffee', 'brown', 'caffeine']);
        list($drink, $color, $power) = $collection;
        $this->assertSame('coffee', $drink);
        $this->assertSame('brown', $color);
        $this->assertSame('caffeine', $power);
    }
}
