<?php

/**
 * PHP version 7.4
 * src/Auth/JwtAuth.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 *
 * @link     https://odan.github.io/2019/12/02/slim4-oauth2-jwt.html
 */

namespace TDW\ACiencia\Auth;

use Cake\Chronos\Chronos;
use InvalidArgumentException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Ramsey\Uuid\Uuid;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;

/**
 * Class JwtAuth
 */
final class JwtAuth
{
    // The issuer name
    private string $issuer;

    // OAuth2 client id.
    private string $clientId;

    // Max lifetime in seconds
    private int $lifetime;

    // The private key
    private string $privateKey;

    // The public key
    private string $publicKey;

    // The signer
    private Sha256 $signer;

    /**
     * The constructor.
     *
     * @param string $issuer The issuer name
     * @param string $clientId OAuth2 client id.
     * @param int $lifetime The max lifetime
     * @param string $privateKey The private key as string
     * @param string $publicKey The public key as string
     */
    public function __construct(
        string $issuer,
        string $clientId,
        int $lifetime,
        string $privateKey,
        string $publicKey
    ) {
        $this->issuer = $issuer;
        $this->clientId = $clientId;
        $this->lifetime = $lifetime;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->signer = new Sha256();
    }

    /**
     * Get JWT max lifetime.
     *
     * @return int The lifetime in seconds
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Create JSON web token.
     *
     * @param User $user
     * @param array $requestedScopes Requested scopes
     * @return string The JWT
     */
    public function createJwt(User $user, array $requestedScopes = Role::ROLES): string
    {
        $issuedAt = Chronos::now()->getTimestamp();
        (!in_array(Role::ROLE_READER, $requestedScopes))
            ? $requestedScopes[] = Role::ROLE_READER
            : null;
        $awardedScopes = array_values(array_intersect($requestedScopes, $user->getRoles()));

        // (JWT ID) Claim, a unique identifier for the JWT
        return (new Builder())->issuedBy($this->issuer)
            ->identifiedBy(Uuid::uuid4()->toString(), true)
            ->issuedAt($issuedAt) // the time at which the JWT was issued
            ->canOnlyBeUsedAfter($issuedAt)
            ->expiresAt($issuedAt + $this->lifetime)
            ->withClaim('aud', $this->clientId) // Audience
            ->withClaim('uid', $user->getId())
            ->withClaim('username', $user->getUsername())
            ->withClaim('scopes', $awardedScopes)
            ->getToken($this->signer, new Key($this->privateKey));
    }

    /**
     * Parse token.
     *
     * @param string $token The JWT
     *
     * @throws InvalidArgumentException
     *
     * @return Token The parsed token
     */
    public function createParsedToken(string $token): Token
    {
        return (new Parser())->parse($token);
    }

    /**
     * Validate the access token.
     *
     * @param string $accessToken The JWT
     *
     * @return bool The status
     */
    public function validateToken(string $accessToken): bool
    {
        $token = $this->createParsedToken($accessToken);

        if (!$token->verify($this->signer, $this->publicKey)) {
            // Token signature is not valid
            return false;
        }

        // Check whether the token has not expired
        $data = new ValidationData();
        $data->setCurrentTime(Chronos::now()->getTimestamp());
        $data->setIssuer($token->getClaim('iss'));
        $data->setId($token->getClaim('jti'));

        return $token->validate($data);
    }
}
