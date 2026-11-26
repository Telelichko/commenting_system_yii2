<?php

namespace common\tests\unit\models;

use common\models\Comment;
use Codeception\Test\Unit;

class CommentTest extends Unit
{
    /**
     * Test successful validation with valid data
     */
    public function testValidationSuccess()
    {
        $model = new Comment([
            'author_name' => 'John Doe',
            'text' => 'This is a great comment!',
        ]);

        $this->assertTrue($model->validate());
        $this->assertEmpty($model->errors);
    }

    /**
     * Test validation failure when text is empty
     */
    public function testValidationFailsOnEmptyText()
    {
        $model = new Comment([
            'author_name' => 'John Doe',
            'text' => '',
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('text', $model->errors);
    }

    /**
     * Test validation failure when author name is empty
     */
    public function testValidationFailsOnEmptyAuthor()
    {
        $model = new Comment([
            'author_name' => '',
            'text' => 'Some text here',
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('author_name', $model->errors);
    }

    /**
     * Test validation failure when author name exceeds 100 characters
     */
    public function testValidationFailsOnLongAuthorName()
    {
        $model = new Comment([
            'author_name' => str_repeat('a', 101),
            'text' => 'Some text',
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('author_name', $model->errors);
    }
}