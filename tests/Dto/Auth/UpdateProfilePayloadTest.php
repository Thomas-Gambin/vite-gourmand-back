<?php

declare(strict_types=1);

namespace App\Tests\Dto\Auth;

use App\Dto\Auth\UpdateProfilePayload;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

final class UpdateProfilePayloadTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidPayloadHasNoViolations(): void
    {
        $payload = $this->validPayload();

        $violations = $this->validator->validate($payload);

        self::assertCount(0, $violations);
    }

    #[DataProvider('invalidPhoneProvider')]
    public function testInvalidPhoneIsRejected(string $telephone): void
    {
        $payload = new UpdateProfilePayload(
            nom: 'Gambin',
            prenom: 'Thomas',
            telephone: $telephone,
            ville: 'Bordeaux',
            pays: 'France',
            adressePostale: '12 rue Exemple',
        );

        $violations = $this->validator->validate($payload);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('telephone', $violations->get(0)->getPropertyPath());
        self::assertSame('Le numéro de téléphone est invalide.', (string) $violations->get(0)->getMessage());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidPhoneProvider(): iterable
    {
        yield 'too short' => ['123'];
        yield 'does not start with zero' => ['1600000000'];
        yield 'invalid prefix' => ['0000000000'];
    }

    private function validPayload(): UpdateProfilePayload
    {
        return new UpdateProfilePayload(
            nom: 'Gambin',
            prenom: 'Thomas',
            telephone: '0600000000',
            ville: 'Bordeaux',
            pays: 'France',
            adressePostale: '12 rue Exemple',
        );
    }
}
