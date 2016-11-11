<?php

class PersonalAccessTokenFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_access_token_can_be_created()
    {
        $server = Mockery::mock('League\OAuth2\Server\AuthorizationServer');
        $clients = Mockery::mock('Masdevs\Passenger\ClientRepository');
        $tokens = Mockery::mock('Masdevs\Passenger\TokenRepository');
        $jwt = Mockery::mock('Lcobucci\JWT\Parser');

        $factory = new Masdevs\Passenger\PersonalAccessTokenFactory($server, $clients, $tokens, $jwt);

        $clients->shouldReceive('personalAccessClient')->andReturn($client = new PersonalAccessTokenFactoryTestClientStub);
        $server->shouldReceive('respondToAccessTokenRequest')->andReturn($response = Mockery::mock());
        $response->shouldReceive('getBody->__toString')->andReturn(json_encode([
            'access_token' => 'foo',
        ]));

        $jwt->shouldReceive('parse')->with('foo')->andReturn($parsedToken = Mockery::mock());
        $parsedToken->shouldReceive('getClaim')->with('jti')->andReturn('token');
        $tokens->shouldReceive('find')->with('token')->andReturn($foundToken = new PersonalAccessTokenFactoryTestModelStub);
        $tokens->shouldReceive('save')->with($foundToken);

        $result = $factory->make(1, 'token', ['scopes']);

        $this->assertInstanceOf('Masdevs\Passenger\PersonalAccessTokenResult', $result);
    }
}

class PersonalAccessTokenFactoryTestClientStub
{
    public $id = 1;
    public $secret = 'something';
}

class PersonalAccessTokenFactoryTestModelStub extends Masdevs\Passenger\Token
{
    public $id = 1;
    public $secret = 'something';
}
