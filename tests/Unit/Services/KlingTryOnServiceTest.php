<?php

namespace Tests\Unit\Services;

use App\Services\KlingApi\KlingAuthService;
use App\Services\KlingApi\KlingTryOnService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KlingTryOnServiceTest extends TestCase
{
    public function test_submit_tryon_sends_model_name_v1_5(): void
    {
        Http::fake([
            '*/v1/images/kolors-virtual-try-on' => Http::response([
                'data' => [
                    'task_id' => 'test-task-123',
                    'task_status' => 'submitted',
                ],
            ]),
        ]);

        Storage::fake('public');
        // Create a minimal valid JPEG image
        $image = imagecreatetruecolor(300, 400);
        ob_start();
        imagejpeg($image, null, 90);
        $jpegData = ob_get_clean();
        imagedestroy($image);
        Storage::disk('public')->put('model.jpg', $jpegData);
        Storage::disk('public')->put('garment.jpg', $jpegData);

        $authService = $this->mock(KlingAuthService::class, function ($mock) {
            $mock->shouldReceive('generateToken')->andReturn('fake-token');
        });

        $service = new KlingTryOnService($authService);
        $result = $service->submitTryOn('model.jpg', 'garment.jpg');

        $this->assertEquals('test-task-123', $result['task_id']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $body['model_name'] === 'kolors-virtual-try-on-v1-5'
                && isset($body['human_image'])
                && isset($body['cloth_image']);
        });
    }

    public function test_submit_tryon_model_name_is_configurable(): void
    {
        config(['services.kling.tryon_model' => 'kolors-virtual-try-on-v1']);

        Http::fake([
            '*/v1/images/kolors-virtual-try-on' => Http::response([
                'data' => [
                    'task_id' => 'test-task-456',
                    'task_status' => 'submitted',
                ],
            ]),
        ]);

        Storage::fake('public');
        $image = imagecreatetruecolor(300, 400);
        ob_start();
        imagejpeg($image, null, 90);
        $jpegData = ob_get_clean();
        imagedestroy($image);
        Storage::disk('public')->put('model.jpg', $jpegData);
        Storage::disk('public')->put('garment.jpg', $jpegData);

        $authService = $this->mock(KlingAuthService::class, function ($mock) {
            $mock->shouldReceive('generateToken')->andReturn('fake-token');
        });

        $service = new KlingTryOnService($authService);
        $result = $service->submitTryOn('model.jpg', 'garment.jpg');

        Http::assertSent(function ($request) {
            return $request->data()['model_name'] === 'kolors-virtual-try-on-v1';
        });
    }

    public function test_get_task_status_returns_correct_structure(): void
    {
        Http::fake([
            '*/v1/images/kolors-virtual-try-on/*' => Http::response([
                'data' => [
                    'task_status' => 'succeed',
                    'task_result' => [
                        'images' => [
                            ['url' => 'https://example.com/result.jpg'],
                        ],
                    ],
                    'task_status_msg' => null,
                ],
            ]),
        ]);

        $authService = $this->mock(KlingAuthService::class, function ($mock) {
            $mock->shouldReceive('generateToken')->andReturn('fake-token');
        });

        $service = new KlingTryOnService($authService);
        $result = $service->getTaskStatus('test-task-123');

        $this->assertEquals('succeed', $result['status']);
        $this->assertCount(1, $result['images']);
        $this->assertEquals('https://example.com/result.jpg', $result['images'][0]['url']);
    }
}
