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

namespace DocusignBundle\EnvelopeCreator;

use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Utils\SignatureExtractor;

final class CreateSignature implements EnvelopeBuilderCallableInterface
{
    private $envelopeBuilder;
    private $signatureExtractor;

    public function __construct(EnvelopeBuilderInterface $envelopeBuilder, SignatureExtractor $signatureExtractor)
    {
        $this->envelopeBuilder = $envelopeBuilder;
        $this->signatureExtractor = $signatureExtractor;
    }

    public function __invoke(array $context = []): void
    {
        if ($context['signature_name'] !== $this->envelopeBuilder->getName()) {
            return;
        }

        $signatures = $this->signatureExtractor->getSignatures();

        if (empty($signatures)) {
            throw new \LogicException('No signatures defined. Check your `signatures` configuration and query parameter.');
        }

        foreach ($signatures as $signature) {
            $isNotMandatory = null;

            if(isset($signature['isNotMandatory'])){
                $isNotMandatory = $signature['isNotMandatory'];
            }

            if(isset($signature['anchor_string'])){
                $this->envelopeBuilder->addAnchorSignatureZone($signature['anchor_string'], $signature, $isNotMandatory);
            }
            else {
                if(isset($signature['page']) && $signature['page'] === 0){
                    unset($signatures[0]);
                }
                else
                {
                    $this->envelopeBuilder->addSignatureZone($signature['page'], $signature['x_position'], $signature['y_position']);
                }

            }
        }
    }
}
