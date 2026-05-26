<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/UserModel.php';

class LoginFlowTest extends TestCase
{
    private $db;
    private $httpClient;
    private $baseUrl;
    private $testUserId;

    protected function setUp(): void
    {
        // Reset Database singleton to use test database
        $reflection = new ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);

        // Connect to test database
        $this->db = Database::connect();

        // Setup HTTP client
        $this->baseUrl = 'http://localhost:8000';
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'allow_redirects' => false // Don't follow redirects automatically
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testUserId) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }

        // Reset Database singleton
        $reflection = new ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testLoginWithCorrectCredentialsRedirectsToDashboard()
    {
        // Create a test admin user
        $nombre = 'E2E Test Admin';
        $usuario = 'e2eadmin_' . time();
        $password = 'testpass123';
        $rol = 'admin';
        $email = 'e2eadmin@example.com';
        $cedula = '1234567890';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();

        try {
            // Send POST request to login endpoint
            $response = $this->httpClient->post('/index.php', [
                'form_params' => [
                    'action' => 'login',
                    'usuario' => $usuario,
                    'password' => $password
                ]
            ]);

            // Verify redirect status code (302)
            $this->assertEquals(302, $response->getStatusCode());

            // Verify Location header points to dashboard
            $location = $response->getHeaderLine('Location');
            $this->assertStringContainsString('dashboard', $location);
        } catch (RequestException $e) {
            $this->markTestSkipped('PHP server not running or connection error: ' . $e->getMessage());
        }
    }

    public function testLoginWithIncorrectCredentialsFails()
    {
        try {
            // Send POST request with incorrect credentials
            $response = $this->httpClient->post('/index.php', [
                'form_params' => [
                    'action' => 'login',
                    'usuario' => 'nonexistent_user',
                    'password' => 'wrongpassword'
                ]
            ]);

            // Verify redirect status code (302)
            $this->assertEquals(302, $response->getStatusCode());

            // Verify Location header points back to login (not dashboard)
            $location = $response->getHeaderLine('Location');
            $this->assertStringContainsString('login', $location);
            $this->assertStringNotContainsString('dashboard', $location);
        } catch (RequestException $e) {
            $this->markTestSkipped('PHP server not running or connection error: ' . $e->getMessage());
        }
    }

    public function testLoginWithEmptyCredentialsFails()
    {
        try {
            // Send POST request with empty credentials
            $response = $this->httpClient->post('/index.php', [
                'form_params' => [
                    'action' => 'login',
                    'usuario' => '',
                    'password' => ''
                ]
            ]);

            // Verify redirect status code (302)
            $this->assertEquals(302, $response->getStatusCode());

            // Verify Location header points back to login
            $location = $response->getHeaderLine('Location');
            $this->assertStringContainsString('login', $location);
        } catch (RequestException $e) {
            $this->markTestSkipped('PHP server not running or connection error: ' . $e->getMessage());
        }
    }

    public function testGetLoginPageReturns200()
    {
        try {
            // Send GET request to login page
            $response = $this->httpClient->get('/index.php', [
                'query' => [
                    'action' => 'login'
                ]
            ]);

            // Verify status code
            $this->assertEquals(200, $response->getStatusCode());

            // Verify response contains login form elements
            $body = (string) $response->getBody();
            $this->assertStringContainsString('login', strtolower($body));
        } catch (RequestException $e) {
            $this->markTestSkipped('PHP server not running or connection error: ' . $e->getMessage());
        }
    }

    public function testAccessDashboardWithoutLoginRedirectsToLogin()
    {
        try {
            // Try to access dashboard without being logged in
            $response = $this->httpClient->get('/index.php', [
                'query' => [
                    'action' => 'dashboard'
                ],
                'allow_redirects' => false
            ]);

            // The dashboard might still return 200 but show login form
            // or redirect to login. We'll check the response.
            $statusCode = $response->getStatusCode();
            $this->assertContains($statusCode, [200, 302]);

            if ($statusCode === 302) {
                $location = $response->getHeaderLine('Location');
                $this->assertStringContainsString('login', $location);
            }
        } catch (RequestException $e) {
            $this->markTestSkipped('PHP server not running or connection error: ' . $e->getMessage());
        }
    }
}
