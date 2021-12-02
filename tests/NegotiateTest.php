<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Negotiate\Exceptions\NegotiateException,
    Fyre\Negotiate\Negotiate,
    PHPUnit\Framework\TestCase;

final class NegotiateTest extends TestCase
{

    public function testNegotiateContent(): void
    {
        $this->assertEquals(
            'text/html',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/html'])
        );
    }

    public function testNegotiateContentMultiple(): void
    {
        $this->assertEquals(
            'text/html',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['application/xml', 'text/html'])
        );
    }

    public function testNegotiateContentParams(): void
    {
        $this->assertEquals(
            'appliation/signed-exchange',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/plain', 'appliation/signed-exchange;v=b3'])
        );
    }

    public function testNegotiateContentParamsNotMatch(): void
    {
        $this->assertEquals(
            'text/plain',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/plain', 'appliation/signed-exchange;v=b2'])
        );
    }

    public function testNegotiateContentParamsDefault(): void
    {
        $this->assertEquals(
            'text/plain',
            Negotiate::content('text/html', ['text/plain'])
        );
    }

    public function testNegotiateContentEmpty(): void
    {
        $this->expectException(NegotiateException::class);
    
        Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', []);
    }

    public function testNegotiateEncoding(): void
    {
        $this->assertEquals(
            'deflate',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['deflate'])
        );
    }

    public function testNegotiateEncodingMultiple(): void
    {
        $this->assertEquals(
            'deflate',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['gzip', 'deflate'])
        );
    }

    public function testNegotiateEncodingQuality(): void
    {
        $this->assertEquals(
            'gzip',
            Negotiate::encoding('deflate;q=0.9, gzip, *;q=0.5', ['gzip', 'deflate'])
        );
    }

    public function testNegotiateEncodingDefault(): void
    {
        $this->assertEquals(
            'any',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['any'])
        );
    }

    public function testNegotiateEncodingEmpty(): void
    {
        $this->assertEquals(
            'identity',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', [])
        );
    }

    public function testNegotiateLanguage(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', ['en-GB'])
        );
    }

    public function testNegotiateLanguageMultiple(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', ['en-GB', 'en-US', 'en'])
        );
    }

    public function testNegotiateLanguageQuality(): void
    {
        $this->assertEquals(
            'en-US',
            Negotiate::language('ru-RU;q=0.9,en-US,en;q=0.8', ['ru-RU', 'en-US', 'en'])
        );
    }

    public function testNegotiateLanguageLocales(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('ru-RU;q=0.9,en-US,en;q=0.8', ['ru-RU', 'en-GB', 'en'])
        );
    }

    public function testNegotiateLanguageEmpty(): void
    {
        $this->expectException(NegotiateException::class);
    
        Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', []);
    }

}
