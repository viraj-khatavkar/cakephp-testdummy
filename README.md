# Cakephp Testdummy

Testdummy provides an easy way of creating random test data. While writing tests you would require random/fake data to run your tests.

Testdummy helps you to create a random set of fake data which you can configure exactly according to your needs in the test.

## Step 1: Installation

Install this package using Composer:

```bash
composer require viraj/cakephp-testdummy
```

## Step 2: Create a factories file

Within the `config/Factories` directory, create a `TableFactory.php` file with the following contents:

```php
# config/TableFactory.php

<?php

$factory = \TestDummy\Definition::getInstance();
```

Within a `config/Factories` directory, you may create any number of PHP files that will automatically be loaded by our package.

## Step 3: Write a factory

Before using factories, you must define them in the above file. An example factory definition would look like this:

```php
<?php

$factory = \TestDummy\Definition::getInstance();

$factory->define('Posts', function (Faker\Generator $faker) {
    return [
        'title'     => $faker->sentence,
        'author'    => $faker->name,
        'body'      => $faker->paragraph,
        'published' => true,
    ];
});
```

In `TableFactory.php` you will have access to `$faker` variable which is an instance of the `Generator` class in the Faker package. Using Faker, you can create random data of various types and even get values which are local to your country. Please [read the documentation](https://github.com/fzaninotto/Faker) of Faker to understand their API.

## Step 4: Using Factories

To use factories, your tests need to extend the `\TestDummy\BaseTestCase`. This class extends the `IntegrationTestCase` present in CakePHP core, so you get access to all the core features and assertions.

Now, you can use your defined factories in the tests:

```php
/** @test */
public function user_can_edit_a_post()
{
    $post = factory('Posts')->create();

    $this->post('/posts/edit/' . $post->id, [
        'title'     => 'Updated Post',
        'author'    => 'Updated Author',
        'body'      => 'Updated Random body text',
        'published' => true,
    ]);
        
    //Your assertions

}
```

## Step 5: Database Migrations

Fixtures create tables before every test and drop them after every tests. When using fixtures, you would need to define fixture files, plus import or configure the names of fixtures to be used in every test.

Alternatively, you can use the `DatabaseMigrations` trait which will basically migrate your database before every test and delete all the tables after every test. Here is an example of how to do this:

```php

namespace App\Test\TestCase;


use TestDummy\Traits\DatabaseMigrations;

class ViewPostListTest extends BaseTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_see_published_posts()
    {
        factory('Posts')->create(['title' => 'Nate Emmons post']);
        factory('Posts')->create(['title' => 'Megan Danz post']);

        $this->get('/posts');
        $this->assertResponseContains('Nate Emmons post');
        $this->assertResponseContains('Megan Danz post');
    }

    /** @test */
    public function user_cannot_see_unpublished_posts()
    {
        factory('Posts')->create(['title' => 'Nate Emmons post', 'published' => false]);
        factory('Posts')->create(['title' => 'Megan Danz post', 'published' => false]);

        $this->get('/posts');
        $this->assertResponseNotContains('Nate Emmons post');
        $this->assertResponseNotContains('Megan Danz post');
    }
}
```

## Overriding attributes

You can even override specific attributes in your tests while using factories:

```php
factory('Posts')->create(['title' => 'Your custom title']);
```

The above code will generate a post record in the database with the above title and fake data for other fields.

## Collection of Factories

If you want to create a collection of 100 posts, you can do so by using the following syntax:

```php
$posts = factory('Posts', 100)->create();
```

The above code will create 100 post records and return a `Cake\Collection\Collection` instance containing 100 posts
