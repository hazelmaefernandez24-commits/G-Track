<?php

namespace Tests\Unit;

use App\Support\IpAddressMasker;
use PHPUnit\Framework\TestCase;

class IpAddressMaskerTest extends TestCase
{
    public function test_masks_ipv4_addresses_inside_signal_text(): void
    {
        $this->assertSame(
            'WiFi - Good | IP: 192.***.*.*09',
            IpAddressMasker::maskInText('WiFi - Good | IP: 192.168.0.109')
        );
    }

    public function test_leaves_non_ipv4_text_unchanged(): void
    {
        $text = 'WiFi - Good | IP: 999.168.0.109';

        $this->assertSame($text, IpAddressMasker::maskInText($text));
        $this->assertNull(IpAddressMasker::maskInText(null));
    }
}
