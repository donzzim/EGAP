<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\MobileAuthController;
use App\Models\User;
use App\Models\UserMobile;
use App\Services\UsersConnectionService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class MobileAuthControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_me_does_not_return_a_plain_text_token_field(): void
    {
        $authenticatedUser = new User;
        $request = Request::create('/mobile-api/me');
        $request->setUserResolver(fn (): User => $authenticatedUser);

        $mobileUser = Mockery::mock(UserMobile::class);
        $mobileUser->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'id' => 9422,
                'name' => 'Mobile Local Teste',
                'token' => null,
            ]);

        $usersConnectionService = Mockery::mock(UsersConnectionService::class);
        $usersConnectionService->shouldReceive('findByUser')
            ->once()
            ->with($authenticatedUser)
            ->andReturn($mobileUser);

        $response = (new MobileAuthController)->me($request, $usersConnectionService);
        $data = $response->getData(true);

        $this->assertSame(9422, $data['user']['id']);
        $this->assertArrayNotHasKey('token', $data['user']);
    }
}
