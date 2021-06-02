<?php

namespace Tests\Unit;

use App\HashidsTicketCodeGenerator;
use App\Models\Ticket;
use PHPUnit\Framework\TestCase;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    /** @test */
    public function ticket_codes_are_at_least_6_characters_long(): void
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        self::assertTrue(strlen($code) >= 6);
    }

    /** @test */
    public function ticket_codes_can_only_contain_uppercase_letters(): void
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        self::assertMatchesRegularExpression('/^[A-Z]+$/', $code);
    }

    /** @test */
    public function ticket_codes_for_the_same_ticket_id_are_the_same(): void
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        self::assertEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_for_different_ticket_ids_are_different(): void
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        self::assertNotEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_generated_with_different_salts_are_different(): void
    {
        $ticketCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
        $ticketCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');

        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));

        self::assertNotEquals($code1, $code2);
    }
}
