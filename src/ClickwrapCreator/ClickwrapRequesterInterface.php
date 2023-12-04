<?php

namespace DocusignBundle\ClickwrapCreator;

interface ClickwrapRequesterInterface
{
    public function createClickwrap(array $document_info, array $parameters);
}
