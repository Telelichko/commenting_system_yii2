<?php

declare(strict_types=1);

namespace common\tests\unit\models;

use common\models\User;
use Codeception\Test\Unit;
use Yii;
use yii\base\NotSupportedException;

/**
 * Unit tests for the User model.
 *
 * These tests verify isolated logic without database access.
 * Methods that require DB (findIdentity, findByUsername, etc.) are covered by Functional tests.
 */
class UserTest extends Unit
{
    /**
     * @var User
     */
    protected User $model;

    /**
     * Set up the test fixture before each test.
     */
    protected function _before(): void
    {
        $this->model = new User();
        
        // Ensure the password reset token expiration parameter is set
        if (!isset(Yii::$app->params['user.passwordResetTokenExpire'])) {
            Yii::$app->params['user.passwordResetTokenExpire'] = 3600; // 1 hour default
        }
    }

    // =========================================================================
    // Constants
    // =========================================================================

    /**
     * Test that status constants have expected values.
     */
    public function testStatusConstants(): void
    {
        $this->assertSame(0, User::STATUS_DELETED);
        $this->assertSame(9, User::STATUS_INACTIVE);
        $this->assertSame(10, User::STATUS_ACTIVE);
    }

    // =========================================================================
    // isPasswordResetTokenValid
    // =========================================================================

    /**
     * Test that a valid (non-expired) token is accepted.
     */
    public function testIsPasswordResetTokenValidWithValidToken(): void
    {
        // Create a token with current timestamp (just created, not expired)
        $token = Yii::$app->security->generateRandomString() . '_' . time();
        
        $this->assertTrue(User::isPasswordResetTokenValid($token));
    }

    /**
     * Test that an expired token is rejected.
     */
    public function testIsPasswordResetTokenValidWithExpiredToken(): void
    {
        // Create a token with a timestamp far in the past
        $expiredTimestamp = time() - 7200; // 2 hours ago
        $token = Yii::$app->security->generateRandomString() . '_' . $expiredTimestamp;
        
        $this->assertFalse(User::isPasswordResetTokenValid($token));
    }

    /**
     * Test that an empty string token is rejected.
     */
    public function testIsPasswordResetTokenValidWithEmptyToken(): void
    {
        $this->assertFalse(User::isPasswordResetTokenValid(''));
    }

    /**
     * Test that a null token is rejected.
     */
    public function testIsPasswordResetTokenValidWithNullToken(): void
    {
        $this->assertFalse(User::isPasswordResetTokenValid(null));
    }

    /**
     * Test that a token without timestamp separator is rejected.
     */
    public function testIsPasswordResetTokenValidWithMalformedToken(): void
    {
        // Token without the underscore separator
        $this->assertFalse(User::isPasswordResetTokenValid('invalidtoken'));
    }

    // =========================================================================
    // Password handling
    // =========================================================================

    /**
     * Test that setPassword generates a non-empty hash.
     */
    public function testSetPasswordGeneratesHash(): void
    {
        $password = 'test_password_123';
        
        $this->model->setPassword($password);
        
        $this->assertNotEmpty($this->model->password_hash);
        $this->assertNotSame($password, $this->model->password_hash);
    }

    /**
     * Test that validatePassword returns true for correct password.
     */
    public function testValidatePasswordWithCorrectPassword(): void
    {
        $password = 'correct_password';
        $this->model->setPassword($password);
        
        $this->assertTrue($this->model->validatePassword($password));
    }

    /**
     * Test that validatePassword returns false for incorrect password.
     */
    public function testValidatePasswordWithIncorrectPassword(): void
    {
        $this->model->setPassword('correct_password');
        
        $this->assertFalse($this->model->validatePassword('wrong_password'));
    }

    /**
     * Test that the same password generates different hashes (salted).
     */
    public function testPasswordHashIsSalted(): void
    {
        $password = 'same_password';
        
        $this->model->setPassword($password);
        $hash1 = $this->model->password_hash;
        
        $this->model->setPassword($password);
        $hash2 = $this->model->password_hash;
        
        $this->assertNotSame($hash1, $hash2);
    }

    // =========================================================================
    // Auth key
    // =========================================================================

    /**
     * Test that generateAuthKey creates a non-empty random string.
     */
    public function testGenerateAuthKey(): void
    {
        $this->assertEmpty($this->model->auth_key);
        
        $this->model->generateAuthKey();
        
        $this->assertNotEmpty($this->model->auth_key);
    }

    /**
     * Test that getAuthKey returns the stored auth key.
     */
    public function testGetAuthKey(): void
    {
        $this->model->auth_key = 'test_auth_key_123';
        
        $this->assertSame('test_auth_key_123', $this->model->getAuthKey());
    }

    /**
     * Test that validateAuthKey returns true for matching key.
     */
    public function testValidateAuthKeyWithMatchingKey(): void
    {
        $this->model->auth_key = 'test_auth_key';
        
        $this->assertTrue($this->model->validateAuthKey('test_auth_key'));
    }

    /**
     * Test that validateAuthKey returns false for non-matching key.
     */
    public function testValidateAuthKeyWithNonMatchingKey(): void
    {
        $this->model->auth_key = 'test_auth_key';
        
        $this->assertFalse($this->model->validateAuthKey('wrong_key'));
    }

    // =========================================================================
    // Password reset token
    // =========================================================================

    /**
     * Test that generatePasswordResetToken creates a token with timestamp.
     */
    public function testGeneratePasswordResetToken(): void
    {
        $beforeTime = time();
        $this->model->generatePasswordResetToken();
        $afterTime = time();
        
        $this->assertNotEmpty($this->model->password_reset_token);
        $this->assertStringContainsString('_', $this->model->password_reset_token);
        
        // Extract timestamp from token and verify it's within expected range
        $parts = explode('_', $this->model->password_reset_token);
        $timestamp = (int) end($parts);
        
        $this->assertGreaterThanOrEqual($beforeTime, $timestamp);
        $this->assertLessThanOrEqual($afterTime, $timestamp);
    }

    /**
     * Test that removePasswordResetToken sets the token to null.
     */
    public function testRemovePasswordResetToken(): void
    {
        $this->model->password_reset_token = 'some_token_123';
        
        $this->model->removePasswordResetToken();
        
        $this->assertNull($this->model->password_reset_token);
    }

    // =========================================================================
    // Email verification token
    // =========================================================================

    /**
     * Test that generateEmailVerificationToken creates a token with timestamp.
     */
    public function testGenerateEmailVerificationToken(): void
    {
        $beforeTime = time();
        $this->model->generateEmailVerificationToken();
        $afterTime = time();
        
        $this->assertNotEmpty($this->model->verification_token);
        $this->assertStringContainsString('_', $this->model->verification_token);
        
        // Extract timestamp and verify it's within expected range
        $parts = explode('_', $this->model->verification_token);
        $timestamp = (int) end($parts);
        
        $this->assertGreaterThanOrEqual($beforeTime, $timestamp);
        $this->assertLessThanOrEqual($afterTime, $timestamp);
    }

    // =========================================================================
    // IdentityInterface methods
    // =========================================================================

    /**
     * Test that findIdentityByAccessToken throws NotSupportedException.
     */
    public function testFindIdentityByAccessTokenThrowsException(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('"findIdentityByAccessToken" is not implemented.');
        
        User::findIdentityByAccessToken('some_token');
    }

    /**
     * Test that getId returns the primary key.
     */
    public function testGetIdReturnsPrimaryKey(): void
    {
        $this->model->id = 42;
        
        $this->assertSame(42, $this->model->getId());
    }

    // =========================================================================
    // Table name and behaviors
    // =========================================================================

    /**
     * Test that tableName returns the correct table name.
     */
    public function testTableName(): void
    {
        $this->assertSame('{{%user}}', User::tableName());
    }

    /**
     * Test that behaviors include TimestampBehavior.
     */
    public function testBehaviorsIncludeTimestampBehavior(): void
    {
        $behaviors = $this->model->behaviors();
        
        $this->assertContains(\yii\behaviors\TimestampBehavior::class, $behaviors);
    }

    // =========================================================================
    // Validation rules
    // =========================================================================

    /**
     * Test that rules array is not empty and contains expected validators.
     */
    public function testRulesAreDefined(): void
    {
        $rules = $this->model->rules();
        
        $this->assertNotEmpty($rules);
        $this->assertIsArray($rules);
    }
}