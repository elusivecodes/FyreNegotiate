<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Http\Negotiate,
    InvalidArgumentException,
    PHPUnit\Framework\TestCase;

final class NegotiateTest extends TestCase
{

    public function testContent(): void
    {
        $this->assertEquals(
            'text/html',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/html'])
        );
    }

    public function testContentMultiple(): void
    {
        $this->assertEquals(
            'text/html',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['application/xml', 'text/html'])
        );
    }

    public function testContentParams(): void
    {
        $this->assertEquals(
            'appliation/signed-exchange',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/plain', 'appliation/signed-exchange;v=b3'])
        );
    }

    public function testContentParamsNotMatch(): void
    {
        $this->assertEquals(
            'text/plain',
            Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', ['text/plain', 'appliation/signed-exchange;v=b2'])
        );
    }

    public function testContentParamsDefault(): void
    {
        $this->assertEquals(
            'text/plain',
            Negotiate::content('text/html', ['text/plain'])
        );
    }

    public function testContentEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
    
        Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', []);
    }

    public function testEncoding(): void
    {
        $this->assertEquals(
            'deflate',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['deflate'])
        );
    }

    public function testEncodingMultiple(): void
    {
        $this->assertEquals(
            'deflate',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['gzip', 'deflate'])
        );
    }

    public function testEncodingQuality(): void
    {
        $this->assertEquals(
            'gzip',
            Negotiate::encoding('deflate;q=0.9, gzip, *;q=0.5', ['gzip', 'deflate'])
        );
    }

    public function testEncodingDefault(): void
    {
        $this->assertEquals(
            'any',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', ['any'])
        );
    }

    public function testEncodingEmpty(): void
    {
        $this->assertEquals(
            'identity',
            Negotiate::encoding('deflate, gzip;q=0.9, *;q=0.5', [])
        );
    }

    public function testLanguage(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', ['en-GB'])
        );
    }

    public function testLanguageMultiple(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', ['en-GB', 'en-US', 'en'])
        );
    }

    public function testLanguageQuality(): void
    {
        $this->assertEquals(
            'en-US',
            Negotiate::language('ru-RU;q=0.9,en-US,en;q=0.8', ['ru-RU', 'en-US', 'en'])
        );
    }

    public function testLanguageLocales(): void
    {
        $this->assertEquals(
            'en-GB',
            Negotiate::language('ru-RU;q=0.9,en-US,en;q=0.8', ['ru-RU', 'en-GB', 'en'])
        );
    }

    public function testLanguageEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
    
        Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', []);
    }

}
