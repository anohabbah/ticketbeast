<?php

namespace Tests\Unit;

use App\RandomOrderConfirmationNumberGenerator;
use Tests\TestCase;

class RandomOrderConfirmationNumberTest extends TestCase
{
    /** @test */
    public function must_be_24_characters_long(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        self::assertEquals(24, strlen($generator->generate()));
    }

    /** @test */
    public function can_only_contain_uppercase_letter_and_numbers(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $generated = $generator->generate();

        self::assertMatchesRegularExpression('/^[A-Z0-9]+$/', $generated);
    }

    /** @test */
    public function cannot_contain_ambiguous_characters(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $generated = $generator->generate();

        self::assertFalse(strpos($generated, '1'));
        self::assertFalse(strpos($generated, 'I'));
        self::assertFalse(strpos($generated, '0'));
        self::assertFalse(strpos($generated, 'O'));
    }

    /** @test */
    public function order_confirmation_number_must_be_unique(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumbers = array_map(function () use ($generator) {
            return $generator->generate();
        }, range(1, 100));

        self::assertCount(100, array_unique($confirmationNumbers));
    }
}
