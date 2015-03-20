<?php

// Based on https://github.com/laracasts/TestDummy/blob/master/src/DbTestCase.php

namespace Lasallecms\Helpers\Tests;

use TestCase, Artisan, DB;

/**
 * Helper parent class for Laravel users.
 * Extend this class from your test classes.
 */
class DbTestCase extends TestCase
{

    /**
     * Setup the DB before each test.
     */
    public function setUp()
    {

echo "\n\rsetUp()...";
        parent::setUp();

        // This should only do work for Sqlite DBs in memory.
echo "\n\rphp artisan migrate:refresh...";
        //Artisan::call('migrate:refresh');

        // We'll run all tests through a transaction,
        // and then rollback afterward.
        DB::beginTransaction();
    }

    /**
     * Rollback transactions after each test.
     */
    public function tearDown()
    {
        DB::rollback();

        parent::tearDown();
    }

}
