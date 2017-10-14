<?php

namespace Lampager\Laravel\Tests;

use NilPortugues\Sql\QueryFormatter\Formatter;
use Orchestra\Testbench\TestCase as BaseTestCase;

class MySQLGrammerTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Skip SQLite setup and use default MySQL configuration
    }

    protected function setUp()
    {
        // Skip SQLite setup and use default MySQL configuration
        BaseTestCase::setUp();
    }

    /**
     * @param $expected
     * @param $actual
     */
    protected function assertSqlEquals($expected, $actual)
    {
        $formatter = new Formatter();
        $this->assertEquals($formatter->format($expected), $formatter->format($actual));
    }

    /**
     * @test
     */
    public function testAscendingForwardStart()
    {
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            select * from `posts`
            where `user_id` = ? 
            order by `updated_at` asc, `created_at` asc, `id` asc
            limit 4
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testAscendingForwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testAscendingForwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testAscendingBackwardStart()
    {
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            select * from `posts`
            where `user_id` = ?
            order by `updated_at` desc, `created_at` desc, `id` desc
            limit 4
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testAscendingBackwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testAscendingBackwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingForwardStart()
    {
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            select * from `posts`
            where `user_id` = ?
            order by `updated_at` desc, `created_at` desc, `id` desc
            limit 4
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingForwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingForwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->forward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingBackwardStart()
    {
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            select * from `posts`
            where `user_id` = ?
            order by `updated_at` asc, `created_at` asc, `id` asc
            limit 4
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingBackwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 4
            )
        ', $builder->toSql());
    }

    /**
     * @test
     */
    public function testDescendingBackwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = Post::whereUserId(2)
            ->lampager()
            ->backward()->limit(3)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                order by `updated_at` desc, `created_at` desc, `id` desc
                limit 1
            )
            union all
            (
                select * from `posts`
                where `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                order by `updated_at` asc, `created_at` asc, `id` asc
                limit 4
            )
        ', $builder->toSql());
    }
}
