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

use DocuSign\eSign\Model;
use DocusignBundle\EnvelopeBuilder;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Exception\FileNotFoundException;

final class CreateDocument implements EnvelopeBuilderCallableInterface
{
    private $envelopeBuilder;

    public function __construct(EnvelopeBuilderInterface $envelopeBuilder)
    {
        $this->envelopeBuilder = $envelopeBuilder;
    }

    public function __invoke(array $context = []): void
    {
        if ($context['signature_name'] !== $this->envelopeBuilder->getName()) {
            return;
        }

        foreach ($this->envelopeBuilder->getFilePaths() as $key => $filePath){
            if (false === $contentBytes = $this->envelopeBuilder->getFileContent($filePath)) {
                throw new FileNotFoundException($filePath ?? 'null');
            }

            $base64FileContent = base64_encode($contentBytes);
            ['extension' => $extension, 'filename' => $filename] = pathinfo($filePath);

            $this->envelopeBuilder->addDocument(new Model\Document([
                'document_base64' => $base64FileContent,
                'name' => $filename,
                'file_extension' => $extension,
                'document_id' => $this->envelopeBuilder->getDocReference()+(int)$key,
            ]));
        }
        $this->envelopeBuilder->setDefaultSigner();
    }
}
