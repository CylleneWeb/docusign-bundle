<?php

/*
 * This file is part of the DocusignBundle.
 *
 * (c) Grégoire Hébert <gregoire@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DocusignBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Consent
{
    public const DEMO_CONSENT_URI = 'https://account-d.docusign.com/oauth/auth';
    public const CONSENT_URI = 'https://account.docusign.com/oauth/auth';

    private $consentUri;
    private $integrationKey;

    public function __construct(bool $demo, string $integrationKey)
    {
        $this->consentUri = $demo ? self::DEMO_CONSENT_URI : self::CONSENT_URI;
        $this->integrationKey = $integrationKey;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        return new RedirectResponse(sprintf(
            '%s?response_type=code&scope=signature%%20impersonation%%20click.manage%%20click.send&client_id=%s&redirect_uri=%s',
            $this->consentUri,
            $this->integrationKey,
            $request->getSchemeAndHttpHost()
        ), RedirectResponse::HTTP_TEMPORARY_REDIRECT);
    }
}
